<?php

session_start();
require 'db.php';

echo $numero = $_POST['numero'] ?? '';
echo $password = $_POST['password'] ?? '';


$estudiante = getEstudiante($numero);


if ($estudiante) {
    if ($password=== $estudiante['Password']) {
        session_regenerate_id(true);
        $_SESSION['user'] = [
            'numero' => $estudiante['NumeroEstudiante'],
            'nombre' => $estudiante['Nombre'],
            'correoInstitucional' => $estudiante['InstitucionalEmail'],
            'telefono' => $estudiante['NumeroCelular'],
            'estado' => $estudiante['Estado']

        ];
        header('Location: admin_view.php');
        exit;
    } else {
        header('Location: admin.php?error=' . urlencode('Usuario o contraseña incorrecta.'));
        exit;
    }
} else {
    header('Location: admin.php?error=' . urlencode('Usuario no encontrado.'));
    exit;
}



function getEstudiante($numeroEstudiante) {
    global $client;
    $response = $client->get('/rest/v1/Admins', [
        'query' => [
            'select' => '*',
            'Codigo' => 'eq.' . $numeroEstudiante
        ]
    ]);
    $data = json_decode($response->getBody(), true);
    return $data[0] ?? null;
}
