<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
error_reporting(0);

require_once "../../modelo/publicacion/publicacion.php";

// Llamar al modelo
$resultado = listarTiposPublicacion();

// Responder JSON
echo json_encode($resultado, JSON_UNESCAPED_UNICODE);
?>
