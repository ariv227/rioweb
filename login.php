<?php
session_start();
require 'conexion.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $correo = $_POST['correo'];
    $password = $_POST['password'];

    // Consultamos usuario y su ROL
    $sql = "SELECT id, nombre, password, rol FROM usuarios WHERE correo = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        $usuario = $resultado->fetch_assoc();
        
        if ($password === $usuario['password']) {
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_nombre'] = $usuario['nombre'];
            $_SESSION['usuario_rol'] = $usuario['rol']; // Guardamos el rol aquí
            
            header("Location: home.php");
            exit;
        }
    }
    echo "<script>alert('Credenciales incorrectas'); window.location.href='bienvenidos..html';</script>";
}
?>