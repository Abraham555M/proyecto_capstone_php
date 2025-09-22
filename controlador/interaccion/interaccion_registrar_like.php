<?php
    $idPublicacion = $_POST['idPublicacion'];  
    $idEstudiante = $_POST['idEstudiante'];  

    require_once("../../modelo/interaccion/interaccion.php");
    $rpta = registrarLike($idPublicacion, $idEstudiante);
    echo json_encode($rpta);
?>

