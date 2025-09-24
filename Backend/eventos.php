<?php
// eventos.php

// 1) Configuración de errores (solo log, no salida al cliente)
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php-error.log');

// 2) Limpia cualquier salida previa
if (ob_get_length()) {
    ob_clean();
}

// 3) Cabeceras CORS y JSON
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json; charset=UTF-8');

// Responde al preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require 'dbEventos.php';
$db = new DbEventos();
global $pdo;

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {

    case 'GET':
        // Filtrar por creador_id
        if (isset($_GET['creador_id']) && is_numeric($_GET['creador_id'])) {
            $uid = (int) $_GET['creador_id'];
            $all = $db->getAll();
            $filtered = array_filter($all, fn($e) =>
                isset($e['creador_id']) && (int)$e['creador_id'] === $uid
            );
            echo json_encode(array_values($filtered));
        }
        // Obtener detalle por id
        elseif (isset($_GET['id']) && is_numeric($_GET['id'])) {
            echo json_encode($db->get((int)$_GET['id']));
        }
        // Obtener todos
        else {
            echo json_encode($db->getAll());
        }
        exit;

    case 'POST':
        // 1) Autenticación y rol
        $userId = intval($_POST['user_id'] ?? 0);
        if ($userId <= 0) {
            http_response_code(401);
            echo json_encode(['error' => 'No autenticado']);
            exit;
        }
        $stmt = $pdo->prepare('SELECT role FROM usuarios WHERE id = ?');
        $stmt->execute([$userId]);
        $usr = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$usr || $usr['role'] !== 'admin') {
            http_response_code(403);
            echo json_encode(['error' => 'Permiso denegado']);
            exit;
        }

        // 2) Recoger y validar datos mínimos
        $titulo = trim($_POST['titulo'] ?? '');
        $categoria_id = intval($_POST['categoria_id'] ?? 0);
        $fecha_inicio = trim($_POST['fecha_inicio'] ?? '');
        if ($titulo === '' || $categoria_id <= 0 || $fecha_inicio === '') {
            http_response_code(400);
            echo json_encode(['error' => 'Faltan campos obligatorios']);
            exit;
        }

        // 3) Montar input para la base
        $input = [
            'titulo'         => $titulo,
            'descripcion'    => trim($_POST['descripcion'] ?? ''),
            'lugar_nombre'   => trim($_POST['lugar_nombre'] ?? ''),
            'categoria_id'   => $categoria_id,
            'municipio'      => trim($_POST['municipio'] ?? ''),
            'fecha_inicio'   => $fecha_inicio,
            'fecha_fin'      => trim($_POST['fecha_fin'] ?? ''),
            'precio'         => trim($_POST['precio'] ?? ''),
            'recomendaciones'=> trim($_POST['recomendaciones'] ?? ''),
            'organizador'    => trim($_POST['organizador'] ?? ''),
            'latitud'        => trim($_POST['latitud'] ?? ''),
            'longitud'       => trim($_POST['longitud'] ?? ''),
            'creador_id'     => $userId
        ];

        // 4) Crear vs Actualizar (sin manejar imágenes por ahora)
        if (!empty($_POST['id']) && is_numeric($_POST['id'])) {
            // Actualizar
            $id = (int) $_POST['id'];
            try {
                $db->update($id, $input);
                echo json_encode(['message' => 'Evento actualizado']);
            } catch (Exception $e) {
                error_log('Error al actualizar evento: ' . $e->getMessage());
                http_response_code(500);
                echo json_encode(['error' => 'Error al actualizar el evento']);
            }
        } else {
            // Crear
            try {
                $db->create($input);
                echo json_encode(['message' => 'Evento creado']);
            } catch (Exception $e) {
                error_log('Error al crear evento: ' . $e->getMessage());
                http_response_code(500);
                echo json_encode(['error' => 'Error al crear el evento']);
            }
        }
        exit;

    case 'DELETE':
        parse_str(file_get_contents('php://input'), $del);
        if (empty($del['id']) || !is_numeric($del['id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Falta id']);
            exit;
        }
        try {
            $db->delete((int)$del['id']);
            echo json_encode(['message' => 'Evento eliminado']);
        } catch (Exception $e) {
            error_log('Error al eliminar evento: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Error al eliminar el evento']);
        }
        exit;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Método no permitido']);
        exit;
}
