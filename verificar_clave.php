<?php
session_start();

require 'config.php';

if (isset($_POST['clave'])) {
    $clave = trim($_POST['clave']);

    $stmt = $conn->prepare("SELECT * FROM contraseña WHERE clave = ?");
    $stmt->bind_param("s", $clave);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        $_SESSION['modo_editor'] = true; 
        echo json_encode(['acceso' => true]);
    } else {
        echo json_encode(['acceso' => false]);
    }

    $stmt->close();
}
$conn->close();
