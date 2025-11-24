<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: PreLogin.php'); 
    exit;
}
$user = $_SESSION['user'];
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Dashboard</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="dashboard">
    <h2>Bienvenido, <?=htmlspecialchars($user['nombre'] ?? $user['numero'])?></h2>
    <p>Número: <?=htmlspecialchars($user['numero'])?></p>
    <a class="btn" href="logout.php">Cerrar sesión</a>
  </div>
</body>
</html>
