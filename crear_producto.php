<?php
session_start();
require 'conexion.php';

// Verificar que sea Administrador
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'admin') {
    header("Location: panel.php");
    exit;
}

$mensaje = '';
$tipo_alerta = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);
    $descripcion = trim($_POST['descripcion']);
    $precio = floatval($_POST['precio']);
    $stock = intval($_POST['stock']);
    $categoria = $_POST['categoria'];
    $region = trim($_POST['region']);
    $calidad = $_POST['calidad'];

    // Manejo de Imagen
    $imagen = 'riologo.png'; // Imagen por defecto
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $nombre_img = time() . '_' . basename($_FILES['imagen']['name']);
        $ruta_destino = 'imagenes/' . $nombre_img;

        if (move_uploaded_file($_FILES['imagen']['tmp_name'], $ruta_destino)) {
            $imagen = $nombre_img;
        }
    }

    if (!empty($nombre) && $precio > 0 && $stock >= 0) {
        $sql = "INSERT INTO productos (nombre, descripcion, precio, stock, imagen, categoria, region, calidad) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("ssdissss", $nombre, $descripcion, $precio, $stock, $imagen, $categoria, $region, $calidad);

        if ($stmt->execute()) {
            $mensaje = "✅ Producto creado exitosamente.";
            $tipo_alerta = "alert-success";
        } else {
            $mensaje = "❌ Error al crear el producto.";
            $tipo_alerta = "alert-danger";
        }
    } else {
        $mensaje = "⚠️ Completa los campos obligatorios correctamente.";
        $tipo_alerta = "alert-danger";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Producto - Río</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>

    <header class="navbar">
        <div class="nav-logo">
            <img src="imagenes/riologo.png" alt="Río Logo">
        </div>
        <div class="nav-user">
            <a href="panel.php" class="btn-admin">Volver al Panel</a>
            <a href="logout.php" class="btn-logout">Cerrar Sesión</a>
        </div>
    </header>

    <main class="panel-container" style="max-width: 600px;">
        <h2>+ Agregar Nuevo Producto</h2>
        <p class="panel-subtitle">Ingresa los datos del producto con su clasificación correspondiente.</p>

        <?php if (!empty($mensaje)): ?>
            <div class="alert-message <?php echo $tipo_alerta; ?>">
                <?php echo $mensaje; ?>
            </div>
        <?php endif; ?>

        <form action="crear_producto.php" method="POST" enctype="multipart/form-data" style="background:white; padding:25px; border-radius:20px; box-shadow:0 4px 15px rgba(0,0,0,0.05);">
            <div>
                <label><strong>Nombre del Producto *</strong></label>
                <input type="text" name="nombre" placeholder="Ej: Manzanas Rojas" required>
            </div>

            <div>
                <label><strong>Categoría *</strong></label>
                <select name="categoria" required>
                    <option value="Alimentos frescos">Alimentos frescos (Frutas, verduras, carnes, lácteos, etc.)</option>
                    <option value="Alimentos procesados y secos">Alimentos procesados y secos (Enlatados, pastas, snacks, etc.)</option>
                    <option value="Bebidas">Bebidas (Aguas, jugos, refrescos, alcohólicas)</option>
                    <option value="Cuidado personal y limpieza">Cuidado personal y limpieza (Detergentes, champú, hogar)</option>
                </select>
            </div>

            <div class="form-row">
                <div>
                    <label><strong>Región / Origen</strong></label>
                    <input type="text" name="region" placeholder="Ej: Los Andes, Costa, Importado" value="Nacional">
                </div>
                <div>
                    <label><strong>Calidad</strong></label>
                    <select name="calidad">
                        <option value="Premium">Premium ⭐⭐⭐</option>
                        <option value="Primera">Primera Clase ⭐⭐</option>
                        <option value="Estándar" selected>Estándar ⭐</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div>
                    <label><strong>Precio ($) *</strong></label>
                    <input type="number" step="0.01" name="precio" placeholder="0.00" required>
                </div>
                <div>
                    <label><strong>Stock Inicial *</strong></label>
                    <input type="number" name="stock" placeholder="10" min="0" required>
                </div>
            </div>

            <div>
                <label><strong>Descripción</strong></label>
                <textarea name="descripcion" rows="3" placeholder="Detalles del producto..."></textarea>
            </div>

            <div>
                <label><strong>Imagen del Producto</strong></label>
                <input type="file" name="imagen" accept="image/*">
            </div>

            <button type="submit" class="btn-submit">Guardar Producto</button>
        </form>
    </main>

</body>
</html>