<?php
    $idEstudiante = $_POST['idEstudiante'];  
    $idPublicacion = $_POST['idPublicacion'];  
    $idEmprendimiento = $_POST['idEmprendimiento'];      
    $menColaboracion = $_POST['menColaboracion'];  

    require_once("../../modelo/colaboracion/colaboracion.php");
    $rpta = registrarColaboracion($idEstudiante, $idPublicacion, $idEmprendimiento, $menColaboracion);

    echo json_encode($rpta);
?>

