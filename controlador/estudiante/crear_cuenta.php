<?php
require_once "../../configuracion/conexion.php";
require_once "../../modelo/estudiante/estudiante.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombres    = $_POST['nombres'] ?? '';
    $apePat     = $_POST['apePat'] ?? '';
    $apeMat     = $_POST['apeMat'] ?? '';
    $correo     = $_POST['correo'] ?? '';
    $contrasena = $_POST['contrasena'] ?? '';
    $celular    = $_POST['celular'] ?? '';
    $sexo       = $_POST['sexo'] ?? '';
    $sede       = $_POST['sede'] ?? '';
    $codigo     = $_POST['codigo'] ?? '';

    $resultado = crearCuenta($con, $nombres, $apePat, $apeMat, $correo, $contrasena, $celular, $sexo, $sede, $codigo);

    echo json_encode($resultado);
}
