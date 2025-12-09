<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="<?php echo BASE_URL; ?>tutorias/index" class="brand-link">
      <span class="brand-text font-weight-light pl-3"><b>SGTM</b> PUCE</span>
    </a>

    <div class="sidebar">
      <div class="user-panel mt-3 pb-3 mb-3 d-flex">
        <div class="image">
          <img src="https://cdn-icons-png.flaticon.com/512/149/149071.png" class="img-circle elevation-2" alt="User Image">
        </div>
        <div class="info">
          <a href="#" class="d-block">
            <?php echo htmlspecialchars($_SESSION['usuario_nombre'] ?? 'Invitado'); ?> <br>
            <small class="badge badge-light text-dark"><?php echo $_SESSION['usuario_rol'] ?? ''; ?></small>
          </a>
        </div>
      </div>

      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
          
          <li class="nav-item">
            <a href="<?php echo BASE_URL; ?>tutorias/index" class="nav-link">
              <i class="nav-icon fas fa-home"></i>
              <p>Inicio</p>
            </a>
          </li>

          <?php if(isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] === 'ESTUDIANTE'): ?>
          <li class="nav-header">MI GESTIÓN ACADÉMICA</li>
          
          <li class="nav-item">
            <a href="<?php echo BASE_URL; ?>tutorias/solicitar" class="nav-link">
              <i class="nav-icon fas fa-plus-circle"></i>
              <p>Reservar Cita</p>
            </a>
          </li>
          
          <li class="nav-item">
            <a href="<?php echo BASE_URL; ?>tutorias/mis_solicitudes" class="nav-link">
              <i class="nav-icon fas fa-list-alt"></i>
              <p>Mis Solicitudes</p>
            </a>
          </li>
          <?php endif; ?>

          <?php if(isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] === 'DOCENTE'): ?>
          <li class="nav-header">PANEL DOCENTE</li>
          
          <li class="nav-item">
            <a href="<?php echo BASE_URL; ?>tutorias/agenda" class="nav-link">
              <i class="nav-icon fas fa-calendar-check"></i>
              <p>Gestionar Agenda</p>
            </a>
          </li>
          
          <li class="nav-item">
            <a href="<?php echo BASE_URL; ?>docente/horarios" class="nav-link">
              <i class="nav-icon fas fa-clock"></i>
              <p>Configurar Horarios</p>
            </a>
          </li>
          <?php endif; ?>

           <?php if(isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] === 'ADMIN'): ?>
          <li class="nav-header">ADMINISTRACIÓN</li>
          
          <li class="nav-item">
            <a href="<?php echo BASE_URL; ?>usuarios/index" class="nav-link">
              <i class="nav-icon fas fa-users-cog"></i>
              <p>Usuarios</p>
            </a>
          </li>

          <li class="nav-item">
            <a href="<?php echo BASE_URL; ?>carreras/index" class="nav-link">
              <i class="nav-icon fas fa-university"></i>
              <p>Carreras y Materias</p>
            </a>
          </li>
          
          <li class="nav-item">
            <a href="<?php echo BASE_URL; ?>tipos/index" class="nav-link">
              <i class="nav-icon fas fa-tags"></i>
              <p>Tipos de Tutoría</p>
            </a>
          </li>

          <li class="nav-item">
            <a href="<?php echo BASE_URL; ?>admintutorias/index" class="nav-link">
              <i class="nav-icon fas fa-eye"></i>
              <p>Historial Global</p>
            </a>
          </li>

          <li class="nav-item">
            <a href="<?php echo BASE_URL; ?>reportes/index" class="nav-link">
              <i class="nav-icon fas fa-chart-line"></i>
              <p>Reportes y KPIs</p>
            </a>
          </li>
          <?php endif; ?>

        </ul>
      </nav>
      </div>
    </aside>