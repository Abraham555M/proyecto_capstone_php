<?php 
    header('Content-Type: application/json; charset=utf-8');

    $idEstudiante = $_GET['idEstudiante'];  

    require_once("../../modelo/emprendimiento/emprendimiento.php");

    $rpta = listarEmprendimientosConPublicaciones($idEstudiante);
    echo json_encode($rpta);
?>