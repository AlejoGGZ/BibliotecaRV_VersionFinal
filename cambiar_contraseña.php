<?php
require 'verificar_sesion.php';
require 'config.php';

$mensaje = '';
$error = '';

$sql = "SELECT clave FROM contraseña WHERE id = 1";
$result = $conn->query($sql);
$claveActualBD = ($result && $row = $result->fetch_assoc()) ? $row['clave'] : null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $actual = $_POST['actual'] ?? '';
    $nueva = $_POST['nueva'] ?? '';
    $confirmar = $_POST['confirmar'] ?? '';

    if (!$claveActualBD) {
        $error = "No se encontró la contraseña actual.";
    } elseif ($actual !== $claveActualBD) {
        $error = "La contraseña actual es incorrecta.";
    } elseif (empty($nueva) || strlen($nueva) < 5) {
        $error = "La nueva contraseña debe tener al menos 5 caracteres.";
    } elseif ($nueva !== $confirmar) {
        $error = "Las nuevas contraseñas no coinciden.";
    } else {
        $stmt = $conn->prepare("UPDATE contraseña SET clave = ? WHERE id = 1");
        $stmt->bind_param("s", $nueva);
        if ($stmt->execute()) {
            $mensaje = "Contraseña actualizada correctamente.";
        } else {
            $error = "Error al actualizar la contraseña.";
        }
        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cambiar Contraseña</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        body { background-color: #e8f4fc; }
        .container { max-width: 500px; margin-top: 50px; }
    </style>
</head>
<body>
    <div class="container">
        <h3 class="mb-4 text-center">Cambiar Contraseña</h3>

        <?php if ($mensaje): ?>
            <div class="alert alert-success"><?php echo $mensaje; ?></div>
        <?php elseif ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label for="actual" class="form-label">Contraseña actual:</label>
                <input type="password" class="form-control" id="actual" name="actual" required>
            </div>
            <div class="mb-3">
                <label for="nueva" class="form-label">Nueva contraseña:</label>
                <input type="password" class="form-control" id="nueva" name="nueva" required>
            </div>
            <div class="mb-3">
                <label for="confirmar" class="form-label">Confirmar nueva contraseña:</label>
                <input type="password" class="form-control" id="confirmar" name="confirmar" required>
            </div>
            <div class="d-grid">
                <button type="submit" class="btn btn-primary">Guardar cambios</button>
            </div>
            <div class="mt-3 text-center">
                <a href="index.php" class="btn btn-secondary btn-sm">Volver</a>
            </div>
        </form>
    </div>
</body>
</html>
