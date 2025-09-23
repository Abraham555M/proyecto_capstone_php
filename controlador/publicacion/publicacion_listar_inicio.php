<?php 
    $idEstudiante = $_GET['idEstudiante'];  

    require_once("../../modelo/publicacion/publicacion.php");

    $rpta = listarPublicacionInicio($idEstudiante);
    echo json_encode($rpta);
?>