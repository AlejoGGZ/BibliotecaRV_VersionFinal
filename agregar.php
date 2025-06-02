<?php
require 'verificar_sesion.php';
include("config.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['palabra_clave']) && !empty($_POST['diario_id']) && !empty($_POST['fecha']) && isset($_FILES['link'])) {
        
        $palabra_clave = $conn->real_escape_string(trim($_POST['palabra_clave']));
        $diario_id = (int)$_POST['diario_id'];
        $fecha = $conn->real_escape_string(trim($_POST['fecha']));

        $archivo = $_FILES['link'];
        $nombreArchivo = basename($archivo['name']);
        $directorioDestino = 'uploads/pdfs/';
        $rutaArchivo = $directorioDestino . $nombreArchivo;
        $tipoArchivo = strtolower(pathinfo($nombreArchivo, PATHINFO_EXTENSION));

        $extensionesPermitidas = ['pdf', 'jpg', 'jpeg', 'png'];

        if (!in_array($tipoArchivo, $extensionesPermitidas)) {
            $mensaje = ['status' => 'error', 'message' => 'Solo se permiten archivos PDF, JPG, JPEG o PNG'];
        } else {
            if (!is_dir($directorioDestino)) {
                mkdir($directorioDestino, 0777, true);
            }

            if ($archivo['error'] === UPLOAD_ERR_OK) {
                if (move_uploaded_file($archivo['tmp_name'], $rutaArchivo)) {
                    $link = $rutaArchivo;
                    $stmt = $conn->prepare("INSERT INTO bibliotecat (palabra_clave, diario_id, fecha, link) VALUES (?, ?, ?, ?)");
                    $stmt->bind_param("siss", $palabra_clave, $diario_id, $fecha, $link);

                    if ($stmt->execute()) {
                        $mensaje = ['status' => 'success', 'message' => 'El registro se ha agregado exitosamente'];
                    } else {
                        $mensaje = ['status' => 'error', 'message' => 'Error al agregar el registro: ' . $conn->error];
                    }

                    $stmt->close();
                } else {
                    $mensaje = ['status' => 'error', 'message' => 'Error al mover el archivo'];
                }
            } else {
                $mensaje = ['status' => 'error', 'message' => 'Error en la carga del archivo: ' . $archivo['error']];
            }
        }
    } else {
        $mensaje = ['status' => 'warning', 'message' => 'Por favor, completa todos los campos'];
    }
}

$diarios = [];
$result = $conn->query("SELECT id, nombre FROM diarios ORDER BY nombre ASC");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $diarios[] = $row;
    }
}
$conn->close();
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Agregar Datos a la Biblioteca</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #baf0fd; }
        .container { max-width: 800px; }
        #mensaje { font-weight: bold; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h2 class="mb-4">Nuevo Dato</h2>

        <?php if (!empty($mensaje)): ?>
            <div id="mensaje" class="alert alert-<?php echo $mensaje['status'] === 'success' ? 'success' : 'danger'; ?>">
                <?php echo $mensaje['message']; ?>
            </div>
        <?php endif; ?>

        <form id="agregarForm" method="post" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="palabra_clave" class="form-label">Palabra Clave</label>
                <input type="text" class="form-control" name="palabra_clave" id="palabra_clave" required placeholder="Ej: Luz - Elecciones - Gaucho">
            </div>
            <div class="mb-3">
                <label for="diario_id" class="form-label">Diario</label>
                <select class="form-select" name="diario_id" id="diario_id" required>
                    <option value="">Seleccionar un diario</option>
                    <?php foreach ($diarios as $d): ?>
                        <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="fecha" class="form-label">Fecha</label>
                <input type="date" class="form-control" name="fecha" id="fecha" required>
            </div>
            <div class="mb-3">
    <label for="link" class="form-label">Archivo (PDF o Imagen)</label>
    <input type="file" class="form-control" name="link" id="link" accept=".pdf,.jpg,.jpeg,.png" required>
</div>
            <button type="submit" class="btn btn-primary">Agregar</button>
        </form>

        <br>
        <a href="index.php" class="btn btn-secondary">Volver a la Búsqueda</a>
    </div>
</body>
</html>
