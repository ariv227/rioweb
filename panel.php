<?php
session_start();
require 'conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: bienvenidos.html");
    exit;
}

$es_admin = (isset($_SESSION['rol']) && strtolower($_SESSION['rol']) === 'admin');

// Calcular total de ítems en el carrito
$total_items_carrito = isset($_SESSION['carrito']) ? array_sum($_SESSION['carrito']) : 0;

// LÓGICA DE SALUDO LIMPIO
$nombre_user = trim($_SESSION['nombre'] ?? '');
$apellido_user = trim($_SESSION['apellido'] ?? '');

if ($nombre_user === $apellido_user || empty($apellido_user)) {
    $nombre_completo = $nombre_user;
} else {
    $nombre_completo = $nombre_user . ' ' . $apellido_user;
}

// VARIABLES DE BÚSQUEDA Y FILTRADO
$buscar = isset($_GET['buscar']) ? trim($_GET['buscar']) : '';
$categoria_filtro = isset($_GET['categoria']) ? trim($_GET['categoria']) : '';
$region_filtro = isset($_GET['region']) ? trim($_GET['region']) : '';

// Construir consulta dinámica SQL
$condiciones = [];
$parametros = [];
$tipos = "";

if (!empty($buscar)) {
    $condiciones[] = "(p.nombre LIKE ? OR p.descripcion LIKE ?)";
    $busqueda_param = "%" . $buscar . "%";
    $parametros[] = $busqueda_param;
    $parametros[] = $busqueda_param;
    $tipos .= "ss";
}

if (!empty($categoria_filtro)) {
    $condiciones[] = "p.categoria = ?";
    $parametros[] = $categoria_filtro;
    $tipos .= "s";
}

if (!empty($region_filtro)) {
    $condiciones[] = "p.region LIKE ?";
    $parametros[] = "%" . $region_filtro . "%";
    $tipos .= "s";
}

$sql_where = "";
if (count($condiciones) > 0) {
    $sql_where = " WHERE " . implode(" AND ", $condiciones);
}

$sql = "SELECT p.*, 
               COALESCE(AVG(c.puntuacion), 0) AS promedio_rating, 
               COUNT(c.id) AS total_votos 
        FROM productos p 
        LEFT JOIN calificaciones c ON p.id = c.producto_id 
        $sql_where
        GROUP BY p.id 
        ORDER BY p.id DESC";

$stmt = $conexion->prepare($sql);

if (!empty($tipos)) {
    $stmt->bind_param($tipos, ...$parametros);
}

