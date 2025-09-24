<?php
// login.php (con municipio y role)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json; charset=UTF-8');

// Preflight CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Solo POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

// Cargar configuración
$configPath = __DIR__ . '/dbConf.php';
if (!file_exists($configPath)) {
    http_response_code(500);
    echo json_encode(['error' => "No se encuentra dbConf.php en $configPath"]);
    exit;
}
$config = include $configPath;

// Conexión a la BD
try {
    $dsn = "mysql:host={$config['db_host']};port=3306;dbname={$config['db_name']};charset=utf8mb4";
    $pdo = new PDO($dsn, $config['db_user'], $config['db_pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error conexión BD: ' . $e->getMessage()]);
    exit;
}

// Leer JSON de entrada
$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) {
    http_response_code(400);
    echo json_encode(['error' => 'JSON inválido o no recibido']);
    exit;
}

$userInput = trim($input['username'] ?? '');
$passInput = trim($input['password'] ?? '');

if ($userInput === '' || $passInput === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Usuario y contraseña son requeridos']);
    exit;
}

try {
    // Ahora seleccionamos también municipio y role, y la columna de hash de la contraseña
    $stmt = $pdo->prepare(
        'SELECT id, nombre, nombre_usuario, email, municipio, role, contrasena_hash
         FROM usuarios
         WHERE nombre_usuario = :user
         LIMIT 1'
    );
    $stmt->execute([':user' => $userInput]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        http_response_code(401);
        echo json_encode(['error' => 'Usuario o contraseña incorrectos']);
        exit;
    }

    // Verificamos con password_verify
    if (!password_verify($passInput, $row['contrasena_hash'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Usuario o contraseña incorrectos']);
        exit;
    }

    // Preparamos la respuesta sin exponer el hash
    unset($row['contrasena_hash']);
    http_response_code(200);
    echo json_encode([
        'message' => 'Login correcto',
        'user'    => [
            'id'            => $row['id'],
            'nombre'        => $row['nombre'],
            'nombre_usuario'=> $row['nombre_usuario'],
            'email'         => $row['email'],
            'municipio'     => $row['municipio'],
            'role'          => $row['role']
        ]
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error en la consulta: ' . $e->getMessage()]);
    exit;
}
