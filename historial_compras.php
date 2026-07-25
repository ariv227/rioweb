<?php
session_start();
require 'conexion.php';

// Validar que el usuario esté autenticado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: bienvenidos.html");
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

// Determinar si es administrador evaluando la sesión
$es_admin = (isset($_SESSION['rol']) && strtolower($_SESSION['rol']) === 'admin');

// Capturar las fechas de filtro enviadas por el formulario
$fecha_desde = $_GET['fecha_desde'] ?? '';
$fecha_hasta = $_GET['fecha_hasta'] ?? '';

// Construir la consulta SQL dinámica
$condiciones = [];
$parametros = [];
$tipos = "";

// Si NO es admin, filtrar estrictamente por su propio ID de usuario
if (!$es_admin) {
    $condiciones[] = "p.usuario_id = ?";
    $parametros[] = $usuario_id;
    $tipos .= "i";
}

// Filtro: Fecha desde
if (!empty($fecha_desde)) {
    $condiciones[] = "DATE(p.fecha) >= ?";
    $parametros[] = $fecha_desde;
    $tipos .= "s";
}

// Filtro: Fecha hasta
if (!empty($fecha_hasta)) {
    $condiciones[] = "DATE(p.fecha) <= ?";
    $parametros[] = $fecha_hasta;
    $tipos .= "s";
}

// Armar SQL final con JOIN a la tabla de usuarios
$sql = "SELECT p.*, u.nombre, u.apellido, u.correo 
        FROM pedidos p 
        LEFT JOIN usuarios u ON p.usuario_id = u.id";

if (count($condiciones) > 0) {
    $sql .= " WHERE " . implode(" AND ", $condiciones);
}

$sql .= " ORDER BY p.fecha DESC";

$stmt = $conexion->prepare($sql);

if (count($parametros) > 0) {
    $stmt->bind_param($tipos, ...$parametros);
}

$stmt->execute();
$pedidos = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Movimientos - Río</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>

    <header class="navbar">
        <div class="nav-logo">
            <img src="imagenes/riologo.png" alt="Río Logo">
        </div>
        <div class="nav-user">
            <a href="panel.php" class="btn-admin">← Volver al Panel</a>
        </div>
    </header>

    <main class="panel-container">
        <h2>📜 Historial de Movimientos</h2>
        <p class="panel-subtitle">
            <?php echo $es_admin ? "Visor General de Ventas (Modo Administrador)" : "Consulta el registro de tus compras realizadas"; ?>
        </p>

        <!-- FORMULARIO DE FILTRO POR FECHA -->
        <form method="GET" action="historial_compras.php" class="search-filter-box">
            <div style="flex: 1; min-width: 160px;">
                <label style="font-size: 0.85rem; font-weight: 600; color: #555;">Desde:</label>
                <input type="date" name="fecha_desde" value="<?php echo htmlspecialchars($fecha_desde); ?>" style="width: 100%; padding: 10px; border-radius: 12px; border: 1px solid #dce8df;">
            </div>

            <div style="flex: 1; min-width: 160px;">
                <label style="font-size: 0.85rem; font-weight: 600; color: #555;">Hasta:</label>
                <input type="date" name="fecha_hasta" value="<?php echo htmlspecialchars($fecha_hasta); ?>" style="width: 100%; padding: 10px; border-radius: 12px; border: 1px solid #dce8df;">
            </div>

            <div style="display: flex; gap: 10px; align-items: flex-end; margin-top: 15px;">
                <button type="submit" class="btn-search">📅 Filtrar Movimientos</button>
                <?php if (!empty($fecha_desde) || !empty($fecha_hasta)): ?>
                    <a href="historial_compras.php" class="btn-reset-filter">Restablecer</a>
                <?php endif; ?>
            </div>
        </form>

        <!-- TABLA DE MOVIMIENTOS / PEDIDOS -->
        <div class="cart-table-wrapper">
            <table class="cart-table">
                <thead>
                    <tr>
                        <th>N° Pedido</th>
                        <?php if ($es_admin): ?>
                            <th>Cliente</th>
                        <?php endif; ?>
                        <th>Fecha y Hora</th>
                        <th>Método</th>
                        <th>Total</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($pedidos && $pedidos->num_rows > 0): ?>
                        <?php while ($row = $pedidos->fetch_assoc()): ?>
                            <tr>
                                <td><strong>#<?php echo str_pad($row['id'], 6, '0', STR_PAD_LEFT); ?></strong></td>
                                
                                <?php if ($es_admin): ?>
                                    <td>
                                        <strong><?php echo htmlspecialchars(($row['nombre'] ?? 'Usuario') . ' ' . ($row['apellido'] ?? '')); ?></strong><br>
                                        <small style="color: #777;"><?php echo htmlspecialchars($row['correo'] ?? ''); ?></small>
                                    </td>
                                <?php endif; ?>

                                <td><?php echo date('d/m/Y h:i A', strtotime($row['fecha'])); ?></td>
                                
                                <td>
                                    <span style="background: #e8f5e9; color: #2e7d32; padding: 4px 10px; border-radius: 10px; font-size: 0.85rem; font-weight: 600;">
                                        <?php echo htmlspecialchars($row['metodo_envio'] ?? 'N/A'); ?>
                                    </span>
                                </td>

                                <td style="font-weight: 800; color: #0b7c3b;">
                                    $<?php echo number_format($row['total'], 2); ?>
                                </td>

                                <td>
                                    <a href="factura.php?id=<?php echo $row['id']; ?>" class="btn-admin" style="font-size: 0.8rem; padding: 6px 12px;" target="_blank">
                                        📄 Ver Recibo
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="<?php echo $es_admin ? '6' : '5'; ?>" style="text-align: center; padding: 30px; color: #777;">
                                🚫 No se encontraron movimientos en el rango de fechas seleccionado.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

</body>
</html>