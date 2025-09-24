<?php
    $idEstudiante = $_POST['idEstudiante'];  
    $idPublicacion = $_POST['idPublicacion'];  
    $idTipoReporte = $_POST['idTipoReporte'];  

    require_once("../../modelo/reporte/reporte.php");
    $rpta = registrarReporte($idEstudiante, $idPublicacion, $idTipoReporte);
    echo json_encode($rpta);
?>

