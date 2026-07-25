<?php
session_start();
require 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = trim($_POST['correo']);
    $password = $_POST['password'];

    $sql = "SELECT id, nombre, apellido, correo, password, rol FROM usuarios WHERE correo = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
        if ($password === $user['password'] || password_verify($password, $user['password'])) {
            $_SESSION['usuario_id'] = $user['id'];
            $_SESSION['nombre'] = $user['nombre'];
            $_SESSION['apellido'] = $user['apellido'];
            $_SESSION['rol'] = $user['rol']; // <--- Guardamos el rol ('admin' o 'cliente')

            header("Location: panel.php");
            exit;
        } else {
            echo "<script>alert('Contraseña incorrecta'); window.location.href='bienvenidos.html';</script>";
        }
    } else {
        echo "<script>alert('El correo no está registrado'); window.location.href='bienvenidos.html';</script>";
    }
}
?>