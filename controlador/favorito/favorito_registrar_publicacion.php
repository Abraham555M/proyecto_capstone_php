<?php
    $idPublicacion = $_POST['idPublicacion'];  
    $idEstudiante = $_POST['idEstudiante'];  

    require_once("../../modelo/favorito/favorito.php");
    $rpta = registrarFavorito($idPublicacion, $idEstudiante);
    echo json_encode($rpta);
?>

