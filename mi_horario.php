<?php
// mi_horario.php
require_once 'includes/auth.php';
require_once 'includes/db.php';

// 1. Seguridad: Solo Docentes
verificarRol(['DOCENTE']);

$docente_id = $_SESSION['usuario_id'];
$mensaje = "";
$error = "";

// ==========================================================================
// LÓGICA DEL CONTROLADOR
// ==========================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    
    // --- AGREGAR HORARIO ---
    if ($_POST['action'] === 'add') {
        $dia = $_POST['dia']; // 1=Lunes, 5=Viernes
        $inicio = $_POST['hora_inicio'];
        $fin = $_POST['hora_fin'];

        if ($inicio >= $fin) {
            $error = "La hora de fin debe ser mayor a la de inicio.";
        } else {
            // Validación de Solapamiento
            $sql_check = "SELECT COUNT(*) FROM horarios_docentes 
                          WHERE docente_id = ? AND dia_semana = ? 
                          AND NOT (hora_fin <= ? OR hora_inicio >= ?)";
            $stmt_check = $pdo->prepare($sql_check);
            $stmt_check->execute([$docente_id, $dia, $inicio, $fin]);
            
            if ($stmt_check->fetchColumn() > 0) {
                $error = "¡Error! El horario se cruza con otro existente.";
            } else {
                $stmt = $pdo->prepare("INSERT INTO horarios_docentes (docente_id, dia_semana, hora_inicio, hora_fin) VALUES (?, ?, ?, ?)");
                if ($stmt->execute([$docente_id, $dia, $inicio, $fin])) {
                    $mensaje = "Horario agregado correctamente.";
                } else {
                    $error = "Error al guardar.";
                }
            }
        }

    // --- ELIMINAR HORARIO (Con Validación de Integridad) ---
    } elseif ($_POST['action'] === 'delete') {
        $id_borrar = $_POST['id_horario'];
        
        // 1. Obtener datos del horario que se quiere borrar
        $stmt_get = $pdo->prepare("SELECT dia_semana, hora_inicio FROM horarios_docentes WHERE id = ? AND docente_id = ?");
        $stmt_get->execute([$id_borrar, $docente_id]);
        $h_data = $stmt_get->fetch();

        if ($h_data) {
            // 2. Verificar si hay tutorías FUTURAS (Pendientes o Confirmadas) en ese horario
            // Nota: WEEKDAY(fecha) devuelve 0=Lunes, 4=Viernes. Nuestra BD usa 1=Lunes.
            // Ajustamos: WEEKDAY + 1 = dia_semana de nuestra BD.
            $sql_integrity = "SELECT COUNT(*) FROM tutorias 
                              WHERE tutor_id = ? 
                              AND estado IN ('PENDIENTE', 'CONFIRMADA') 
                              AND fecha >= CURDATE()
                              AND (WEEKDAY(fecha) + 1) = ?
                              AND hora_inicio = ?";
            
            $stmt_int = $pdo->prepare($sql_integrity);
            $stmt_int->execute([$docente_id, $h_data['dia_semana'], $h_data['hora_inicio']]);

            if ($stmt_int->fetchColumn() > 0) {
                $error = "No puedes eliminar este horario: Tienes tutorías futuras asignadas en este bloque. Cancélalas primero.";
            } else {
                // Proceder a eliminar
                $stmt = $pdo->prepare("DELETE FROM horarios_docentes WHERE id = ?");
                if ($stmt->execute([$id_borrar])) {
                    $mensaje = "Horario eliminado correctamente.";
                }
            }
        }
    }
}

// Obtener lista actualizada
$stmt_list = $pdo->prepare("SELECT * FROM horarios_docentes WHERE docente_id = ? ORDER BY dia_semana, hora_inicio");
$stmt_list->execute([$docente_id]);
$horarios = $stmt_list->fetchAll();

$dias_semana = [1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles', 4 => 'Jueves', 5 => 'Viernes'];

require_once 'includes/header.php'; 
?>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <h1>Configuración de Disponibilidad</h1>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-4">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Agregar Bloque</h3>
                        </div>
                        <form action="mi_horario.php" method="POST">
                            <input type="hidden" name="action" value="add">
                            <div class="card-body">
                                <?php if($error): ?>
                                    <div class="alert alert-danger alert-dismissible">
                                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                                        <?php echo $error; ?>
                                    </div>
                                <?php elseif($mensaje): ?>
                                    <div class="alert alert-success alert-dismissible">
                                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                                        <?php echo $mensaje; ?>
                                    </div>
                                <?php endif; ?>

                                <div class="form-group">
                                    <label>Día</label>
                                    <select name="dia" class="form-control" required>
                                        <?php foreach($dias_semana as $num => $nombre): ?>
                                            <option value="<?php echo $num; ?>"><?php echo $nombre; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Desde</label>
                                    <input type="time" name="hora_inicio" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label>Hasta</label>
                                    <input type="time" name="hora_fin" class="form-control" required>
                                </div>
                            </div>
                            <div class="card-footer">
                                <button type="submit" class="btn btn-primary btn-block">Guardar</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="col-md-8">
                    <div class="card card-outline card-info">
                        <div class="card-header">
                            <h3 class="card-title">Horarios Registrados</h3>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Día</th>
                                        <th>Horario</th>
                                        <th style="width: 50px">Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($horarios as $h): ?>
                                    <tr>
                                        <td><?php echo $dias_semana[$h['dia_semana']]; ?></td>
                                        <td>
                                            <span class="badge badge-info text-md">
                                                <?php echo substr($h['hora_inicio'],0,5) . ' - ' . substr($h['hora_fin'],0,5); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <form action="mi_horario.php" method="POST" onsubmit="return confirm('¿Eliminar este bloque horario?');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id_horario" value="<?php echo $h['id']; ?>">
                                                <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash-alt"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php if(empty($horarios)) echo "<tr><td colspan='3' class='text-center text-muted'>No tienes horarios configurados.</td></tr>"; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<?php require_once 'includes/footer.php'; ?>