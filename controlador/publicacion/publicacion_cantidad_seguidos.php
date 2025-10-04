<?php 
    $idEstudiante = $_GET['idEstudiante'];  

    require_once("../../modelo/publicacion/publicacion.php");

    $rpta = cantidadSeguidos($idEstudiante);
    echo json_encode([
        "total_seguidos" => $rpta
    ]);
?>