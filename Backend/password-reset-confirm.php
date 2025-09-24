<?php
ini_set('display_errors', 1);
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json; charset=UTF-8');

require 'DbPasswordReset.php';
require 'DbUsuarios.php';
$dbReset    = new DbPasswordReset();
$dbUsuarios = new DbUsuarios();

$method = $_SERVER['REQUEST_METHOD'];
if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $token       = $input['token']       ?? '';
    $newPassword = $input['newPassword'] ?? '';

    // Validar token
    $record = $dbReset->getByToken($token);
    if (!$record) {
        http_response_code(400);
        echo json_encode(['error' => 'Token inválido o caducado']);
        exit;
    }

    // Hash y actualización
    $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
    $dbUsuarios->updatePassword($record['user_id'], $hashed);
    $dbReset->invalidateToken($token);

    echo json_encode(['message' => 'Contraseña restablecida con éxito']);
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
}
