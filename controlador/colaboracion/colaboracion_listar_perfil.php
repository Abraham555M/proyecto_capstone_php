<?php 
    $idEmprendedor = $_GET['idEmprendedor'];  

    require_once("../../modelo/colaboracion/colaboracion.php");

    $rpta = listarColaboracionDelPerfil($idEmprendedor);
    echo json_encode($rpta);
?>