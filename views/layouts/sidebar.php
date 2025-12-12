<aside class="main-sidebar sidebar-light-navy elevation-4">
    
    <a href="<?php echo BASE_URL; ?>tutorias/index" class="brand-link text-center">
      <img src="<?php echo ASSET_URL; ?>public/img/logo_azul.png" alt="PUCE" style="max-height: 50px; width: auto;">
    </a>

    <div class="sidebar">
      <div class="user-panel mt-3 pb-3 mb-3 d-flex">
        <div class="image">
          <img src="https://cdn-icons-png.flaticon.com/512/149/149071.png" class="img-circle elevation-2" alt="User">
        </div>
        <div class="info">
          <a href="#" class="d-block">
            <?php echo htmlspecialchars($_SESSION['usuario_nombre'] ?? 'Invitado'); ?> <br>
            <small class="badge badge-light border"><?php echo $_SESSION['usuario_rol'] ?? ''; ?></small>
          </a>
        </div>
      </div>

      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
          
          <li class="nav-item">
            <a href="<?php echo BASE_URL; ?>tutorias/index" class="nav-link">
              <i class="nav-icon fas fa-home"></i> <p>Inicio</p>
            </a>
          </li>

          <?php if(isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] === 'ESTUDIANTE'): ?>
          <li class="nav-header">ESTUDIANTE</li>
          <li class="nav-item"><a href="<?php echo BASE_URL; ?>tutorias/solicitar" class="nav-link"><i class="nav-icon fas fa-plus-circle"></i><p>Reservar Cita</p></a></li>
          <li class="nav-item"><a href="<?php echo BASE_URL; ?>tutorias/mis_solicitudes" class="nav-link"><i class="nav-icon fas fa-list-alt"></i><p>Mis Solicitudes</p></a></li>
          <li class="nav-item"><a href="<?php echo BASE_URL; ?>reportes/index" class="nav-link"><i class="nav-icon fas fa-file-pdf"></i><p>Mis Reportes</p></a></li>
          <?php endif; ?>

          <?php if(isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] === 'DOCENTE'): ?>
          <li class="nav-header">DOCENTE</li>
          <li class="nav-item"><a href="<?php echo BASE_URL; ?>tutorias/agenda" class="nav-link"><i class="nav-icon fas fa-calendar-check"></i><p>Mi Agenda</p></a></li>
          <li class="nav-item"><a href="<?php echo BASE_URL; ?>tutorias/solicitar" class="nav-link"><i class="nav-icon fas fa-users"></i><p>Agendar Sesión</p></a></li>
          <li class="nav-item"><a href="<?php echo BASE_URL; ?>docente/horarios" class="nav-link"><i class="nav-icon fas fa-clock"></i><p>Configurar Horario</p></a></li>
          <li class="nav-item"><a href="<?php echo BASE_URL; ?>reportes/index" class="nav-link"><i class="nav-icon fas fa-file-pdf"></i><p>Reportes PDF</p></a></li>
          <?php endif; ?>

           <?php if(isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] === 'ADMIN'): ?>
          <li class="nav-header">ADMINISTRACIÓN</li>
          <li class="nav-item"><a href="<?php echo BASE_URL; ?>usuarios/index" class="nav-link"><i class="nav-icon fas fa-users-cog"></i><p>Usuarios</p></a></li>
          <li class="nav-item"><a href="<?php echo BASE_URL; ?>carreras/index" class="nav-link"><i class="nav-icon fas fa-university"></i><p>Carreras</p></a></li>
          <li class="nav-item"><a href="<?php echo BASE_URL; ?>tipos/index" class="nav-link"><i class="nav-icon fas fa-tags"></i><p>Tipos Tutoría</p></a></li>
          <li class="nav-item"><a href="<?php echo BASE_URL; ?>admintutorias/index" class="nav-link"><i class="nav-icon fas fa-eye"></i><p>Historial</p></a></li>
          <li class="nav-item"><a href="<?php echo BASE_URL; ?>reportes/index" class="nav-link"><i class="nav-icon fas fa-chart-line"></i><p>Reportes PDF</p></a></li>
          <?php endif; ?>

        </ul>
      </nav>
    </div>
  </aside>