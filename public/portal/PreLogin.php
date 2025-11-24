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
<title>Portal Académico - Acceso Estudiante</title>
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
      <form action="admin.php" method="get">
        <button type="submit" class="btn">Sesion Administrador</button>
      </form>
      <form action="login.php" method="get">
      <button type="submit" class="btn">Sesion Estudiante</button>
      </form>
  </div>
</div>
</body>
</html>
