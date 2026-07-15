<?php
session_start();

if (!isset($_SESSION['nombre'])) {
    header("Location: datosbasicos.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Credenciales</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>

    <div class="main-container">
        
        <div class="register-section">
            <a href="datosbasicos.php" class="back-arrow">&#8592;</a>
            
            <div class="form-container">
                <div class="logo-placeholder">
                    <img src="imagenes/riologo.png">
                </div>
                
                <h1>Registro</h1>

                <?php if (isset($_GET['error'])): ?>
                    <p style="color:red;"><?php echo htmlspecialchars($_GET['error']); ?></p>
                <?php endif; ?>
                
                <form action="registrar.php" method="POST">
                    <input type="email" name="correo" placeholder="Correo" required>
                    <input type="password" name="password" placeholder="Contraseña" required>
                    <input type="password" name="confirmPassword" placeholder="Confirmar Contraseña" required>
                    
                    <button type="submit" class="btn-submit">Crear Cuenta</button>
                </form>
            </div>
            
            <div></div>
        </div>

        <div class="image-section">
            <img src="imagenes/credenciales.jpg">
        </div>

    </div>

</body>
</html>
