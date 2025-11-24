<?php
session_start();
if (isset($_SESSION['user'])) {
    header('Location: dashboard.php');
    exit;
}
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Portal Académico - Acceso Administrador</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="wrapper">
  <header class="brand">
    <div class="icon">🎓</div>
    <h1>Portal Académico</h1>
    <p class="subtitle">Selecciona tu tipo de acceso</p>
  </header>
  <div class="card">
    <a href="PreLogin.php" class="back">&larr; Acceso Administrador</a>
    <?php if (!empty($_GET['error'])): ?>
      <div class="alert"><?=htmlspecialchars($_GET['error'])?></div>
    <?php endif; ?>
    <form action="login_process_admin.php" method="post" autocomplete="off">
      <label>Usuario Administrador</label>
      <input name="numero" type="text" placeholder="Ej: 2024001234" required>
      <label>Contraseña</label>
      <input name="password" type="password" placeholder="Ingresa tu contraseña" required>
      <button type="submit" class="btn">Iniciar Sesión</button>
    </form>
  </div>
</div>
</body>
</html>