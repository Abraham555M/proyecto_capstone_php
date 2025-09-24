<?php 
    $idEstudiante = $_GET['idEstudiante'];  
    $idEmprendimiento = $_GET['idEmprendimiento'];  

    require_once("../../modelo/publicacion/publicacion.php");

    $rpta = listarPublicacionPerfil($idEstudiante, $idEmprendimiento);
    echo json_encode($rpta);
?>