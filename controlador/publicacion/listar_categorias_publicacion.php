<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");

error_reporting(0);

$idEstudiante = isset($_GET['id_estudiante']) ? intval($_GET['id_estudiante']) : 0;

// Validación
if ($idEstudiante <= 0) {
    echo json_encode([]);
    exit;
}

require_once "../../modelo/publicacion/publicacion.php";

// Llamar al modelo
$resultado = listarCategoriasPublicacion($idEstudiante);

// Respuesta JSON
echo json_encode($resultado, JSON_UNESCAPED_UNICODE);
?>
