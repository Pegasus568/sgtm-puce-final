<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>SGTM PUCE | Inicio</title>

  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">
  
  <link rel="stylesheet" href="<?php echo ASSET_URL; ?>public/css/custom_puce.css">

  <style>
    /* Estilos específicos para esta página */
    .hero-section {
        background: linear-gradient(135deg, var(--puce-blue-dark) 0%, #001845 100%);
        color: white;
        padding: 80px 20px;
        text-align: center;
        border-bottom: 5px solid var(--puce-gold);
    }
    .hero-title { font-size: 3rem; font-weight: 700; }
    .hero-subtitle { font-size: 1.2rem; opacity: 0.9; margin-bottom: 30px; }
    
    .feature-box {
        padding: 30px;
        border-radius: 12px;
        background: white;
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        height: 100%;
        transition: transform 0.3s;
        border-top: 4px solid var(--puce-blue-light);
    }
    .feature-box:hover { transform: translateY(-10px); }
    .icon-box {
        font-size: 3rem;
        color: var(--puce-blue-dark);
        margin-bottom: 20px;
    }
    
    .btn-action {
        padding: 12px 40px;
        font-size: 1.2rem;
        border-radius: 50px;
        font-weight: bold;
        background-color: var(--puce-gold);
        color: #002048;
        border: none;
    }
    .btn-action:hover {
        background-color: #e0a800;
        color: #000;
        text-decoration: none;
    }
  </style>
</head>
<body style="background-color: #f4f6f9;">

    <nav class="navbar navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand font-weight-bold text-dark" href="#">
                <img src="<?php echo ASSET_URL; ?>public/img/logo_azul.png" height="40" class="mr-2">
                SGTM
            </a>
            <a href="<?php echo BASE_URL; ?>auth/index" class="btn btn-outline-primary btn-sm font-weight-bold rounded-pill px-4">
                <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
            </a>
        </div>
    </nav>

    <header class="hero-section">
        <div class="container">
            <h1 class="hero-title">Gestión de Tutorías Académicas</h1>
            <p class="hero-subtitle">Optimiza tu tiempo, organiza tus clases y mejora tu rendimiento académico con la plataforma oficial de la Sede Ambato.</p>
            <br>
            <a href="<?php echo BASE_URL; ?>auth/index" class="btn btn-action shadow">
                INGRESAR AL SISTEMA
            </a>
        </div>
    </header>

    <section class="py-5">
        <div class="container">
            <div class="row text-center">
                
                <div class="col-md-4 mb-4">
                    <div class="feature-box">
                        <div class="icon-box"><i class="fas fa-user-graduate"></i></div>
                        <h4>Para Estudiantes</h4>
                        <p class="text-muted">Reserva citas con tus docentes en tiempo real. Visualiza horarios disponibles y gestiona tu historial académico.</p>
                    </div>
                </div>

                <div class="col-md-4 mb-4">
                    <div class="feature-box">
                        <div class="icon-box"><i class="fas fa-chalkboard-teacher"></i></div>
                        <h4>Para Docentes</h4>
                        <p class="text-muted">Administra tu agenda, aprueba solicitudes y registra la asistencia de tus tutorías de forma digital y centralizada.</p>
                    </div>
                </div>

                <div class="col-md-4 mb-4">
                    <div class="feature-box">
                        <div class="icon-box"><i class="fas fa-file-pdf"></i></div>
                        <h4>Reportes Oficiales</h4>
                        <p class="text-muted">Generación automática de reportes en PDF con formato institucional para validación y seguimiento de calidad.</p>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <footer class="bg-dark text-white text-center py-4">
        <div class="container">
            <small>
                &copy; <?php echo date('Y'); ?> <strong>Pontificia Universidad Católica del Ecuador - Sede Ambato</strong><br>
                Sistema desarrollado bajo norma ISO/IEC 12207
            </small>
        </div>
    </footer>

</body>
</html>