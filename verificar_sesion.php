<?php
session_start();
if (!isset($_SESSION['modo_editor'])) {
    header("Location: index_publico.php");
    exit;
}
?>