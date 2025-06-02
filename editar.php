<?php  
require 'verificar_sesion.php';
require 'config.php';

$errorMessage = '';
$successMessage = '';
$registro = null;

if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id = urldecode($_GET['id']);
    $id = str_replace('+', ' ', $id);

    $sql = "SELECT * FROM bibliotecat WHERE palabra_clave = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $id);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        $registro = $resultado->fetch_assoc();
    } else {
        $errorMessage = "Registro no encontrado.";
    }
} else {
    $errorMessage = "ID no proporcionado.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $registro) {
    $palabra_clave_nueva = trim($_POST['palabra_clave']);
    $diario_id = intval($_POST['diario_id']);
    $fecha = date("Y-m-d", strtotime($_POST['fecha']));
    $link = $registro['link'];

    if (empty($palabra_clave_nueva) || empty($diario_id) || empty($fecha)) {
        $errorMessage = "Todos los campos son obligatorios.";
    } else {
        if (!empty($_FILES['link']['name']) && $_FILES['link']['error'] === UPLOAD_ERR_OK) {
            $archivo = $_FILES['link'];
            $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
            $extPermitidas = ['pdf', 'jpg', 'jpeg', 'png'];

            if (!in_array($extension, $extPermitidas)) {
                $errorMessage = "Solo se permiten archivos PDF, JPG, JPEG o PNG.";
            } else {
                $nombreArchivo = uniqid() . "_" . basename($archivo['name']);
                $directorioDestino = 'uploads/pdfs/';
                $rutaArchivo = $directorioDestino . $nombreArchivo;

                if (!is_dir($directorioDestino)) {
                    mkdir($directorioDestino, 0777, true);
                }

                if (move_uploaded_file($archivo['tmp_name'], $rutaArchivo)) {
                    $link = $rutaArchivo;
                } else {
                    $errorMessage = "Error al subir el archivo.";
                }
            }
        }

        if (empty($errorMessage)) {
            $sql = "UPDATE bibliotecat SET palabra_clave = ?, diario_id = ?, fecha = ?, link = ? WHERE palabra_clave = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sisss", $palabra_clave_nueva, $diario_id, $fecha, $link, $id);

            if ($stmt->execute()) {
                header("Location: index.php?success=1");
                exit;
            } else {
                $errorMessage = "Error al actualizar: " . $conn->error;
            }

            $stmt->close();
        }
    }
}
?>


<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Editar Registro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        body { background-color: #f0f8ff; }
        .container { max-width: 800px; margin-top: 50px; }
        .form-label { font-weight: bold; }
    </style>
</head>
<body>
<div class="container">
    <h2 class="text-center mb-4">Editar Registro</h2>

    <?php if (!empty($errorMessage)): ?>
        <div class="alert alert-danger"><?php echo $errorMessage; ?></div>
    <?php endif; ?>

    <?php if ($registro): ?>
        <form method="POST" enctype="multipart/form-data" class="d-flex flex-column align-items-center">
            <input type="hidden" name="id" value="<?php echo $registro['ID']; ?>">

            <div class="mb-3 w-75">
                <label for="palabra_clave" class="form-label">Palabra Clave:</label>
                <input type="text" name="palabra_clave" class="form-control" required value="<?php echo htmlspecialchars($registro['palabra_clave']); ?>">
            </div>

            <div class="mb-3 w-75">
                <label for="diario_id" class="form-label">Diario:</label>
                <select name="diario_id" class="form-control" required>
                    <option value="">Seleccione un diario</option>
                    <?php
                    $diarios = $conn->query("SELECT id, nombre FROM diarios");
                    while ($d = $diarios->fetch_assoc()) {
                        $selected = ($d['id'] == $registro['diario_id']) ? 'selected' : '';
                        echo "<option value='{$d['id']}' $selected>" . htmlspecialchars($d['nombre']) . "</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="mb-3 w-75">
                <label for="fecha" class="form-label">Fecha:</label>
                <input type="date" name="fecha" class="form-control" required value="<?php echo $registro['fecha']; ?>">
            </div>

            <div class="mb-3 w-75">
                <label for="link" class="form-label">Archivo (PDF, JPG, PNG):</label>
                <input type="file" name="link" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                <small class="form-text text-muted">Deja vacío si no deseas cambiar el archivo.</small>
                <?php if (!empty($registro['link'])): ?>
                    <p>Archivo actual: <a href="<?php echo htmlspecialchars($registro['link']); ?>" target="_blank">Ver archivo</a></p>
                <?php endif; ?>
            </div>

            <div class="d-flex justify-content-between w-75">
                <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                <a href="index.php" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    <?php endif; ?>
</div>
</body>
</html>

<?php $conn->close(); ?>
