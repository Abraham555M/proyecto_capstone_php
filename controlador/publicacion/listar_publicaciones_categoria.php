<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");

error_reporting(0);

$idEstudiante = isset($_GET['id_estudiante']) ? intval($_GET['id_estudiante']) : 0;
$idCategoria = isset($_GET['id_categoria']) ? intval($_GET['id_categoria']) : 0;
$idEmprendimiento = isset($_GET['id_emprendimiento']) ? intval($_GET['id_emprendimiento']) : 0;

// Validación básica
if ($idEstudiante <= 0 || $idCategoria <= 0 || $idEmprendimiento <= 0) {
    echo json_encode([]);
    exit;
}

require_once "../../modelo/publicacion/publicacion.php";

// Llamar al modelo
$resultado = listarPublicacionesCategorias($idEstudiante, $idCategoria, $idEmprendimiento);

// Respuesta
echo json_encode($resultado, JSON_UNESCAPED_UNICODE);
?>
