<?php
session_start();
// Si no es admin, lo mandamos al home inmediatamente
if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] !== 'admin') {
    header("Location: home.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Añadir Producto</title>
    <!-- Usa tus mismos estilos -->
    <link rel="stylesheet" href="estilos.css">
</head>
<body>
    <div class="main-container">
        
        <div class="register-section">
            <a href="ver_productos.php" class="back-arrow">&#8592;</a>
            
            <div class="form-container">
                <div class="logo-placeholder">
                    <img src="imagenes/riologo.png" alt="Río Logo">
                </div>
                
                <h1>Nuevo Producto</h1>
                
                <form action="guardar_producto.php" method="POST" enctype="multipart/form-data">
                    <input type="text" name="nombre" placeholder="Nombre del Producto" required>
                    <input type="text" name="descripcion" placeholder="Descripción breve" required>
                    <input type="text" name="precio" placeholder="Precio (Ej: 15.50)" required>
                    
                    <!-- Campo de imagen con estilos integrados para que coincida con tus inputs -->
                    <input type="file" name="imagen" accept="image/*" required 
                           style="padding: 15px 20px; background-color: #f4f9f5; border: 1px solid #dce8df; border-radius: 25px; width: 100%; color: #99aab0;">

                    <div class="btn-submit-container">
                        <button type="submit" class="btn-submit">Guardar Producto</button>
                    </div>
                </form>
            </div>
            <!-- Pon esto dentro de tu <form> -->
<div style="margin-bottom: 15px; text-align: left;">
    <label style="font-weight: 600; display: block; margin-bottom: 5px;">Categoría del Producto:</label>
    <select name="categoria" required style="width: 100%; padding: 10px; border: 1px solid #eaeaea; border-radius: 10px; font-family: 'Inter', sans-serif; background: white;">
        <option value="Fruta">Frutas</option>
        <option value="Vegetal">Vegetales</option>
        <option value="Lácteo">Lácteos</option>
        <option value="Pan">Panadería</option>
        <option value="Bebida">Bebidas</option>
        <option value="Oferta">Ofertas</option>
    </select>
</div>
            <div></div>
        </div>

        <div class="image-section">
            <!-- Usamos una de tus imágenes existentes de fondo -->
            <img src="imagenes/bienvenidos.jpg" alt="Sección de productos">
        </div>

    </div>
</body>
</html>