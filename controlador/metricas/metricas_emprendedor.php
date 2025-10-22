<?php

if (!isset($_GET["idEstudiante"])) {
    echo json_encode(["status" => "error", "message" => "Falta idEstudiante"]);
    exit;
}

$idEstudiante = $_GET['idEstudiante'];


$fechaDesde = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : null;
$fechaHasta = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : null;

require_once("../../modelo/metricas/metricas.php");
$rpta = obtenerMetricasEmprendedor($idEstudiante, $fechaDesde, $fechaHasta);
echo json_encode($rpta, JSON_UNESCAPED_UNICODE);
?>
