<?php

require_once("../../modelo/favorito/favorito.php");

$idEstudiante = isset($_GET['idEstudiante']) ? intval($_GET['idEstudiante']) : 0;

if ($idEstudiante <= 0) {
    echo json_encode(["status" => "error", "message" => "ID de estudiante no válido"]);
    exit;
}

$rpta = listarPublicacionesFavoritos($idEstudiante);
echo json_encode($rpta, JSON_UNESCAPED_UNICODE);
?>
