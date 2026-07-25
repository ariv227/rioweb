<?php
session_start();
require 'conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: bienvenidos.html");
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$mensaje = '';
$tipo = '';

// Obtener saldo actual de forma segura
$saldo_actual = 0.00;
$stmt = $conexion->prepare("SELECT saldo FROM usuarios WHERE id = ?");
if ($stmt) {
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    if ($res && isset($res['saldo'])) {
        $saldo_actual = floatval($res['saldo']);
    }
}

// Procesar Formulario de Recarga
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $monto = floatval($_POST['monto']);
    $referencia = trim($_POST['referencia']);
    $banco = trim($_POST['banco']);

    if ($monto > 0 && !empty($referencia) && !empty($banco)) {
        $stmt_update = $conexion->prepare("UPDATE usuarios SET saldo = saldo + ? WHERE id = ?");
        $stmt_update->bind_param("di", $monto, $usuario_id);
        
        if ($stmt_update->execute()) {
            $saldo_actual += $monto;
            $mensaje = "✅ ¡Pago acreditado exitosamente! Se han añadido $" . number_format($monto, 2) . " a tu balance.";
            $tipo = "alert-success";
        } else {
            $mensaje = "❌ Error al procesar la recarga.";
            $tipo = "alert-danger";
        }
    } else {
        $mensaje = "⚠️ Por favor ingresa un monto válido y el número de referencia.";
        $tipo = "alert-danger";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recargar Saldo - Río</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>

    <header class="navbar">
        <div class="nav-logo">
            <img src="imagenes/riologo.png" alt="Río Logo">
        </div>
        <div class="nav-user">
            <a href="carrito.php" class="btn-admin">← Volver al Carrito</a>
        </div>
    </header>

    <main class="panel-container" style="max-width: 500px;">
        <h2>💳 Recargar Balance</h2>
        
        <div class="balance-card-header">
            <p style="margin: 0; font-size: 1rem; opacity: 0.9;">Tu Saldo Actual:</p>
            <h1 style="margin: 5px 0; font-size: 2.8rem; font-weight: 800;">$<?php echo number_format($saldo_actual, 2); ?></h1>
        </div>

        <?php if (!empty($mensaje)): ?>
            <div class="alert-message <?php echo $tipo; ?>">
                <?php echo $mensaje; ?>
            </div>
        <?php endif; ?>

        <form method="POST" style="background: white; padding: 25px; border-radius: 15px; box-shadow: 0 4px 10px rgba(0,0,0,0.05);">
            <div style="margin-bottom: 15px;">
                <label><strong>Monto a Recargar ($) *</strong></label>
                <input type="number" step="0.01" min="1" name="monto" placeholder="Ej: 20.00" required>
            </div>

            <div style="margin-bottom: 15px;">
                <label><strong>Banco de Origen *</strong></label>
                <select name="banco" required>
                    <option value="">Selecciona tu banco</option>
                    <option value="Banesco">Banesco</option>
                    <option value="Mercantil">Mercantil</option>
                    <option value="BBVA Provincial">BBVA Provincial</option>
                    <option value="Banco de Venezuela">Banco de Venezuela</option>
                    <option value="Otro">Otro Banco</option>
                </select>
            </div>

            <div style="margin-bottom: 20px;">
                <label><strong>N° de Referencia de Pago Móvil *</strong></label>
                <input type="text" name="referencia" placeholder="Ej: 849201" required>
            </div>

            <button type="submit" class="btn-submit" style="width: 100%;">
                Confirmar y Acreditar Saldo 📲
            </button>
        </form>
    </main>

</body>
</html>