<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
error_reporting(0);

require_once "../../modelo/publicacion/publicacion.php";

// Validar parámetros
$camposObligatorios = [
    "id_emprendimiento",
    "id_tipo_publicacion",
    "tit_publicacion",
    "con_publicacion",
    "est_publicacion"
];

foreach ($camposObligatorios as $campo) {
    if (!isset($_POST[$campo])) {
        echo json_encode([
            "success" => false,
            "message" => "Falta el campo: $campo"
        ]);
        exit;
    }
}

// Ejecutar modelo
$resultado = agregarPublicacion($_POST);

// Responder
echo json_encode($resultado, JSON_UNESCAPED_UNICODE);
?>
