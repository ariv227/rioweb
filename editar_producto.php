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

// Obtener ID del producto desde la URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: panel.php");
    exit;
}

$producto_id = intval($_GET['id']);

// Consultar datos actuales del producto
$stmt = $conexion->prepare("SELECT * FROM productos WHERE id = ?");
$stmt->bind_param("i", $producto_id);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    header("Location: panel.php");
    exit;
}

$producto = $resultado->fetch_assoc();

// PROCESAR ACTUALIZACIÓN DEL PRODUCTO
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);
    $descripcion = trim($_POST['descripcion']);
    $precio = floatval($_POST['precio']);
    $stock = intval($_POST['stock']);
    $categoria = $_POST['categoria'];
    $region = trim($_POST['region']);
    $calidad = $_POST['calidad'];

    // Imagen por defecto mantiene la actual
    $imagen = $producto['imagen']; 

    // Si el usuario sube una nueva imagen
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $nombre_img = time() . '_' . basename($_FILES['imagen']['name']);
        $ruta_destino = 'imagenes/' . $nombre_img;

        if (move_uploaded_file($_FILES['imagen']['tmp_name'], $ruta_destino)) {
            $imagen = $nombre_img;
        }
    }

    if (!empty($nombre) && $precio >= 0 && $stock >= 0) {
        $sql = "UPDATE productos SET nombre = ?, descripcion = ?, precio = ?, stock = ?, imagen = ?, categoria = ?, region = ?, calidad = ? WHERE id = ?";
        $stmt_update = $conexion->prepare($sql);
        $stmt_update->bind_param("ssdissssi", $nombre, $descripcion, $precio, $stock, $imagen, $categoria, $region, $calidad, $producto_id);

        if ($stmt_update->execute()) {
            $mensaje = "✅ Producto actualizado exitosamente.";
            $tipo_alerta = "alert-success";

            // Actualizar datos locales para reflejar los cambios en el formulario inmediatamente
            $producto['nombre'] = $nombre;
            $producto['descripcion'] = $descripcion;
            $producto['precio'] = $precio;
            $producto['stock'] = $stock;
            $producto['imagen'] = $imagen;
            $producto['categoria'] = $categoria;
            $producto['region'] = $region;
            $producto['calidad'] = $calidad;
        } else {
            $mensaje = "❌ Error al actualizar el producto.";
            $tipo_alerta = "alert-danger";
        }
    } else {
        $mensaje = "⚠️ Por favor completa los campos obligatorios correctamente.";
        $tipo_alerta = "alert-danger";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Producto - Río</title>
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
        <h2>✏️ Editar Producto</h2>
        <p class="panel-subtitle">Modifica los detalles, categoría o stock del producto seleccionados.</p>

        <?php if (!empty($mensaje)): ?>
            <div class="alert-message <?php echo $tipo_alerta; ?>">
                <?php echo $mensaje; ?>
            </div>
        <?php endif; ?>

        <form action="editar_producto.php?id=<?php echo $producto_id; ?>" method="POST" enctype="multipart/form-data" style="background:white; padding:25px; border-radius:20px; box-shadow:0 4px 15px rgba(0,0,0,0.05);">
            
            <div>
                <label><strong>Nombre del Producto *</strong></label>
                <input type="text" name="nombre" value="<?php echo htmlspecialchars($producto['nombre']); ?>" required>
            </div>

            <div>
                <label><strong>Categoría *</strong></label>
                <select name="categoria" required>
                    <option value="Alimentos frescos" <?php echo ($producto['categoria'] === 'Alimentos frescos') ? 'selected' : ''; ?>>Alimentos frescos</option>
                    <option value="Alimentos procesados y secos" <?php echo ($producto['categoria'] === 'Alimentos procesados y secos') ? 'selected' : ''; ?>>Alimentos procesados y secos</option>
                    <option value="Bebidas" <?php echo ($producto['categoria'] === 'Bebidas') ? 'selected' : ''; ?>>Bebidas</option>
                    <option value="Cuidado personal y limpieza" <?php echo ($producto['categoria'] === 'Cuidado personal y limpieza') ? 'selected' : ''; ?>>Cuidado personal y limpieza</option>
                </select>
            </div>

            <div class="form-row">
                <div>
                    <label><strong>Región / Origen</strong></label>
                    <input type="text" name="region" value="<?php echo htmlspecialchars($producto['region'] ?? ''); ?>">
                </div>
                <div>
                    <label><strong>Calidad</strong></label>
                    <select name="calidad">
                        <option value="Premium" <?php echo ($producto['calidad'] === 'Premium') ? 'selected' : ''; ?>>Premium ⭐⭐⭐</option>
                        <option value="Primera" <?php echo ($producto['calidad'] === 'Primera') ? 'selected' : ''; ?>>Primera Clase ⭐⭐</option>
                        <option value="Estándar" <?php echo ($producto['calidad'] === 'Estándar') ? 'selected' : ''; ?>>Estándar ⭐</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div>
                    <label><strong>Precio ($) *</strong></label>
                    <input type="number" step="0.01" name="precio" value="<?php echo htmlspecialchars($producto['precio']); ?>" required>
                </div>
                <div>
                    <label><strong>Stock Disponibles *</strong></label>
                    <input type="number" name="stock" value="<?php echo htmlspecialchars($producto['stock']); ?>" min="0" required>
                </div>
            </div>

            <div>
                <label><strong>Descripción</strong></label>
                <textarea name="descripcion" rows="3"><?php echo htmlspecialchars($producto['descripcion']); ?></textarea>
            </div>

            <div>
                <label><strong>Imagen Actual:</strong></label><br>
                <img src="imagenes/<?php echo htmlspecialchars($producto['imagen']); ?>" alt="Imagen actual" style="max-height:80px; margin: 8px 0; border-radius:8px;" onerror="this.src='imagenes/riologo.png';"><br>
                <label><strong>Cambiar Imagen (Opcional):</strong></label>
                <input type="file" name="imagen" accept="image/*">
            </div>

            <button type="submit" class="btn-submit">Guardar Cambios</button>
        </form>
    </main>

</body>
</html>