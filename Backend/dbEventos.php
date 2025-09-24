<?php
class DbEventos
{
    private $pdo;
    public function __construct()
    {
        $config = include 'dbConf.php';
        $dsn = "mysql:host={$config['db_host']};dbname={$config['db_name']};charset=utf8mb4";
        $this->pdo = new PDO($dsn, $config['db_user'], $config['db_pass']);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    // Obtener todos los eventos
    public function getAll()
    {
        $stmt = $this->pdo->query(
            'SELECT * FROM eventos ORDER BY fecha_inicio DESC'
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener evento por ID
    public function get($id)
    {
        $stmt = $this->pdo->prepare('SELECT * FROM eventos WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Crear evento
    public function create($data)
    {
        $sql = 'INSERT INTO eventos (
            titulo,
            descripcion,
            fecha_inicio,
            fecha_fin,
            lugar_nombre,
            municipio,
            direccion,
            latitud,
            longitud,
            categoria_id,
            precio,
            url_entradas,
            imagen_url,
            organizador,
            recomendaciones,
            etiquetas,
            destacado
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $data['titulo'],
            $data['descripcion'],
            $data['fecha_inicio'],
            $data['fecha_fin'],
            $data['lugar_nombre'],
            $data['municipio'],
            $data['direccion'],
            $data['latitud'],
            $data['longitud'],
            $data['categoria_id'],
            $data['precio'],
            $data['url_entradas'],
            $data['imagen_url'],
            $data['organizador'],
            $data['recomendaciones'],
            $data['etiquetas'],
            $data['destacado']
        ]);
    }

    // Actualizar evento
    public function update($id, $data)
    {
        $sql = 'UPDATE eventos SET
            titulo        = ?,
            descripcion   = ?,
            fecha_inicio  = ?,
            fecha_fin     = ?,
            lugar_nombre  = ?,
            municipio     = ?,
            direccion     = ?,
            latitud       = ?,
            longitud      = ?,
            categoria_id  = ?,
            precio        = ?,
            url_entradas  = ?,
            imagen_url    = ?,
            organizador   = ?,
            recomendaciones = ?,
            etiquetas     = ?,
            destacado     = ?
        WHERE id = ?';
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $data['titulo'],
            $data['descripcion'],
            $data['fecha_inicio'],
            $data['fecha_fin'],
            $data['lugar_nombre'],
            $data['municipio'],
            $data['direccion'],
            $data['latitud'],
            $data['longitud'],
            $data['categoria_id'],
            $data['precio'],
            $data['url_entradas'],
            $data['imagen_url'],
            $data['organizador'],
            $data['recomendaciones'],
            $data['etiquetas'],
            $data['destacado'],
            $id
        ]);
    }

    // Eliminar evento
    public function delete($id)
    {
        $stmt = $this->pdo->prepare('DELETE FROM eventos WHERE id = ?');
        return $stmt->execute([$id]);
    }
}
?>
