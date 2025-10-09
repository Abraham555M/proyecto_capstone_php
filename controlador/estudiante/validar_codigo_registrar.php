<?php
    header("Content-Type: application/json; charset=UTF-8");
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: POST");

    $codigo     = isset($_POST['codigo']) ? trim($_POST['codigo']) : '';
    $correo     = isset($_POST['correo']) ? trim($_POST['correo']) : '';
    $nombres    = isset($_POST['nombres']) ? trim($_POST['nombres']) : '';
    $apePat     = isset($_POST['apePat']) ? trim($_POST['apePat']) : '';
    $apeMat     = isset($_POST['apeMat']) ? trim($_POST['apeMat']) : '';
    $contrasena = isset($_POST['contrasena']) ? trim($_POST['contrasena']) : '';
    $celular    = isset($_POST['celular']) ? trim($_POST['celular']) : '';
    $id_sexo    = isset($_POST['id_sexo']) ? intval($_POST['id_sexo']) : 0;
    $id_sede    = isset($_POST['id_sede']) ? intval($_POST['id_sede']) : 0;

    if (empty($codigo) || empty($correo) || empty($nombres) || empty($apePat) || empty($apeMat) || empty($contrasena)) {
        echo json_encode(["status" => "error", "msg" => "Faltan datos"]);
        exit();
    }

    require_once "../../modelo/estudiante/estudiante.php";

    $resultado = verificarCodigoYRegistrar(
        $codigo,
        $correo,
        $nombres,
        $apePat,
        $apeMat,
        $contrasena,
        $celular,
        $id_sexo,
        $id_sede
    );

    echo json_encode($resultado);
?>
