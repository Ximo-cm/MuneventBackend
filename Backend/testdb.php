<?php
$config = include('dbConf.php');

$conn = new mysqli($config['db_host'], $config['db_user'], $config['db_pass'], $config['db_name'], $config['db_port']);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}
echo "¡Conexión exitosa!";
$conn->close();
?>

