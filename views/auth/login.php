<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>SGTM PUCE | Ingreso</title>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
  
  <style>
    body {
        background: linear-gradient(135deg, #002D74 0%, #001845 100%);
        height: 100vh;
        display: flex; align-items: center; justify-content: center;
        font-family: 'Source Sans Pro', sans-serif;
    }
    .login-card {
        background: white; width: 900px; max-width: 90%;
        border-radius: 12px; box-shadow: 0 15px 35px rgba(0,0,0,0.4);
        overflow: hidden; display: flex; min-height: 550px;
    }
    .login-brand-side {
        width: 55%; position: relative; display: flex; align-items: center; justify-content: center;
        /* IMAGEN DE FONDO */
        background-image: url('<?php echo ASSET_URL; ?>public/img/fondo_login.jpg');
        background-size: cover; background-position: center;
    }
    .login-brand-side::before {
        content: ""; position: absolute; top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(0, 45, 116, 0.85); /* Azul PUCE semitransparente */
    }
    .brand-content { position: relative; z-index: 2; text-align: center; color: white; padding: 40px; }
    
    .login-form-side { width: 45%; padding: 50px; display: flex; flex-direction: column; justify-content: center; }
    .form-control { border: none; border-bottom: 1px solid #ccc; border-radius: 0; padding: 20px 5px; }
    .form-control:focus { border-bottom-color: #FDB913; box-shadow: none; }
    .btn-login {
        background-color: #002D74; color: white; padding: 12px; border-radius: 50px;
        font-weight: bold; border: none; width: 100%; margin-top: 20px;
    }
    .btn-login:hover { background-color: #004489; }
    @media (max-width: 768px) {
        .login-card { flex-direction: column; height: auto; }
        .login-brand-side { width: 100%; height: 200px; }
        .login-form-side { width: 100%; padding: 30px; }
    }
  </style>
</head>
<body>

<div class="login-card">
    <div class="login-brand-side">
        <div class="brand-content">
            <img src="<?php echo ASSET_URL; ?>public/img/logo_azul.png" 
                 style="filter: brightness(0) invert(1); width: 180px; margin-bottom: 20px;">
            <h2>SGTM</h2>
            <p>Sistema de Gestión de Tutorías<br>Pontificia Universidad Católica del Ecuador</p>
        </div>
    </div>

    <div class="login-form-side">
        <div class="text-center mb-4">
            <h4 style="color:#002D74; font-weight:700">Bienvenido</h4>
            <small class="text-muted">Ingreso al Sistema Académico</small>
        </div>

        <?php if(isset($_SESSION['error_login'])): ?>
            <div class="alert alert-danger small text-center"><?php echo $_SESSION['error_login']; unset($_SESSION['error_login']); ?></div>
        <?php endif; ?>

        <form action="<?php echo BASE_URL; ?>auth/login" method="post">
            <div class="form-group mb-4">
                <input type="email" name="correo" class="form-control" placeholder="Correo Institucional" required>
            </div>
            <div class="form-group mb-4">
                <input type="password" name="password" class="form-control" placeholder="Contraseña" required>
            </div>
            <button type="submit" class="btn btn-login">INGRESAR</button>
        </form>
    </div>
</div>

</body>
</html>