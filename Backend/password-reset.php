<?php
ini_set('display_errors', 1);
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json; charset=UTF-8');

require 'DbPasswordReset.php';
require 'DbUsuarios.php';   // Para comprobar que existe el email
$dbReset    = new DbPasswordReset();
$dbUsuarios = new DbUsuarios();

$method = $_SERVER['REQUEST_METHOD'];
if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $email = $input['email'] ?? '';
    $user  = $dbUsuarios->getByEmail($email);
    if (!$user) {
        http_response_code(404);
        echo json_encode(['error' => 'Usuario no encontrado']);
        exit;
    }

    // Generar token y caducidad
    $token   = bin2hex(random_bytes(16));
    $expires = date('Y-m-d H:i:s', time() + 3600); // 1 hora
    $dbReset->createToken($user['id'], $token, $expires);

    // Aquí enviarías el email con el enlace /password-reset/{token}
    // mail( ... );

    echo json_encode(['message' => 'Correo de restablecimiento enviado']);
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
}
