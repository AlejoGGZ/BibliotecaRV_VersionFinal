<?php
require 'verificar_sesion.php';
require 'config.php';

$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nuevo_diario'])) {
    $nuevo = trim($_POST['nuevo_diario']);
    if (!empty($nuevo)) {
        $stmt = $conn->prepare("INSERT INTO diarios (nombre) VALUES (?)");
        $stmt->bind_param("s", $nuevo);
        if ($stmt->execute()) {
            $mensaje = "Diario agregado correctamente.";
        } else {
            $mensaje = "Error al agregar diario: " . $conn->error;
        }
        $stmt->close();
    }
}

if (isset($_GET['eliminar']) && is_numeric($_GET['eliminar'])) {
    $idEliminar = (int) $_GET['eliminar'];

    $check = $conn->prepare("SELECT COUNT(*) FROM bibliotecat WHERE diario_id = ?");
    $check->bind_param("i", $idEliminar);
    $check->execute();
    $check->bind_result($cuenta);
    $check->fetch();
    $check->close();

    if ($cuenta > 0) {
        $mensaje = "No se puede eliminar el diario porque está siendo usado, primero elimina los registros.";
    } else {
        $del = $conn->prepare("DELETE FROM diarios WHERE id = ?");
        $del->bind_param("i", $idEliminar);
        if ($del->execute()) {
            $mensaje = "Diario eliminado correctamente.";
        } else {
            $mensaje = "Error al eliminar diario.";
        }
        $del->close();
    }
}

$diarios = $conn->query("SELECT * FROM diarios ORDER BY nombre ASC");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestionar Diarios</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #e6f7ff;
        }
        .container {
            max-width: 700px;
        }
    </style>
</head>
<body class="container mt-5">
    <h2 class="mb-4">Agregar y Eliminar Diarios</h2>

    <?php if (!empty($mensaje)): ?>
        <div class="alert alert-info"><?php echo $mensaje; ?></div>
    <?php endif; ?>

    <form method="POST" class="mb-4">
        <div class="input-group">
            <input type="text" name="nuevo_diario" class="form-control" placeholder="Nombre del nuevo diario" required>
            <button type="submit" class="btn btn-success">Agregar Diario</button>
        </div>
    </form>

    <h4>Diarios existentes:</h4>
    <ul class="list-group">
        <?php while ($row = $diarios->fetch_assoc()): ?>
            <li class="list-group-item d-flex justify-content-between align-items-center">
                <?php echo htmlspecialchars($row['nombre']); ?>
                <a href="agregar_diario.php?eliminar=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Estás seguro de eliminar este diario?')">Eliminar</a>
            </li>
        <?php endwhile; ?>
    </ul>

    <div class="mt-4">
        <a href="index.php" class="btn btn-secondary">Volver al Inicio</a>
    </div>
</body>
</html>
