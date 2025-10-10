<?php
    $idEmprendedor = $_GET['idEmprendedor'];  

    require_once("../../modelo/seguimiento/seguimiento.php");
    $rpta = obtenerCantidadSeguidores($idEmprendedor);
    echo json_encode($rpta);
?>

