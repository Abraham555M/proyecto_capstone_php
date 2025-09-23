<?php
    header('Content-Type: application/json');

    require_once "../../configuracion/conexion.php";
    require_once "../../modelo/estudiante/estudiante.php";

    $resultado = obtenerSexo($con);

    echo json_encode($resultado);

    $con->close();
?>
