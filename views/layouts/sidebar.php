<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="<?php echo BASE_URL; ?>tutorias/index" class="brand-link">
      <span class="brand-text font-weight-light pl-3"><b>SGTM</b> PUCE</span>
    </a>
    <div class="sidebar">
      <div class="user-panel mt-3 pb-3 mb-3 d-flex">
        <div class="info">
          <a href="#" class="d-block"><?php echo $_SESSION['usuario_nombre'] ?? 'Invitado'; ?> <br>
            <small><?php echo $_SESSION['usuario_rol'] ?? ''; ?></small></a>
        </div>
      </div>
      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
          <li class="nav-item"><a href="<?php echo BASE_URL; ?>tutorias/index" class="nav-link"><i class="nav-icon fas fa-home"></i><p>Inicio</p></a></li>

          <?php if($_SESSION['usuario_rol'] === 'ESTUDIANTE'): ?>
          <li class="nav-header">ESTUDIANTE</li>
          <li class="nav-item"><a href="<?php echo BASE_URL; ?>tutorias/solicitar" class="nav-link"><i class="nav-icon fas fa-plus-circle"></i><p>Reservar Cita</p></a></li>
          <li class="nav-item"><a href="<?php echo BASE_URL; ?>tutorias/mis_solicitudes" class="nav-link"><i class="nav-icon fas fa-list-alt"></i><p>Mis Solicitudes</p></a></li>
          <li class="nav-item"><a href="<?php echo BASE_URL; ?>reportes/index" class="nav-link"><i class="nav-icon fas fa-file-pdf"></i><p>Mis Reportes</p></a></li>
          <?php endif; ?>

          <?php if($_SESSION['usuario_rol'] === 'DOCENTE'): ?>
          <li class="nav-header">DOCENTE</li>
          <li class="nav-item"><a href="<?php echo BASE_URL; ?>tutorias/agenda" class="nav-link"><i class="nav-icon fas fa-calendar-check"></i><p>Mi Agenda</p></a></li>
          <li class="nav-item"><a href="<?php echo BASE_URL; ?>tutorias/solicitar" class="nav-link"><i class="nav-icon fas fa-users"></i><p>Agendar Sesión</p></a></li>
          <li class="nav-item"><a href="<?php echo BASE_URL; ?>docente/horarios" class="nav-link"><i class="nav-icon fas fa-clock"></i><p>Configurar Horario</p></a></li>
          <li class="nav-item"><a href="<?php echo BASE_URL; ?>reportes/index" class="nav-link"><i class="nav-icon fas fa-file-pdf"></i><p>Reportes PDF</p></a></li>
          <?php endif; ?>

           <?php if($_SESSION['usuario_rol'] === 'ADMIN'): ?>
          <li class="nav-header">ADMINISTRACIÓN</li>
          <li class="nav-item"><a href="<?php echo BASE_URL; ?>usuarios/index" class="nav-link"><i class="nav-icon fas fa-users"></i><p>Usuarios</p></a></li>
          <li class="nav-item"><a href="<?php echo BASE_URL; ?>carreras/index" class="nav-link"><i class="nav-icon fas fa-university"></i><p>Académico</p></a></li>
          <li class="nav-item"><a href="<?php echo BASE_URL; ?>tipos/index" class="nav-link"><i class="nav-icon fas fa-tags"></i><p>Tipos Tutoría</p></a></li>
          <li class="nav-item"><a href="<?php echo BASE_URL; ?>admintutorias/index" class="nav-link"><i class="nav-icon fas fa-eye"></i><p>Historial</p></a></li>
          <li class="nav-item"><a href="<?php echo BASE_URL; ?>reportes/index" class="nav-link"><i class="nav-icon fas fa-chart-line"></i><p>Reportes PDF</p></a></li>
          <?php endif; ?>
        </ul>
      </nav>
    </div>
  </aside>