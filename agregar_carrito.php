<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: bienvenidos.html");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $producto_id = intval($_POST['producto_id']);
    $cantidad = intval($_POST['cantidad']);

    if ($cantidad > 0) {
        if (!isset($_SESSION['carrito'])) {
            $_SESSION['carrito'] = [];
        }

        // Si ya está en el carrito, sumamos la cantidad
        if (isset($_SESSION['carrito'][$producto_id])) {
            $_SESSION['carrito'][$producto_id] += $cantidad;
        } else {
            $_SESSION['carrito'][$producto_id] = $cantidad;
        }
    }
}

header("Location: panel.php");
exit;
?>