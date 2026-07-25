<?php
session_start();
require 'conexion.php';

// 1. Validar autenticación
if (!isset($_SESSION['usuario_id'])) {
    header("Location: bienvenidos.html");
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$es_admin = (isset($_SESSION['rol']) && strtolower($_SESSION['rol']) === 'admin');

// 2. Validar ID del pedido
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: historial_compras.php");
    exit;
}

$pedido_id = intval($_GET['id']);

// 3. Consultar el pedido junto con los datos del usuario que compró
$sql = "SELECT p.*, u.nombre, u.apellido, u.correo 
        FROM pedidos p 
        LEFT JOIN usuarios u ON p.usuario_id = u.id 
        WHERE p.id = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $pedido_id);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    header("Location: historial_compras.php");
    exit;
}

$pedido = $res->fetch_assoc();

// 🔑 CORRECCIÓN AQUÍ:
// Permitir ver la factura si es el DUEÑO del pedido O si es ADMINISTRADOR
if (!$es_admin && $pedido['usuario_id'] != $usuario_id) {
    header("Location: panel.php");
    exit;
}

// 4. Consultar el detalle de productos del pedido (si la tabla existe)
$detalles = null;
$sql_detalles = "SELECT dp.*, pr.nombre 
                FROM detalle_pedidos dp 
                JOIN productos pr ON dp.producto_id = pr.id 
                WHERE dp.pedido_id = ?";
$stmt_detalles = $conexion->prepare($sql_detalles);
if ($stmt_detalles) {
    $stmt_detalles->bind_param("i", $pedido_id);
    $stmt_detalles->execute();
    $detalles = $stmt_detalles->get_result();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recibo #<?php echo str_pad($pedido['id'], 6, '0', STR_PAD_LEFT); ?> - Río</title>
    <link rel="stylesheet" href="estilos.css">
    <style>
        body {
            background-color: #f4f7f6;
            font-family: Arial, sans-serif;
        }
        .factura-box {
            max-width: 600px;
            margin: 40px auto;
            background: #fff;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }
        .factura-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #eef2f0;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .factura-tabla {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .factura-tabla th, .factura-tabla td {
            padding: 10px;
            border-bottom: 1px solid #f0f0f0;
            text-align: left;
        }
        .factura-tabla th {
            background: #f8fbf9;
            color: #444;
        }
        .no-print {
            margin-top: 25px;
            display: flex;
            gap: 10px;
            justify-content: space-between;
        }
        @media print {
            .no-print { display: none; }
            body { background: white; }
            .factura-box { box-shadow: none; margin: 0; width: 100%; max-width: 100%; }
        }
    </style>
</head>
<body>

    <div class="factura-box">
        <div class="factura-header">
            <div>
                <img src="imagenes/riologo.png" alt="Río Logo" style="height: 45px;">
                <h3 style="margin-top: 5px; color: #0b7c3b;">Comprobante de Compra</h3>
            </div>
            <div style="text-align: right;">
                <strong>N° Pedido:</strong> #<?php echo str_pad($pedido['id'], 6, '0', STR_PAD_LEFT); ?><br>
                <small style="color: #666;">Fecha: <?php echo date('d/m/Y h:i A', strtotime($pedido['fecha'])); ?></small>
            </div>
        </div>

        <div style="margin-bottom: 20px;">
            <h4 style="margin-bottom: 5px; color: #333;">Datos del Cliente:</h4>
            <p style="margin: 0; color: #555;">
                <strong><?php echo htmlspecialchars(($pedido['nombre'] ?? 'Cliente') . ' ' . ($pedido['apellido'] ?? '')); ?></strong><br>
                <small><?php echo htmlspecialchars($pedido['correo'] ?? 'Sin correo'); ?></small><br>
                <small>Método: <strong><?php echo htmlspecialchars($pedido['metodo_envio'] ?? 'N/A'); ?></strong></small>
            </p>
        </div>

        <table class="factura-tabla">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Cant.</th>
                    <th>Precio U.</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($detalles && $detalles->num_rows > 0): ?>
                    <?php while ($item = $detalles->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['nombre']); ?></td>
                            <td><?php echo $item['cantidad']; ?></td>
                            <td>$<?php echo number_format($item['precio_unitario'], 2); ?></td>
                            <td>$<?php echo number_format($item['cantidad'] * $item['precio_unitario'], 2); ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" style="text-align: center; color: #888; padding: 15px;">
                            Resumen de compra
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <div style="text-align: right; margin-top: 20px; font-size: 1.2rem; border-top: 2px solid #eef2f0; padding-top: 15px;">
            <strong>Total: </strong>
            <span style="color: #0b7c3b; font-weight: 800;">$<?php echo number_format($pedido['total'], 2); ?></span>
        </div>

        <div class="no-print">
            <a href="historial_compras.php" class="btn-admin" style="background: #777; text-decoration: none; padding: 10px 15px; border-radius: 8px; color: white;">← Volver al Historial</a>
            <button onclick="window.print()" class="btn-admin" style="background: #27ae60; border: none; cursor: pointer; padding: 10px 15px; border-radius: 8px; color: white;">🖨️ Imprimir Recibo</button>
        </div>
    </div>

</body>
</html>