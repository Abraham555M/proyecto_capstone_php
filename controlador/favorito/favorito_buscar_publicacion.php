<?php
require_once("../../modelo/favorito/favorito.php");

if (!isset($_GET['idEstudiante']) || !isset($_GET['texto'])) {
    echo json_encode([
        "status" => "error",
        "message" => "Faltan parámetros"
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$idEstudiante = intval($_GET['idEstudiante']);
$texto = trim($_GET['texto']);
$idCategoria = isset($_GET['idCategoria']) ? intval($_GET['idCategoria']) : null;

// ⭐ Si idCategoria es -1 (desde Java), convertirlo a null
if ($idCategoria === -1) {
    $idCategoria = null;
}

// Llamar función con o sin categoría
$rpta = buscarFavoritosConCategoria($idEstudiante, $texto, $idCategoria);

echo json_encode($rpta, JSON_UNESCAPED_UNICODE);
?>