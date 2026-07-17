<?php
session_start();
require 'conexion.php'; // Tu archivo de conexión[cite: 7]

// Si el usuario no ha iniciado sesión, al login de una[cite: 6]
if (!isset($_SESSION['usuario_nombre'])) {
    header("Location: bienvenidos..html");
    exit;
}

// 1. CAPTURAR FILTROS (Buscador y Categorías)
$buscar = isset($_GET['buscar']) ? mysqli_real_escape_string($conexion, $_GET['buscar']) : '';
$categoria = isset($_GET['categoria']) ? mysqli_real_escape_string($conexion, $_GET['categoria']) : '';

// 2. CONSTRUIR LA CONSULTA DINÁMICA
// Si tu tabla productos no tiene columna "categoria", buscamos por el nombre/descripción para que no falle.
// CONSTRUIR LA CONSULTA DINÁMICA PERFECTA
$sql = "SELECT * FROM productos WHERE 1=1";

if (!empty($buscar)) {
    $sql .= " AND (nombre LIKE '%$buscar%' OR descripcion LIKE '%$buscar%')";
}

// Ahora filtra directamente por la columna categoria de tu base de datos
if (!empty($categoria)) {
    $sql .= " AND categoria = '$categoria'";
}

$sql .= " ORDER BY id DESC";
$resultado_productos = mysqli_query($conexion, $sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Río - Home Funcional</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <style>
        /* Estilos Base */
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }
        body { background-color: #ffffff; color: #333; }
        a { text-decoration: none; color: inherit; }

        /* Navegación (Header) */
        header { border-bottom: 1px solid #eaeaea; padding: 15px 40px; display: flex; align-items: center; justify-content: space-between; position: sticky; top: 0; background: white; z-index: 100; }
        .logo { font-size: 2rem; font-weight: 800; color: #0b7c3b; }
        .nav-links { display: flex; gap: 20px; font-weight: 600; color: #666; font-size: 0.95rem; }
        .nav-links a { padding-bottom: 5px; transition: color 0.2s; }
        .nav-links a.active, .nav-links a:hover { color: #0b7c3b; border-bottom: 2px solid #0b7c3b; }
        
        /* Buscador y Perfil */
        .header-right { display: flex; align-items: center; gap: 20px; }
        .search-form { display: flex; align-items: center; background: #f4f4f4; border-radius: 20px; padding: 5px 15px; width: 280px; }
        .search-form input { border: none; background: transparent; outline: none; width: 100%; font-size: 0.9rem; padding: 5px; }
        .search-form button { background: #0b7c3b; color: white; border: none; padding: 6px 12px; border-radius: 15px; cursor: pointer; font-size: 0.85rem; font-weight: 600; }
        
        .perfil-usuario { display: flex; align-items: center; gap: 8px; font-weight: 600; font-size: 0.9rem; background: #f4f4f4; padding: 8px 15px; border-radius: 20px; }
        .carrito { background: #e6f4ea; color: #0b7c3b; padding: 8px 15px; border-radius: 20px; font-weight: 800; cursor: pointer; }

        /* Sección Hero (Banner Verde) */
        .hero { background-color: #0b7c3b; color: white; padding: 60px 40px; margin: 20px 40px; border-radius: 20px; position: relative; }
        .hero-tag { background: #f3dfa2; color: #8a6d2b; padding: 5px 12px; border-radius: 15px; font-size: 0.8rem; font-weight: 800; display: inline-block; margin-bottom: 20px; }
        .hero h1 { font-size: 3.5rem; font-weight: 800; margin-bottom: 20px; line-height: 1.1; max-width: 500px; }
        .hero p { max-width: 450px; line-height: 1.5; margin-bottom: 30px; opacity: 0.9; }
        .hero-btns { display: flex; gap: 15px; }
        .btn-primary { background: #085a2a; color: white; padding: 12px 25px; border-radius: 25px; font-weight: 600; border: none; cursor: pointer; }
        .btn-outline { background: transparent; color: white; padding: 12px 25px; border-radius: 25px; font-weight: 600; border: 2px solid white; cursor: pointer; }

        /* Contenedor Principal */
        .container { padding: 40px; max-width: 1400px; margin: 0 auto; }
        .section-header { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 30px; }
        .section-header h2 { font-size: 1.8rem; font-weight: 800; }
        .section-header p { color: #666; margin-top: 5px; }
        .ver-todo { color: #0b7c3b; font-weight: 600; font-size: 0.95rem; }

        /* Grid Categorías */
        .grid-categorias { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 20px; margin-bottom: 60px; }
        .card-categoria { background: #f4f9f5; border: 1px solid #dce8df; border-radius: 15px; padding: 25px; text-align: center; transition: all 0.2s; cursor: pointer; }
        .card-categoria:hover { background: #e6f4ea; border-color: #0b7c3b; }
        .icono-cat { font-size: 2rem; margin-bottom: 10px; display: block; }
        .card-categoria h3 { font-size: 1.1rem; color: #333; }
        
        /* Grid Productos */
        .grid-productos { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 20px; }
        .card-producto { border: 1px solid #eaeaea; border-radius: 15px; padding: 15px; position: relative; transition: transform 0.2s; background: #fff; }
        .card-producto:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.05); }
        .card-producto img { width: 100%; height: 200px; object-fit: cover; border-radius: 10px; margin-bottom: 15px; }
        .badge-descuento { position: absolute; top: 25px; left: 25px; background: #d32f2f; color: white; padding: 4px 10px; border-radius: 10px; font-size: 0.8rem; font-weight: 800; }
        
        .producto-info { display: flex; justify-content: space-between; align-items: flex-end; margin-top: 10px; }
        .producto-titulo { font-weight: 600; font-size: 1.1rem; color: #333; }
        .producto-desc { font-size: 0.85rem; color: #888; margin: 5px 0; min-height: 32px; }
        .producto-precio { font-size: 1.3rem; font-weight: 800; color: #0b7c3b; }
        
        .btn-add { background: #0b7c3b; color: white; border: none; width: 40px; height: 40px; border-radius: 50%; font-size: 1.4rem; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: background 0.2s; }
        .btn-add:hover { background: #085a2a; }

        .btn-limpiar { background: #ffdde1; color: #c0392b; padding: 8px 15px; border-radius: 15px; font-size: 0.85rem; font-weight: 600; display: inline-block; margin-bottom: 20px; }
    </style>
</head>
<body>

<!-- Navegación -->
    <header>
        <div class="logo"><a href="home.php">Río</a></div>
<nav class="nav-links">
    <a href="home.php?categoria=Fruta" class="<?php echo ($categoria == 'Fruta') ? 'active' : ''; ?>">Frutas</a>
    <a href="home.php?categoria=Vegetal" class="<?php echo ($categoria == 'Vegetal') ? 'active' : ''; ?>">Vegetales</a>
    <a href="home.php?categoria=Lácteo" class="<?php echo ($categoria == 'Lácteo') ? 'active' : ''; ?>">Lácteos</a>
    <a href="home.php?categoria=Pan" class="<?php echo ($categoria == 'Pan') ? 'active' : ''; ?>">Panadería</a>
    <a href="home.php?categoria=Bebida" class="<?php echo ($categoria == 'Bebida') ? 'active' : ''; ?>">Bebidas</a>
    <a href="home.php?categoria=Oferta" class="<?php echo ($categoria == 'Oferta') ? 'active' : ''; ?>">Ofertas</a>
</nav>
        <div class="header-right">
            <!-- Buscador -->
            <form action="home.php" method="GET" class="search-form">
                <input type="text" name="buscar" placeholder="Buscar productos frescos..." value="<?php echo htmlspecialchars($buscar); ?>">
                <button type="submit">Buscar</button>
            </form>
            
            <!-- Botón de Admin protegido -->
            <?php if (isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] === 'admin'): ?>
                <a href="crear_producto.php" style="background: #0b7c3b; color: white; padding: 8px 15px; border-radius: 20px; font-size: 0.85rem; font-weight: 600;">+ Añadir</a>
            <?php endif; ?>
            
            <!-- Perfil del usuario -->
<div class="perfil-usuario">
    <span>👤</span>
    <?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?>
    <a href="logout.php" style="color: #c0392b; margin-left: 10px; font-size: 0.8rem; font-weight: bold;">Salir</a>
</div>
            
            <!-- Carrito -->
            <div class="carrito" onclick="vaciarCarrito()">
                🛒 <strong id="contador-carrito">0</strong>
            </div>
        </div>
    </header> <!-- ¡Esta etiqueta es la que salva el diseño! -->

    <!-- Banner Hero (Asegúrate de que esta sección quede justo debajo del </header>) -->
    <section class="hero">
        <div class="hero-tag">OBTENIDO FRESCO A DIARIO</div>
        <h1>Lleva la cosecha a tu hogar</h1>
        <p>Disfrute de los mejores productos orgánicos, seleccionados a mano por agricultores locales y entregados directamente en su puerta en un plazo de 24 horas.</p>
        <div class="hero-btns">
            <button class="btn-primary" onclick="document.getElementById('ancla-productos').scrollIntoView({behavior: 'smooth'});">Compra ahora</button>
            <button class="btn-outline">Nuestra historia</button>
        </div>
    </section>

    <div class="container">
        <!-- Sección Explorar Categorías -->
        <div class="section-header">
            <div>
                <h2>Explorar categorías</h2>
                <p>Encuentra exactamente lo que necesitas para tu estilo de vida saludable.</p>
            </div>
            <a href="home.php" class="ver-todo">Ver todo &rarr;</a>
        </div>
        
<div class="grid-categorias">
    <a href="home.php?categoria=Fruta" class="card-categoria">
        <span class="icono-cat">🍎</span>
        <h3>Frutas</h3>
    </a>
    <a href="home.php?categoria=Vegetal" class="card-categoria">
        <span class="icono-cat">🥕</span>
        <h3>Vegetales</h3>
    </a>
    <a href="home.php?categoria=Bebida" class="card-categoria">
        <span class="icono-cat">🧃</span>
        <h3>Bebidas</h3>
    </a>
    <a href="home.php?categoria=Pan" class="card-categoria">
        <span class="icono-cat">🍞</span>
        <h3>Panadería</h3>
    </a>
</div>

        <!-- Sección de Productos / Ofertas -->
        <div class="section-header" id="ancla-productos">
            <div>
                <h2>
                    <?php 
                        if(!empty($buscar)) echo "Resultados para: '" . htmlspecialchars($buscar) . "'";
                        elseif(!empty($categoria)) echo "Categoría: " . htmlspecialchars($categoria);
                        else echo "Ofertas de la semana";
                    ?>
                </h2>
                <p>Productos frescos y listos para tu mesa.</p>
            </div>
            <?php if(!empty($buscar) || !empty($categoria)): ?>
                <a href="home.php" class="btn-limpiar">❌ Limpiar Filtros</a>
            <?php endif; ?>
        </div>

        <div class="grid-productos">
            <?php
            if (mysqli_num_rows($resultado_productos) > 0) {
                while($producto = mysqli_fetch_assoc($resultado_productos)) {
            ?>
                <div class="card-producto">
                    <div class="badge-descuento">Oferta</div> 
                    <img src="imagenes/<?php echo htmlspecialchars($producto['imagen']); ?>" alt="<?php echo htmlspecialchars($producto['nombre']); ?>">
                    <div class="producto-titulo"><?php echo htmlspecialchars($producto['nombre']); ?></div>
                    <div class="producto-desc"><?php echo htmlspecialchars($producto['descripcion']); ?></div>
                    
                    <div class="producto-info">
                        <div class="producto-precio">$<?php echo htmlspecialchars($producto['precio']); ?></div>
                        <!-- Botón funcional mediante JavaScript -->
                        <button class="btn-add" onclick="agregarAlCarrito('<?php echo $producto['id']; ?>')">+</button>
                    </div>
                </div>
            <?php
                }
            } else {
                echo "<p style='grid-column: 1/-1; text-align:center; color:#999; padding: 40px;'>No se encontraron productos que coincidan con la selección.</p>";
            }
            ?>
        </div>
    </div>

    <!-- JAVASCRIPT PARA LA FUNCIONALIDAD DEL CARRITO -->
    <script>
        // Al cargar la página, recuperamos el estado del carrito
        document.addEventListener("DOMContentLoaded", () => {
            actualizarInterfazCarrito();
        });

        function obtenerCarrito() {
            const carrito = localStorage.getItem("carrito_rio");
            return carrito ? JSON.parse(carrito) : [];
        }

        function agregarAlCarrito(idProducto) {
            let carrito = obtenerCarrito();
            carrito.push(idProducto);
            localStorage.setItem("carrito_rio", JSON.stringify(carrito));
            actualizarInterfazCarrito();
            
            // Animación/Alerta sutil opcional
            alert("¡Producto añadido al carrito!");
        }

        function actualizarInterfazCarrito() {
            const carrito = obtenerCarrito();
            const contador = document.getElementById("contador-carrito");
            if(contador) {
                contador.innerText = carrito.length;
            }
        }

        function vaciarCarrito() {
            if(confirm("¿Quieres vaciar tu carrito de compras?")) {
                localStorage.removeItem("carrito_rio");
                actualizarInterfazCarrito();
            }
        }
    </script>
</body>
</html>