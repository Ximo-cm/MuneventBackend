<?php
ini_set('display_errors', 1);
header('Allow: GET, POST, PUT, DELETE');
header('Content-Type: application/json; charset=UTF-8');

require 'dbUsuarios.php';
$db = new DbUsuarios();
$method = $_SERVER['REQUEST_METHOD'];
$request = explode('/', trim($_SERVER['PATH_INFO'] ?? '', '/'));

switch ($method) {
    case 'GET':
        if (isset($request[0]) && is_numeric($request[0])) {
            $user = $db->get(intval($request[0]));
            echo json_encode($user);
        } else {
            $users = $db->getAll();
            echo json_encode($users);
        }
        break;

    case 'POST':
        $input = json_decode(file_get_contents('php://input'), true);
        $db->create($input['nombre'], $input['email'], $input['contrasena'], $input['municipio']);
        echo json_encode(['message' => 'Usuario creado']);
        break;

    case 'PUT':
        parse_str(file_get_contents('php://input'), $data);
        $id = intval($_GET['id']);
        $db->update($id, $data['nombre'], $data['email'], $data['municipio'], $data['activo']);
        echo json_encode(['message' => 'Usuario actualizado']);
        break;

    case 'DELETE':
        $id = intval($_GET['id']);
        $db->delete($id);
        echo json_encode(['message' => 'Usuario eliminado']);
        break;

    default:
        header('HTTP/1.1 405 Method Not Allowed');
        break;
}
?>