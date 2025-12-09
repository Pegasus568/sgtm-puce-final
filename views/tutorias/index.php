<div class="content-wrapper">
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1 class="m-0">Bienvenido al SGTM</h1>
        </div>
      </div>
    </div>
  </div>

  <div class="content">
    <div class="container-fluid">
      
      <div class="alert alert-info">
        <h5><i class="icon fas fa-user"></i> ¡Hola, <?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?>!</h5>
        Has ingresado como <strong><?php echo $_SESSION['usuario_rol']; ?></strong>.
      </div>

      <div class="row">
        
        <?php if($_SESSION['usuario_rol'] == 'ESTUDIANTE'): ?>
        <div class="col-lg-6">
          <div class="card card-primary card-outline">
            <div class="card-header">
              <h5 class="m-0">¿Necesitas una Tutoría?</h5>
            </div>
            <div class="card-body">
              <p class="card-text">Busca a tu docente, revisa su horario y agenda tu cita en segundos.</p>
              <a href="<?php echo BASE_URL; ?>tutorias/solicitar" class="btn btn-primary">Solicitar Ahora</a>
            </div>
          </div>
        </div>
        <?php endif; ?>

        <?php if($_SESSION['usuario_rol'] == 'DOCENTE'): ?>
        <div class="col-lg-3 col-6">
          <div class="small-box bg-warning">
            <div class="inner">
              <h3>0</h3>
              <p>Solicitudes Pendientes</p>
            </div>
            <div class="icon"><i class="fas fa-inbox"></i></div>
            <a href="#" class="small-box-footer">Revisar <i class="fas fa-arrow-circle-right"></i></a>
          </div>
        </div>
        
        <div class="col-lg-3 col-6">
          <div class="small-box bg-info">
            <div class="inner">
              <h3>Config</h3>
              <p>Mis Horarios</p>
            </div>
            <div class="icon"><i class="fas fa-clock"></i></div>
            <a href="<?php echo BASE_URL; ?>docente/horarios" class="small-box-footer">Configurar <i class="fas fa-arrow-circle-right"></i></a>
          </div>
        </div>
        <?php endif; ?>

      </div>
      </div>
  </div>
  </div>