<?php
require 'db.php';

$numero = '211658954';
$nombre = 'Rubi Duran';
$password_plain = 'Rubi123';

$usuario = createEstudiante($numero, $nombre, $password_plain);

if ($usuario) {
    echo "Usuario creado correctamente. Número: $numero - Contraseña: $password_plain";
} else {
    echo "Error al crear el usuario.";
}
