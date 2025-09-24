<?php
class DbPasswordReset
{
    private $pdo;
    public function __construct()
    {
        $config = include 'dbConf.php';
        $dsn = "mysql:host={$config['db_host']};dbname={$config['db_name']};charset=utf8mb4";
        $this->pdo = new PDO($dsn, $config['db_user'], $config['db_pass']);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    /**
     * Guarda un token de restablecimiento asociado a un usuario.
     * @param int $userId
     * @param string $token
     * @param string $expires  Fecha y hora de expiración (Y-m-d H:i:s)
     */
    public function createToken(int $userId, string $token, string $expires): bool
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)'
        );
        return $stmt->execute([$userId, $token, $expires]);
    }

    /**
     * Recupera el registro de restablecimiento por token (si existe y no expiró).
     * @param string $token
     * @return array|false  Devuelve ['user_id'=>…, 'expires_at'=>…] o false si no existe.
     */
    public function getByToken(string $token)
    {
        $stmt = $this->pdo->prepare(
            'SELECT user_id, expires_at FROM password_resets WHERE token = ?'
        );
        $stmt->execute([$token]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) return false;
        // Verificar expiración
        if (new DateTime() > new DateTime($row['expires_at'])) {
            return false;
        }
        return $row;
    }

    /**
     * Marca un token como usado (o lo borra).
     * @param string $token
     */
    public function invalidateToken(string $token): bool
    {
        $stmt = $this->pdo->prepare(
            'DELETE FROM password_resets WHERE token = ?'
        );
        return $stmt->execute([$token]);
    }

    /**
     * Actualiza la contraseña de un usuario (ya hasheada).
     * @param int $userId
     * @param string $hashedPassword
     */
    public function updatePassword(int $userId, string $hashedPassword): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE usuarios SET password = ? WHERE id = ?'
        );
        return $stmt->execute([$hashedPassword, $userId]);
    }
}
?>
