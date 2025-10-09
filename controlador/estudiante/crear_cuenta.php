<?php
    $nombres    = $_POST['nombres'] ?? '';
    $apePat     = $_POST['apePat'] ?? '';
    $apeMat     = $_POST['apeMat'] ?? '';
    $correo     = $_POST['correo'] ?? '';
    $contrasena = $_POST['contrasena'] ?? '';
    $celular    = $_POST['celular'] ?? '';
    $sexo       = $_POST['sexo'] ?? '';
    $sede       = $_POST['sede'] ?? '';
    $codigo     = $_POST['codigo'] ?? '';

    require_once "../../modelo/estudiante/estudiante.php";

   
    $resultado = crearCuenta($nombres, $apePat, $apeMat, $correo, $contrasena, $celular, $sexo, $sede, $codigo);

    echo json_encode($resultado);
?>
