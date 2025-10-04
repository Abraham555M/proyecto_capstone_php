<?php 
    $idEstudiante = $_GET['idEstudiante'];  

    require_once("../../modelo/publicacion/publicacion.php");

    $rpta = cantidadPublicacionesPerfil($idEstudiante);
    echo json_encode([
        "total_publicaciones" => $rpta
    ]);
?>