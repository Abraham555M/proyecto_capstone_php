<?php
require_once("../../modelo/publicacion/publicacion.php");

if (!isset($_GET['idEstudiante'])) {
    echo json_encode([
        "status" => "error",
        "message" => "Falta el parámetro idEstudiante"
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$idEstudiante = intval($_GET['idEstudiante']);
$idCategoria = isset($_GET['idCategoria']) ? intval($_GET['idCategoria']) : null;

// Si no hay categoría, mostrar todas
if ($idCategoria === null || $idCategoria === 0) {
    $rpta = listarPublicacionInicio($idEstudiante);
} else {
    $rpta = filtrarPublicacionesPorCategoria($idEstudiante, $idCategoria);
}

echo json_encode($rpta, JSON_UNESCAPED_UNICODE);
?>