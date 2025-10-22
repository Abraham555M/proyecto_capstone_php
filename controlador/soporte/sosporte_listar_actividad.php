<?php 
    $idEmprendedor = $_GET['idEmprendedor'];  

    require_once("../../modelo/soporte/soporte.php");

    $rpta = listarActividadesNotificaciones($idEmprendedor);
    echo json_encode($rpta);
?>