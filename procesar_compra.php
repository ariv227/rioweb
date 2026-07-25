<?php
session_start();
require 'conexion.php';

if (!isset($_SESSION['usuario_id']) || empty($_SESSION['carrito'])) {
    header("Location: panel.php");
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

// Obtener método de envío desde el formulario de carrito (Delivery o Pickup)
$costo_envio = isset($_POST['metodo_envio']) ? floatval($_POST['metodo_envio']) : 0.00;
$metodo_envio = ($costo_envio > 0) ? 'Delivery' : 'Pickup';

// 1. INICIAR LA TRANSACCIÓN SQL
$conexion->begin_transaction();

try {
    // A) Bloquear y consultar el saldo del cliente
    $sql_saldo = "SELECT saldo FROM usuarios WHERE id = ? FOR UPDATE";
    $stmt_saldo = $conexion->prepare($sql_saldo);
    $stmt_saldo->bind_param("i", $usuario_id);
    $stmt_saldo->execute();
    $user_data = $stmt_saldo->get_result()->fetch_assoc();

    if (!$user_data) {
        throw new Exception("Usuario no encontrado.");
    }

    $saldo_disponible = floatval($user_data['saldo']);

    // B) Calcular el subtotal y verificar el stock de los productos
    $subtotal = 0;
    $detalles_compra = [];

    foreach ($_SESSION['carrito'] as $producto_id => $cantidad_solicitada) {
        // Bloquear la fila del producto para evitar compras simultáneas sin stock
        $sql_check = "SELECT stock, nombre, precio FROM productos WHERE id = ? FOR UPDATE";
        $stmt = $conexion->prepare($sql_check);
        $stmt->bind_param("i", $producto_id);
        $stmt->execute();
        $producto = $stmt->get_result()->fetch_assoc();

        if (!$producto) {
            throw new Exception("El producto seleccionado ya no existe.");
        }

        if ($producto['stock'] < $cantidad_solicitada) {
            throw new Exception("Stock insuficiente para '" . $producto['nombre'] . "'. Disponible: " . $producto['stock']);
        }

        $precio_unitario = floatval($producto['precio']);
        $subtotal += ($precio_unitario * $cantidad_solicitada);

        $detalles_compra[] = [
            'producto_id' => $producto_id,
            'cantidad' => $cantidad_solicitada,
            'precio_unitario' => $precio_unitario
        ];
    }

    $total_compra = $subtotal + $costo_envio;

    // C) Validar si el cliente tiene saldo suficiente
    if ($saldo_disponible < $total_compra) {
        throw new Exception("Saldo insuficiente. Tienes $" . number_format($saldo_disponible, 2) . " y el total a pagar es $" . number_format($total_compra, 2));
    }

    // D) Descontar el dinero del saldo del usuario
    $sql_update_saldo = "UPDATE usuarios SET saldo = saldo - ? WHERE id = ?";
    $stmt_up_saldo = $conexion->prepare($sql_update_saldo);
    $stmt_up_saldo->bind_param("di", $total_compra, $usuario_id);
    $stmt_up_saldo->execute();

    // E) Descontar el stock de los productos
    $sql_update_stock = "UPDATE productos SET stock = stock - ? WHERE id = ?";
    $stmt_up_stock = $conexion->prepare($sql_update_stock);

    foreach ($detalles_compra as $item) {
        $stmt_up_stock->bind_param("ii", $item['cantidad'], $item['producto_id']);
        $stmt_up_stock->execute();
    }

    // F) Registrar la cabecera del Pedido
    $sql_pedido = "INSERT INTO pedidos (usuario_id, total, metodo_envio, costo_envio) VALUES (?, ?, ?, ?)";
    $stmt_pedido = $conexion->prepare($sql_pedido);
    $stmt_pedido->bind_param("idds", $usuario_id, $total_compra, $metodo_envio, $costo_envio);
    $stmt_pedido->execute();
    $pedido_id = $conexion->insert_id;

    // G) Registrar el detalle del Pedido
    $sql_detalle = "INSERT INTO detalle_pedidos (pedido_id, producto_id, cantidad, precio_unitario) VALUES (?, ?, ?, ?)";
    $stmt_det = $conexion->prepare($sql_detalle);

    foreach ($detalles_compra as $item) {
        $stmt_det->bind_param("iiid", $pedido_id, $item['producto_id'], $item['cantidad'], $item['precio_unitario']);
        $stmt_det->execute();
    }

    // 2. CONFIRMAR LA TRANSACCIÓN
    $conexion->commit();
    unset($_SESSION['carrito']); // Limpiar el carrito de compras

    // Redirigir directamente a la Factura Digital / PDF
    header("Location: factura.php?id=" . $pedido_id);
    exit;

} catch (Exception $e) {
    // 3. REVERTIR TODOS LOS CAMBIOS SI OCURRIÓ UN ERROR
    $conexion->rollback();
    header("Location: carrito.php?error=" . urlencode($e->getMessage()));
    exit;
}
?>