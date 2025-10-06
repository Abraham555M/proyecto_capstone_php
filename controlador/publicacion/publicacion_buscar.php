<?php
require_once("../../modelo/publicacion/publicacion.php");

// Verificar parámetros obligatorios
if (!isset($_GET['idEstudiante']) || !isset($_GET['textoBusqueda'])) {
    echo json_encode([
        "status" => "error",
        "message" => "Faltan parámetros requeridos"
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$idEstudiante = intval($_GET['idEstudiante']);
$textoBusqueda = trim($_GET['textoBusqueda']);

// Nuevo parámetro opcional de categoría
$idCategoria = isset($_GET['idCategoria']) ? intval($_GET['idCategoria']) : null;

// Si el texto está vacío y no hay categoría → cargar todo
if (empty($textoBusqueda) && empty($idCategoria)) {
    $rpta = listarPublicacionInicio($idEstudiante);

// Si hay categoría pero no texto → filtrar por categoría
} elseif (empty($textoBusqueda) && !empty($idCategoria)) {
    $rpta = filtrarPublicacionesPorCategoria($idEstudiante, $idCategoria);

// Si hay texto (con o sin categoría)
} else {
    $rpta = buscarPublicaciones($idEstudiante, $textoBusqueda, $idCategoria);
}

echo json_encode($rpta, JSON_UNESCAPED_UNICODE);
?>
