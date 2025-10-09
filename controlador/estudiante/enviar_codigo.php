<?php
    header('Content-Type: application/json');
    error_reporting(0);

    $correo = isset($_POST['correo']) ? trim($_POST['correo']) : '';
    $nombres = isset($_POST['nombres']) ? trim($_POST['nombres']) : '';

    if (empty($correo) || empty($nombres)) {
        echo json_encode(["status" => "error", "msg" => "Faltan datos"]);
        exit;
    }

    require_once "../../modelo/estudiante/estudiante.php";

    $resultado = enviarCodigo($correo, $nombres);
    echo json_encode($resultado);
?>
