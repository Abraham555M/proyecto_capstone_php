<?php 
    $idEmprendimiento = $_GET['idEmprendimiento'];  
    
    require_once("../../modelo/emprendimiento/emprendimiento.php");

    $rpta = obtenerEstudianteDelEmprendimiento($idEmprendimiento);
    echo json_encode($rpta);
?>