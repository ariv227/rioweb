<?php
session_start();
if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] !== 'admin') {
    header("Location: home.php");
    exit;
}

require 'conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = mysqli_real_escape_string($conexion, $_POST['nombre']);
    $descripcion = mysqli_real_escape_string($conexion, $_POST['descripcion']);
    $precio = mysqli_real_escape_string($conexion, $_POST['precio']);
    $categoria = mysqli_real_escape_string($conexion, $_POST['categoria']);
    
    // --- LÓGICA DE LA IMAGEN ---
    $imagen = $_FILES['imagen']['name']; 
    $ruta_temporal = $_FILES['imagen']['tmp_name'];
    $carpeta_destino = 'imagenes/';
    
    // Si la carpeta 'imagenes' no existe, la creamos automáticamente
    if (!file_exists($carpeta_destino)) {
        mkdir($carpeta_destino, 0777, true);
    }
    
    // Movemos el archivo de la memoria temporal a nuestra carpeta
    $ruta_final = $carpeta_destino . basename($imagen);
    move_uploaded_file($ruta_temporal, $ruta_final);
    // ----------------------------

    $sql = "INSERT INTO productos (nombre, descripcion, precio, imagen, categoria) VALUES ('$nombre', '$descripcion', '$precio', '$imagen', '$categoria')";
    
    if (mysqli_query($conexion, $sql)) {
        echo "<script>alert('¡Producto agregado con éxito!'); window.location.href='home.php';</script>";
    } else {
        echo "Error al guardar: " . mysqli_error($conexion);
    }
}
?>