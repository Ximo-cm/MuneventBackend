<?php
class DbCategorias
{
    private $pdo;
    public function __construct()
    {
        $config = include 'dbConf.php';
        $dsn = "mysql:host={$config['db_host']};dbname={$config['db_name']};charset=utf8mb4";
        $this->pdo = new PDO($dsn, $config['db_user'], $config['db_pass']);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    // Obtener todas las categorías (incluyendo URL de imagen)
    public function getAll()
    {
        $stmt = $this->pdo->query('SELECT id, nombre, imagen_url FROM categorias');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener categoría por ID (incluyendo URL de imagen)
    public function get($id)
    {
        $stmt = $this->pdo->prepare('SELECT id, nombre, imagen_url FROM categorias WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Crear categoría (solo nombre, imagen_url se puede actualizar después)
    public function create($nombre)
    {
        $stmt = $this->pdo->prepare('INSERT INTO categorias (nombre) VALUES (?)');
        return $stmt->execute([$nombre]);
    }

    // Actualizar categoría (solo nombre; para imagen_url usa una función aparte si la necesitas)
    public function update($id, $nombre)
    {
        $stmt = $this->pdo->prepare('UPDATE categorias SET nombre = ? WHERE id = ?');
        return $stmt->execute([$nombre, $id]);
    }

    // Eliminar categoría por ID
    public function delete($id)
    {
        $stmt = $this->pdo->prepare('DELETE FROM categorias WHERE id = ?');
        return $stmt->execute([$id]);
    }
}
?>
