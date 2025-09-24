<?php
// favoritos.php
ini_set('display_errors', 0);
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json; charset=UTF-8');

if ($_SERVER['REQUEST_METHOD']==='OPTIONS') {
  http_response_code(200);
  exit;
}

// Carga la configuración correctamente
$config = include __DIR__ . '/dbConf.php';

$dsn = "mysql:host={$config['db_host']};port=3306;dbname={$config['db_name']};charset=utf8mb4";
$pdo = new PDO($dsn, $config['db_user'], $config['db_pass'], [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);

$userId = intval($_GET['user_id'] ?? 0);
if ($userId<=0) {
  http_response_code(400);
  echo json_encode(['error'=>'user_id requerido']);
  exit;
}

switch($_SERVER['REQUEST_METHOD']) {
  case 'GET':
    $stmt = $pdo->prepare("
      SELECT e.id, e.titulo, e.imagen_url, e.lugar_nombre, e.fecha_inicio
      FROM eventos e
      JOIN usuarios_favoritos uf ON uf.evento_id = e.id
      WHERE uf.usuario_id = :uid
      ORDER BY e.fecha_inicio
    ");
    $stmt->execute([':uid'=>$userId]);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    break;

  case 'POST':
    $input = json_decode(file_get_contents('php://input'), true);
    $evt = intval($input['evento_id'] ?? 0);
    if (!$evt) {
      http_response_code(400);
      echo json_encode(['error'=>'evento_id requerido']);
      exit;
    }
    $stmt = $pdo->prepare("
      INSERT IGNORE INTO usuarios_favoritos(usuario_id,evento_id)
      VALUES(:uid,:eid)
    ");
    $stmt->execute([':uid'=>$userId,':eid'=>$evt]);
    echo json_encode(['message'=>'Favorito añadido']);
    break;

  case 'DELETE':
    $input = json_decode(file_get_contents('php://input'), true);
    $evt = intval($input['evento_id'] ?? 0);
    if (!$evt) {
      http_response_code(400);
      echo json_encode(['error'=>'evento_id requerido']);
      exit;
    }
    $stmt = $pdo->prepare("
      DELETE FROM usuarios_favoritos
      WHERE usuario_id = :uid AND evento_id = :eid
    ");
    $stmt->execute([':uid'=>$userId,':eid'=>$evt]);
    echo json_encode(['message'=>'Favorito eliminado']);
    break;

  default:
    http_response_code(405);
    echo json_encode(['error'=>'Método no permitido']);
    break;
}

