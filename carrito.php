<?php
session_start();
require 'conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: bienvenidos.html");
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

// Obtener saldo del cliente
$stmt = $conexion->prepare("SELECT saldo FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$saldo_usuario = $stmt->get_result()->fetch_assoc()['saldo'] ?? 0.00;

$carrito = $_SESSION['carrito'] ?? [];
$productos_carrito = [];
$subtotal_compra = 0;

if (!empty($carrito)) {
    $ids = implode(',', array_map('intval', array_keys($carrito)));
    $sql = "SELECT * FROM productos WHERE id IN ($ids)";
    $resultado = $conexion->query($sql);

    while ($prod = $resultado->fetch_assoc()) {
        $prod['cantidad'] = $carrito[$prod['id']];
        $prod['subtotal'] = $prod['precio'] * $prod['cantidad'];
        $subtotal_compra += $prod['subtotal'];
        $productos_carrito[] = $prod;
    }
}

// Vaciar carrito
if (isset($_GET['vaciar'])) {
    unset($_SESSION['carrito']);
    header("Location: carrito.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Carrito - Río</title>
    <link rel="stylesheet" href="estilos.css">
    <script>
        function actualizarTotal() {
            let subtotal = <?php echo $subtotal_compra; ?>;
            let envio = parseFloat(document.querySelector('input[name="metodo_envio"]:checked').value);
            let total = subtotal + envio;
            
            document.getElementById('monto-envio').textContent = '$' + envio.toFixed(2);
            document.getElementById('monto-total').textContent = '$' + total.toFixed(2);
        }
    </script>
</head>
<body>

    <header class="navbar">
        <div class="nav-logo">
            <img src="imagenes/riologo.png" alt="Río Logo">
        </div>
        <div class="nav-user">
            <a href="panel.php" class="btn-admin">← Seguir Comprando</a>
            <a href="recargar_saldo.php" style="background:#27ae60; color:white; padding:8px 15px; border-radius:10px; text-decoration:none; font-weight:bold;">
                💳 Mi Saldo: $<?php echo number_format($saldo_usuario, 2); ?>
            </a>
        </div>
    </header>

    <main class="panel-container">
        <h2>Mi Carrito de Compras</h2>

        <?php if (isset($_GET['error'])): ?>
            <div style="background-color: #f8d7da; color: #721c24; padding: 15px; border-radius: 10px; margin-bottom: 20px; border:1px solid #f5c6cb;">
                ⚠️ <?php echo htmlspecialchars($_GET['error']); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($productos_carrito)): ?>
            <form action="procesar_compra.php" method="POST">
                <table style="width:100%; border-collapse: collapse; margin-top:20px; background: white; border-radius:15px; overflow:hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.05);">
                    <thead>
                        <tr style="background:#0b7c3b; color:white; text-align:left;">
                            <th style="padding:15px;">Producto</th>
                            <th style="padding:15px;">Precio Unit.</th>
                            <th style="padding:15px;">Cantidad</th>
                            <th style="padding:15px;">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($productos_carrito as $item): ?>
                            <tr style="border-bottom: 1px solid #eee;">
                                <td style="padding:15px;"><strong><?php echo htmlspecialchars($item['nombre']); ?></strong></td>
                                <td style="padding:15px;">$<?php echo number_format($item['precio'], 2); ?></td>
                                <td style="padding:15px;"><?php echo $item['cantidad']; ?> unidades</td>
                                <td style="padding:15px; font-weight:bold; color:#0b7c3b;">$<?php echo number_format($item['subtotal'], 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <!-- OPCIÓN DE RETIRO / ENVÍO -->
                <div style="background: white; padding: 20px; border-radius: 15px; margin-top: 20px; box-shadow: 0 4px 10px rgba(0,0,0,0.05);">
                    <h3>🚚 Método de Entrega:</h3>
                    <div style="display:flex; gap: 20px; margin-top: 10px;">
                        <label style="background:#f8f9fa; padding: 12px 20px; border-radius: 10px; border:1px solid #ddd; cursor:pointer;">
                            <input type="radio" name="metodo_envio" value="0.00" checked onchange="actualizarTotal()"> 
                            <strong>Pickup en Tienda</strong> ($0.00)
                        </label>
                        <label style="background:#f8f9fa; padding: 12px 20px; border-radius: 10px; border:1px solid #ddd; cursor:pointer;">
                            <input type="radio" name="metodo_envio" value="3.00" onchange="actualizarTotal()"> 
                            <strong>Delivery a Domicilio</strong> (+$3.00)
                        </label>
                    </div>
                </div>

                <!-- RESUMEN DE TOTAL Y BOTÓN DE COMPRA -->
                <div style="display:flex; justify-content: space-between; align-items:center; margin-top: 20px; background:white; padding: 20px; border-radius:15px; box-shadow: 0 4px 10px rgba(0,0,0,0.05);">
                    <div>
                        <p style="margin: 0; color:#666;">Subtotal: <strong>$<?php echo number_format($subtotal_compra, 2); ?></strong></p>
                        <p style="margin: 5px 0; color:#666;">Costo Envíos: <strong id="monto-envio">$0.00</strong></p>
                        <h2 style="margin: 0;">Total a Pagar: <span id="monto-total" style="color:#0b7c3b;">$<?php echo number_format($subtotal_compra, 2); ?></span></h2>
                    </div>

                    <div>
                        <a href="carrito.php?vaciar=1" style="color:#e74c3c; margin-right:15px; text-decoration:none; font-weight:600;">Vaciar Carrito</a>
                        
                        <?php if ($saldo_usuario >= $subtotal_compra): ?>
                            <button type="submit" class="btn-submit" style="padding: 12px 25px; background:#0b7c3b; color:white; border:none; border-radius:10px; font-weight:bold; cursor:pointer;">
                                Confirmar y Pagar 💳
                            </button>
                        <?php else: ?>
                            <div style="text-align:right;">
                                <p style="color:#e74c3c; font-size:0.85rem; margin-bottom:5px;">⚠️ Saldo insuficiente ($<?php echo number_format($saldo_usuario, 2); ?>)</p>
                                <a href="recargar_saldo.php" style="background:#f39c12; color:white; padding:10px 18px; border-radius:10px; text-decoration:none; font-weight:bold; font-size:0.9rem;">
                                    ➕ Recargar Saldo
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </form>
        <?php else: ?>
            <p style="margin-top:20px;">Tu carrito está vacío.</p>
        <?php endif; ?>
    </main>

</body>
</html>