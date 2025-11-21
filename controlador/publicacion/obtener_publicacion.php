<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
error_reporting(0);

$idPublicacion = isset($_GET["id_publicacion"]) ? intval($_GET["id_publicacion"]) : 0;

// Validación básica
if ($idPublicacion <= 0) {
    echo json_encode([
        "status" => "error",
        "msg" => "Falta el parámetro id_publicacion"
    ]);
    exit;
}

require_once "../../modelo/publicacion/publicacion.php";

$resultado = obtenerDetallePublicacion($idPublicacion);

echo json_encode($resultado, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>

