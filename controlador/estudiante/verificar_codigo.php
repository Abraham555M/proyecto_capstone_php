<?php
require_once "../../configuracion/conexion.php";
require_once "../../modelo/estudiante/estudiante.php";

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email  = $_POST['email'] ?? '';
    $codigo = $_POST['codigo'] ?? '';

    if (empty($email) || empty($codigo)) {
        http_response_code(400);
        echo json_encode(["error" => "Faltan parámetros"]);
        exit;
    }

    // Supongamos que tienes una función validarCodigo($con, $email, $codigo) en estudiante.php
    $resultado = validarCodigo($con, $email, $codigo);

    if ($resultado) {
        echo "OK";
    } else {
        echo "INVALIDO";
    }
}
?>