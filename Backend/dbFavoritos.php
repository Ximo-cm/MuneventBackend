<?php
class DbFavoritos
{
    private $pdo;
    public function __construct()
    {
        $config = include 'dbConf.php';
        $dsn = "mysql:host={$config['db_host']};dbname={$config['db_name']};charset=utf8mb4";
        $this->pdo = new PDO($dsn, $config['db_user'], $config['db_pass']);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    // Listar favoritos de un usuario
    public function getByUser($userId)
    {
        $stmt = $this->pdo->prepare(
            'SELECT e.* FROM eventos e 
             JOIN usuarios_favoritos uf ON e.id = uf.evento_id 
             WHERE uf.usuario_id = ?
             ORDER BY uf.fecha_guardado DESC'
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Añadir favorito
    public function add($userId, $eventId)
    {
        $stmt = $this->pdo->prepare('INSERT IGNORE INTO usuarios_favoritos (usuario_id, evento_id) VALUES (?, ?)');
        return $stmt->execute([$userId, $eventId]);
    }

    // Eliminar favorito
    public function delete($userId, $eventId)
    {
        $stmt = $this->pdo->prepare('DELETE FROM usuarios_favoritos WHERE usuario_id = ? AND evento_id = ?');
        return $stmt->execute([$userId, $eventId]);
    }
}
?>