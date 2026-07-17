<?php
require 'conexion.php';

$sql = "SELECT * FROM productos ORDER BY fecha_creacion DESC";
$resultado = mysqli_query($conexion, $sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catálogo Río</title>
    <link rel="stylesheet" href="estilos.css">
    <style>
        .catalogo-container {
            padding: 40px;
            width: 100%;
            height: 100vh;
            overflow-y: auto;
        }
        .nav-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
        }
        .grid-productos {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: 25px;
            max-width: 1200px;
            margin: 40px auto;
        }
        .tarjeta-producto {
            border: 1px solid #dce8df;
            padding: 20px;
            border-radius: 25px;
            text-align: center;
            background-color: #f4f9f5;
            transition: transform 0.3s;
        }
        .tarjeta-producto:hover {
            transform: translateY(-5px);
        }
        .tarjeta-producto img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 15px;
            margin-bottom: 15px;
        }
        .titulo-producto {
            color: #333;
            font-weight: 800;
            font-size: 1.2rem;
            margin-bottom: 5px;
        }
        .precio {
            color: #0b7c3b;
            font-weight: 800;
            font-size: 1.4rem;
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <div class="catalogo-container">
        
        <div class="nav-top">
            <a href="bienvenidos..html" class="back-arrow">&#8592;</a>
            <img src="imagenes/riologo.png" alt="Logo" style="height: 50px;">
            <a href="crear_producto.php" class="btn-submit" style="text-decoration: none; font-size: 0.9rem;">+ Añadir</a>
        </div>
        
        <h1 style="text-align:center; margin-top: 30px;">Nuestro Catálogo</h1>
        
        <div class="grid-productos">
            <?php
            if (mysqli_num_rows($resultado) > 0) {
                while($fila = mysqli_fetch_assoc($resultado)) {
                    echo "<div class='tarjeta-producto'>";
                    echo "<img src='imagenes/" . htmlspecialchars($fila['imagen']) . "' alt='" . htmlspecialchars($fila['nombre']) . "'>";
                    echo "<div class='titulo-producto'>" . htmlspecialchars($fila['nombre']) . "</div>";
                    echo "<p style='font-size: 0.95rem; color: #666;'>" . htmlspecialchars($fila['descripcion']) . "</p>";
                    echo "<div class='precio'>$" . htmlspecialchars($fila['precio']) . "</div>";
                    echo "</div>";
                }
            } else {
                echo "<p style='text-align:center; grid-column: 1 / -1; color: #99aab0;'>Aún no hay productos en el catálogo.</p>";
            }
            ?>
        </div>
    </div>
</body>
</html>