$stmt->execute();
$resultado = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catálogo de Productos - Río</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>

    <header class="navbar">
        <div class="nav-logo">
            <img src="imagenes/riologo.png" alt="Río Logo">
        </div>
        <div class="nav-user">
            <!-- BOTÓN DE MOVIMIENTOS / HISTORIAL -->
            <a href="historial_compras.php" class="btn-admin" style="background-color: #3498db;">
                📜 <?php echo $es_admin ? 'Ver Movimientos' : 'Mis Compras'; ?>
            </a>

            <?php if (!$es_admin): ?>
                <a href="carrito.php" class="btn-admin" style="background-color: #27ae60;">
                    🛒 Carrito (<?php echo $total_items_carrito; ?>)
                </a>
            <?php else: ?>
                <a href="crear_producto.php" class="btn-admin">+ Crear Producto</a>
            <?php endif; ?>

            <!-- SALUDO -->
            <span>Hola, <strong><?php echo htmlspecialchars($nombre_completo); ?></strong> (<?php echo htmlspecialchars($_SESSION['rol']); ?>)</span>
            <a href="logout.php" class="btn-logout">Cerrar Sesión</a>
        </div>
    </header>

    <main class="panel-container">
        <h2>Catálogo de Productos</h2>
        <p class="panel-subtitle">Encuentra y filtra productos según tus preferencias.</p>

        <!-- MENSAJES DE NOTIFICACIÓN -->
        <?php if (isset($_GET['mensaje']) && $_GET['mensaje'] === 'eliminado'): ?>
            <div class="alert-message alert-success" style="background-color: #d4edda; color: #155724; padding: 12px; border-radius: 8px; margin-bottom: 20px;">
                🗑️ Producto eliminado exitosamente.
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
            <div class="alert-message alert-danger" style="background-color: #f8d7da; color: #721c24; padding: 12px; border-radius: 8px; margin-bottom: 20px;">
                ⚠️ <?php echo htmlspecialchars($_GET['error']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['compra']) && $_GET['compra'] === 'exitosa'): ?>
            <div class="alert-message alert-success">
                🎉 ¡Compra realizada con éxito! El stock ha sido actualizado.
            </div>
        <?php endif; ?>

        <!-- BARRA DE BÚSQUEDA Y FILTROS -->
        <form method="GET" action="panel.php" class="search-filter-box">
            <input type="text" name="buscar" placeholder="🔍 Buscar por nombre..." value="<?php echo htmlspecialchars($buscar); ?>">
            
            <select name="categoria">
                <option value="">Todas las Categorías</option>
                <option value="Alimentos frescos" <?php echo ($categoria_filtro === 'Alimentos frescos') ? 'selected' : ''; ?>>Alimentos frescos</option>
                <option value="Alimentos procesados y secos" <?php echo ($categoria_filtro === 'Alimentos procesados y secos') ? 'selected' : ''; ?>>Alimentos procesados y secos</option>
                <option value="Bebidas" <?php echo ($categoria_filtro === 'Bebidas') ? 'selected' : ''; ?>>Bebidas</option>
                <option value="Cuidado personal y limpieza" <?php echo ($categoria_filtro === 'Cuidado personal y limpieza') ? 'selected' : ''; ?>>Cuidado personal y limpieza</option>
            </select>

            <input type="text" name="region" placeholder="🌍 Región / Origen" value="<?php echo htmlspecialchars($region_filtro); ?>" style="max-width: 160px;">

            <button type="submit" class="btn-search">Buscar</button>
            <?php if (!empty($buscar) || !empty($categoria_filtro) || !empty($region_filtro)): ?>
                <a href="panel.php" class="btn-reset-filter">Limpiar Filtros</a>
            <?php endif; ?>
        </form>

        <!-- GRILLA DE PRODUCTOS -->
        <div class="products-grid">
            <?php if ($resultado && $resultado->num_rows > 0): ?>
                <?php while ($producto = $resultado->fetch_assoc()): ?>
                    <div class="product-card">
                        <div class="product-img-box">
                            <img src="imagenes/<?php echo htmlspecialchars($producto['imagen']); ?>" 
                                 alt="<?php echo htmlspecialchars($producto['nombre']); ?>"
                                 onerror="this.src='imagenes/riologo.png';">
                        </div>
                        <div class="product-info">
                            <span class="badge-category"><?php echo htmlspecialchars($producto['categoria']); ?></span>
                            <h3><?php echo htmlspecialchars($producto['nombre']); ?></h3>
                            
                            <div class="product-tags">
                                <span class="tag-region">🌍 <?php echo htmlspecialchars($producto['region'] ?? 'Nacional'); ?></span>
                                <span class="tag-calidad">🏷️ <?php echo htmlspecialchars($producto['calidad'] ?? 'Estándar'); ?></span>
                            </div>

                            <p class="product-desc"><?php echo htmlspecialchars($producto['descripcion']); ?></p>
                            
                            <!-- Promedio de Calificaciones -->
                            <div class="product-rating-box">
                                ⭐ <strong><?php echo number_format($producto['promedio_rating'], 1); ?></strong> / 5.0
                                <span style="color:#777; font-size: 0.8rem;">(<?php echo $producto['total_votos']; ?> opiniones)</span>
                            </div>

                            <p style="font-size: 0.85rem; color: #555; margin-bottom: 10px;">
                                Stock: <strong><?php echo $producto['stock']; ?> unidades</strong>
                            </p>

                            <!-- SECCIÓN ACCIONES CLIENTE / ADMIN -->
                            <?php if (!$es_admin): ?>
                                <form action="calificar.php" method="POST" class="rating-form">
                                    <input type="hidden" name="producto_id" value="<?php echo $producto['id']; ?>">
                                    <div style="display:flex; gap:5px;">
                                        <select name="puntuacion" required>
                                            <option value="5">⭐⭐⭐⭐⭐ (5)</option>
                                            <option value="4">⭐⭐⭐⭐ (4)</option>
                                            <option value="3">⭐⭐⭐ (3)</option>
                                            <option value="2">⭐⭐ (2)</option>
                                            <option value="1">⭐ (1)</option>
                                        </select>
                                        <button type="submit" class="btn-calificar">Opinar</button>
                                    </div>
                                    <textarea name="comentario" placeholder="¿Qué te pareció este producto? (Opcional)" rows="2"></textarea>
                                </form>

                                <form action="agregar_carrito.php" method="POST" style="display:flex; gap:5px; margin-top:10px;">
                                    <input type="hidden" name="producto_id" value="<?php echo $producto['id']; ?>">
                                    <input type="number" name="cantidad" value="1" min="1" max="<?php echo $producto['stock']; ?>" style="width:60px; padding:5px;" required>
                                    <button type="submit" class="btn-add-cart" style="flex:1;">Añadir 🛒</button>
                                </form>
                            <?php else: ?>
                                <!-- OPCIONES DE ADMINISTRADOR: EDITAR Y ELIMINAR -->
                                <div style="display: flex; gap: 8px; margin-top: 10px;">
                                    <a href="editar_producto.php?id=<?php echo $producto['id']; ?>" class="btn-edit" style="flex: 1; text-align: center; background-color: #f39c12; color: white; padding: 8px; border-radius: 6px; text-decoration: none; font-weight: bold;">✏️ Editar</a>
                                    <a href="eliminar_producto.php?id=<?php echo $producto['id']; ?>" class="btn-delete" style="flex: 1; text-align: center; background-color: #e74c3c; color: white; padding: 8px; border-radius: 6px; text-decoration: none; font-weight: bold;" onclick="return confirm('¿Estás seguro de que deseas eliminar este producto?');">🗑️ Eliminar</a>
                                </div>
                            <?php endif; ?>

                            <!-- DESPLEGABLE CON LAS OPINIONES -->
                            <details class="reviews-details">
                                <summary>💬 Ver opiniones (<?php echo $producto['total_votos']; ?>)</summary>
                                <div class="reviews-list">
                                    <?php
                                    $stmtCom = $conexion->prepare("SELECT c.*, u.nombre, u.apellido FROM calificaciones c JOIN usuarios u ON c.usuario_id = u.id WHERE c.producto_id = ? ORDER BY c.fecha DESC");
                                    $stmtCom->bind_param("i", $producto['id']);
                                    $stmtCom->execute();
                                    $resCom = $stmtCom->get_result();

                                    if ($resCom && $resCom->num_rows > 0):
                                        while ($com = $resCom->fetch_assoc()):
                                    ?>
                                            <div class="review-item">
                                                <div class="review-header">
                                                    <strong><?php echo htmlspecialchars($com['nombre'] . ' ' . $com['apellido']); ?></strong>
                                                    <span class="review-stars"><?php echo str_repeat('⭐', $com['puntuacion']); ?></span>
                                                </div>
                                                <?php if (!empty($com['comentario'])): ?>
                                                    <p class="review-text">"<?php echo htmlspecialchars($com['comentario']); ?>"</p>
                                                <?php endif; ?>
                                                <span class="review-date"><?php echo date('d/m/Y', strtotime($com['fecha'])); ?></span>
                                            </div>
                                    <?php 
                                        endwhile;
                                    else:
                                    ?>
                                        <p style="font-size:0.8rem; color:#999; margin-top:5px;">Aún no hay opiniones escritas.</p>
                                    <?php endif; ?>
                                </div>
                            </details>

                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div style="grid-column: 1 / -1; text-align: center; padding: 40px; background: white; border-radius: 15px;">
                    <p style="font-size: 1.1rem; color: #666;">🔍 No se encontraron productos con los criterios seleccionados.</p>
                </div>
            <?php endif; ?>
        </div>
    </main>

</body>
</html>