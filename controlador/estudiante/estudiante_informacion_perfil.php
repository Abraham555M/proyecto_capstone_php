<?php 
    $idEstudiante = $_GET['idEstudiante'];  

    require_once("../../modelo/estudiante/estudiante.php");

    $rpta = obtenerInformacionPerfil($idEstudiante);
    echo json_encode($rpta);
?>