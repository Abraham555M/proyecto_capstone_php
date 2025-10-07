<?php
    $idEstudiante = $_POST['idEstudiante'];
    $idComentario = $_POST['idComentario'];
    $idTipoReporte = $_POST['idTipoReporte'];

    require_once("../../modelo/reporte/reporte.php");
    $rpta = registrarReporteComentario($idEstudiante, $idComentario, $idTipoReporte);
    echo json_encode($rpta);
?>
