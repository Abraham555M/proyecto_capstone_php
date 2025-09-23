<?php 
    require_once("../../modelo/reporte/reporte.php");

    $rpta = listarTiposReporte();
    echo json_encode($rpta);
?>