<?php 
    $idEstudiante = $_GET['idEstudiante'];  

    require_once("../../modelo/soporte/soporte.php");

    $rpta = listarSolicitudesSoporte($idEstudiante);
    echo json_encode($rpta);
?>