<?php
require_once("../../modelo/publicacion/publicacion.php");

if (!isset($_GET['idEstudiante']) || !isset($_GET['textoBusqueda'])) {
    echo json_encode([
        "status" => "error",
        "message" => "Faltan parámetros requeridos"
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$idEstudiante = intval($_GET['idEstudiante']);
$textoBusqueda = trim($_GET['textoBusqueda']);

// Si el texto está vacío, cargar todas las publicaciones
if (empty($textoBusqueda)) {
    $rpta = listarPublicacionInicio($idEstudiante);
} else {
    $rpta = buscarPublicaciones($idEstudiante, $textoBusqueda);
}

echo json_encode($rpta, JSON_UNESCAPED_UNICODE);
?>