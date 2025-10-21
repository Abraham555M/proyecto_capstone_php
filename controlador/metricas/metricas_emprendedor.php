<?php

if (!isset($_GET["idEstudiante"])) {
    echo json_encode(["status" => "error", "message" => "Falta idEstudiante"]);
    exit;
}

$idEstudiante = $_GET['idEstudiante'];

require_once("../../modelo/metricas/metricas.php");
$rpta = obtenerMetricasEmprendedor($idEstudiante);
echo json_encode($rpta, JSON_UNESCAPED_UNICODE);
?>
