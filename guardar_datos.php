<?php
session_start();

$_SESSION['nombre'] = $_POST['nombre'];
$_SESSION['apellido'] = $_POST['apellido'];
$_SESSION['identificacion'] = $_POST['identificacion'];
$_SESSION['estado'] = $_POST['estado'];
$_SESSION['direccion'] = $_POST['direccion'];
$_SESSION['telefono'] = $_POST['telefono'];

header("Location: credenciales.php");
exit;
?>