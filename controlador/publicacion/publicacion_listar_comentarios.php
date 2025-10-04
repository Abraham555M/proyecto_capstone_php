<?php 
    $idPublicacion = $_GET['idPublicacion'];  
    $idEstudiante = $_GET['idEstudiante'];  

    require_once("../../modelo/publicacion/publicacion.php");

    $rpta = listarComentariosPublicacion($idPublicacion, $idEstudiante);
    echo json_encode($rpta);
?>