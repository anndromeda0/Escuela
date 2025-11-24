<?php
require __DIR__ . '/../../vendor/autoload.php';
use GuzzleHttp\Client;

// Configuración Supabase
$SUPABASE_URL = 'https://vxorllitblvcfjtofwdl.supabase.co';
$SUPABASE_KEY = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InZ4b3JsbGl0Ymx2Y2ZqdG9md2RsIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NjA4ODg0NDYsImV4cCI6MjA3NjQ2NDQ0Nn0.5RztDY6R6szpUjIl6OB6fYZhXnTlvIikvNrKHcHXPhc';

$client = new Client([
    'base_uri' => $SUPABASE_URL,
    'headers' => [
        'apikey' => $SUPABASE_KEY,
        'Authorization' => "Bearer $SUPABASE_KEY",
        'Content-Type' => 'application/json',
    ],
]);


function createEstudiante($numero, $nombre, $password, $email = null) {
    global $client;
    $body = [
        'NumeroEstudiante' => $numero,
        'Nombre' => $nombre,
        'Password' => password_hash($password, PASSWORD_DEFAULT),
        'Email' => $email
    ];
    $response = $client->post('/rest/v1/estudiantes', [
        'json' => $body
    ]);
    return json_decode($response->getBody(), true);
}
