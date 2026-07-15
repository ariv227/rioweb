<?php
session_start();
require 'conexion.php';

if (!isset($_SESSION['nombre'])) {
    header("Location: datosbasicos.php");
    exit;
}

$password = $_POST['password'];
$confirmPassword = $_POST['confirmPassword'];

if ($password !== $confirmPassword) {
    header("Location: credenciales.php?error=" . urlencode("Las contraseñas no coinciden"));
    exit;
}

$nombre = $_SESSION['nombre'];
$apellido = $_SESSION['apellido'];
$identificacion = $_SESSION['identificacion'];
$estado = $_SESSION['estado'];
$direccion = $_SESSION['direccion'];
$telefono = $_SESSION['telefono'];

$correo = $_POST['correo'];

$sql = "INSERT INTO usuarios (nombre, apellido, identificacion, estado, direccion, telefono, correo, password)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conexion->prepare($sql);
$stmt->bind_param(
    "ssssssss",
    $nombre,
    $apellido,
    $identificacion,
    $estado,
    $direccion,
    $telefono,
    $correo,
    $password
);

if ($stmt->execute()) {
    session_destroy();
    header("Location: cuentacreada.html");
    exit;
} else {
    header("Location: credenciales.php?error=" . urlencode("Ese correo o identificación ya está registrado"));
    exit;
}
?>