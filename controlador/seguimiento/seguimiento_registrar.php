<?php
    $idEstudiante = $_POST['idEstudiante'];  
    $idEmprendimiento = $_POST['idEmprendimiento'];  

    require_once("../../modelo/seguimiento/seguimiento.php");
    $rpta = registrarSeguimiento($idEstudiante, $idEmprendimiento);
    echo json_encode($rpta);
?>

