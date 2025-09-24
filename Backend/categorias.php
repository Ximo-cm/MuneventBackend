<?php
ini_set('display_errors', 1);
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json; charset=UTF-8');
require 'dbCategorias.php';
$db = new DbCategorias();
$method = $_SERVER['REQUEST_METHOD'];
$request = explode('/', trim($_SERVER['PATH_INFO'] ?? '', '/'));
switch ($method) {
    case 'GET':
        if (isset($request[0]) && is_numeric($request[0])) {
            $data = $db->get(intval($request[0]));
        } else {
            $data = $db->getAll();
        }
        echo json_encode($data);
        break;
    case 'POST':
        $input = json_decode(file_get_contents('php://input'), true);
        $db->create($input['nombre']);
        echo json_encode(['message' => 'Categoría creada']);
        break;
    case 'PUT':
        parse_str(file_get_contents('php://input'), $data);
        $db->update(intval($data['id']), $data['nombre']);
        echo json_encode(['message' => 'Categoría actualizada']);
        break;
    case 'DELETE':
        parse_str(file_get_contents('php://input'), $data);
        $db->delete(intval($data['id']));
        echo json_encode(['message' => 'Categoría eliminada']);
        break;
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Método no permitido']);
        break;
}
?>