<?php
class DbUsuarios
{
    private $pdo;
    public function __construct()
    {
        $config = include 'dbConf.php';
        $dsn = "mysql:host={$config['db_host']};dbname={$config['db_name']};charset=utf8mb4";
        $this->pdo = new PDO($dsn, $config['db_user'], $config['db_pass']);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    // Obtener todos los usuarios
    public function getAll()
    {
        $stmt = $this->pdo->query('SELECT id, nombre, email, municipio, activo, fecha_creacion FROM usuarios');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener usuario por ID
    public function get($id)
    {
        $stmt = $this->pdo->prepare('SELECT id, nombre, email, municipio, activo, fecha_creacion FROM usuarios WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Obtiene el usuario por su correo electrónico.
    public function getByEmail(string $email)
    {
        $stmt = $this->pdo->prepare('SELECT id, nombre, email, municipio, activo, fecha_creacion FROM usuarios WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: false;
    }

    // Crear usuario
    public function create($nombre, $email, $contrasena, $municipio)
    {
        $stmt = $this->pdo->prepare('INSERT INTO usuarios (nombre, email, contrasena, municipio) VALUES (?, ?, ?, ?)');
        return $stmt->execute([$nombre, $email, $contrasena, $municipio]);
    }

    // Actualizar usuario
    public function update($id, $nombre, $email, $municipio, $activo)
    {
        $stmt = $this->pdo->prepare('UPDATE usuarios SET nombre = ?, email = ?, municipio = ?, activo = ? WHERE id = ?');
        return $stmt->execute([$nombre, $email, $municipio, $activo, $id]);
    }

    // Actualizar contraseña
    public function updatePassword(int $userId, string $hashedPassword): bool
    {
        $stmt = $this->pdo->prepare('UPDATE usuarios SET password = ? WHERE id = ?');
        return $stmt->execute([$hashedPassword, $userId]);
    }

    // Eliminar usuario
    public function delete($id)
    {
        $stmt = $this->pdo->prepare('DELETE FROM usuarios WHERE id = ?');
        return $stmt->execute([$id]);
    }


}
?>