<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>

    <div class="main-container">
        
        <div class="register-section">
            <a href="bienvenidos..html" class="back-arrow">&#8592;</a>
            
            <div class="form-container">
                <div class="logo-placeholder">
                    <img src="imagenes/riologo.png">
                </div>
                
                <h1>Registro</h1>
                
                <form action="guardar_datos.php" method="POST">
                    <div class="form-row">
                        <input type="text" name="nombre" placeholder="Nombre" required>
                        <input type="text" name="estado" placeholder="Estado" required>
                    </div>
                    
                    <div class="form-row">
                        <input type="text" name="apellido" placeholder="Apellido" required>
                        <input type="text" name="direccion" placeholder="Dirección de envío" required>
                    </div>
                    
                    <div class="form-row">
                        <input type="text" name="identificacion" placeholder="Identificación" required>
                        <div></div> 
                    </div>
                    
                    <div class="form-row">
                        <input type="tel" name="telefono" placeholder="Numero de telefono" required>
                        <div></div> 
                    </div>

                    <div class="btn-submit-container">
                        <button type="submit" class="btn-submit">Siguiente</button>
                    </div>
                </form>
            </div>
            
            <div></div>
        </div>

        <div class="image-section">
            <img src="imagenes/datosbasicos.jpg">
        </div>

    </div>

</body>
</html>
