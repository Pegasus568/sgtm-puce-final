<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>SGTM v2.0 | Ingreso</title>

  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
  
  <style>
    body {
        background-color: #e9ecef;
        background-image: url('https://img.freepik.com/free-photo/diverse-students-studying-together-academic-concept_53876-126292.jpg?w=1380');
        background-size: cover;
        background-position: center;
        background-blend-mode: multiply; /* Oscurece la imagen para leer mejor */
    }
    .login-box {
        box-shadow: 0 0 20px rgba(0,0,0,0.5);
        border-radius: 10px;
        overflow: hidden;
        background: white;
    }
    .card-header {
        background-color: #007bff;
        color: white;
        border-bottom: 0;
    }
    .card-header a {
        color: white !important;
    }
    .btn-primary {
        background-color: #0056b3;
        border-color: #004085;
    }
    .btn-primary:hover {
        background-color: #004085;
    }
  </style>
</head>
<body class="hold-transition login-page">
<div class="login-box">
  
  <div class="card card-outline card-primary mb-0">
    <div class="card-header text-center">
      <a href="#" class="h1"><b>SGTM</b> PUCE</a>
    </div>
    <div class="card-body">
      <p class="login-box-msg">Sistema de Gestión de Tutorías</p>

      <?php if(isset($_SESSION['error_login'])): ?>
          <div class="alert alert-danger alert-dismissible">
              <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
              <i class="icon fas fa-exclamation-triangle"></i> <?php echo $_SESSION['error_login']; unset($_SESSION['error_login']); ?>
          </div>
      <?php endif; ?>

      <form action="<?php echo BASE_URL; ?>auth/login" method="post">
        
        <div class="input-group mb-3">
          <input type="email" name="correo" class="form-control" placeholder="Correo Institucional" required>
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-envelope"></span>
            </div>
          </div>
        </div>
        
        <div class="input-group mb-3">
          <input type="password" name="password" class="form-control" placeholder="Contraseña" required>
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-lock"></span>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-8">
            <div class="icheck-primary">
              <input type="checkbox" id="remember">
              <label for="remember">Recuérdame</label>
            </div>
          </div>
          <div class="col-4">
            <button type="submit" class="btn btn-primary btn-block">Ingresar</button>
          </div>
        </div>

      </form>
      
      <div class="social-auth-links text-center mt-2 mb-3">
        <hr>
        <small class="text-muted">¿Eres estudiante nuevo?</small><br>
        <a href="#" class="btn btn-block btn-default disabled">
          <i class="fas fa-user-plus mr-2"></i> Solicitar Registro
        </a>
      </div>
      
    </div>
    </div>
  </div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
</body>
</html>