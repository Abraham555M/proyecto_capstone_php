<?php 
    $idEstudiante = $_GET['idEstudiante'];  

    require_once("../../modelo/publicacion/publicacion.php");

    $rpta = cantidadSeguidores($idEstudiante);
    echo json_encode([
        "total_seguidores" => $rpta
    ]);
?>