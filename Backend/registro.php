<?php
// registro.php
ini_set('display_errors', 0);

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json; charset=UTF-8');

// Preflight CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$config = include __DIR__ . '/dbConf.php';

try {
    $dsn = "mysql:host={$config['db_host']};port=3306;dbname={$config['db_name']};charset=utf8mb4";
    $pdo = new PDO($dsn, $config['db_user'], $config['db_pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de conexión con la base de datos']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    http_response_code(400);
    echo json_encode(['error' => 'JSON inválido']);
    exit;
}

// Campos obligatorios
$required = ['fullName', 'email', 'username', 'password'];
foreach ($required as $field) {
    if (empty($input[$field])) {
        http_response_code(400);
        echo json_encode(['error' => "El campo {$field} es requerido"]);
        exit;
    }
}

// Validaciones
if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['error' => 'Email no válido']);
    exit;
}
if (strlen($input['username']) < 4) {
    http_response_code(400);
    echo json_encode(['error' => 'El nombre de usuario debe tener al menos 4 caracteres']);
    exit;
}
if (strlen($input['password']) < 6) {
    http_response_code(400);
    echo json_encode(['error' => 'La contraseña debe tener al menos 6 caracteres']);
    exit;
}

try {
    // Comprobar duplicados
    $stmt = $pdo->prepare('
        SELECT COUNT(*) 
        FROM usuarios 
        WHERE email = :email OR nombre_usuario = :username
    ');
    $stmt->execute([
        ':email'    => $input['email'],
        ':username' => $input['username']
    ]);
    if ($stmt->fetchColumn() > 0) {
        http_response_code(409);
        echo json_encode(['error' => 'El email o el nombre de usuario ya están en uso']);
        exit;
    }

    // Insertar nuevo usuario
    $hashed = password_hash($input['password'], PASSWORD_DEFAULT);
    $stmt = $pdo->prepare('
        INSERT INTO usuarios 
          (nombre, nombre_usuario, email, contrasena_hash, municipio, activo, fecha_creacion) 
        VALUES 
          (:fullName, :username, :email, :contrasena, :municipio, 1, NOW())
    ');
    $stmt->execute([
        ':fullName'    => $input['fullName'],
        ':username'    => $input['username'],
        ':email'       => $input['email'],
        ':contrasena'  => $hashed,
        ':municipio'   => ''      // Por defecto vacío; puedes cambiarlo si envías municipio
    ]);

    http_response_code(201);
    echo json_encode(['message' => 'Usuario registrado con éxito']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error en la base de datos: ' . $e->getMessage()]);
}
