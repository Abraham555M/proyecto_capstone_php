<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");

error_reporting(0);

$idEstudiante = isset($_GET['idEstudiante']) ? intval($_GET['idEstudiante']) : 0;

// Validación básica
if ($idEstudiante <= 0) {
    echo json_encode([]);
    exit;
}

require_once "../../modelo/publicacion/publicacion.php";

// Llamar al modelo
$resultado = listarPublicaciones($idEstudiante);

// Responder JSON
echo json_encode($resultado, JSON_UNESCAPED_UNICODE);
?>
