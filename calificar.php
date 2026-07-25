<?php
session_start();
require 'conexion.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'cliente') {
    header("Location: panel.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $producto_id = intval($_POST['producto_id']);
    $usuario_id = $_SESSION['usuario_id'];
    $puntuacion = intval($_POST['puntuacion']);
    $comentario = trim($_POST['comentario'] ?? '');

    if ($puntuacion >= 1 && $puntuacion <= 5) {
        $sql = "INSERT INTO calificaciones (producto_id, usuario_id, puntuacion, comentario) 
                VALUES (?, ?, ?, ?) 
                ON DUPLICATE KEY UPDATE 
                    puntuacion = VALUES(puntuacion), 
                    comentario = VALUES(comentario)";
                    
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("iiis", $producto_id, $usuario_id, $puntuacion, $comentario);
        $stmt->execute();
    }
}

header("Location: panel.php");
exit;
?>