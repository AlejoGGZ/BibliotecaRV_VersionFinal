<?php
include("config.php");

if (isset($_GET['id'])) {
    $id = $conn->real_escape_string($_GET['id']);
    
    $sql = "DELETE FROM bibliotecat WHERE palabra_clave = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Registro eliminado exitosamente.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al eliminar el registro.']);
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Registro(id) no especificado para eliminar.']);
}

$conn->close();
?>
