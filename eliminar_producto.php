<?php
session_start();
require 'conexion.php';

// Verificación de seguridad: Solo administradores pueden borrar
if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['rol']) || strtolower($_SESSION['rol']) !== 'admin') {
    header("Location: panel.php");
    exit;
}

if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id = intval($_GET['id']);

    try {
        // 1. Obtener la imagen del producto para borrar el archivo físico (si no es la default)
        $sqlSelect = "SELECT imagen FROM productos WHERE id = ?";
        $stmtSelect = $conexion->prepare($sqlSelect);
        $stmtSelect->bind_param("i", $id);
        $stmtSelect->execute();
        $resultado = $stmtSelect->get_result();

        if ($producto = $resultado->fetch_assoc()) {
            $imagenPath = "imagenes/" . $producto['imagen'];
            if ($producto['imagen'] !== 'riologo.png' && $producto['imagen'] !== 'default.jpg' && file_exists($imagenPath)) {
                @unlink($imagenPath); // Borra el archivo de la carpeta
            }
        }

        // 2. Eliminar el registro en la base de datos
        $sqlDelete = "DELETE FROM productos WHERE id = ?";
        $stmtDelete = $conexion->prepare($sqlDelete);
        $stmtDelete->bind_param("i", $id);

        if ($stmtDelete->execute()) {
            header("Location: panel.php?mensaje=eliminado");
            exit;
        } else {
            throw new Exception("No se pudo eliminar el producto.");
        }
    } catch (mysqli_sql_exception $e) {
        // Captura de error si existen compras asociadas en la base de datos
        header("Location: panel.php?error=" . urlencode("No se puede eliminar el producto porque tiene compras registradas en el historial."));
        exit;
    } catch (Exception $e) {
        header("Location: panel.php?error=" . urlencode($e->getMessage()));
        exit;
    }
} else {
    header("Location: panel.php");
    exit;
}
?>