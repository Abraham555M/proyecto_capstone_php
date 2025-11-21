<?php
header("Content-Type: application/json");

include '../../configuracion/conexion.php';
include '../../modelo/publicacion/publicacion.php';

$response = ["status" => "error", "msg" => "Error desconocido"];

$id_publicacion = $_POST['id_publicacion'] ?? null;

if (!$id_publicacion) {
    echo json_encode(["status" => "error", "msg" => "Faltan datos"]);
    exit;
}

$ok = eliminarPublicacion($con, $id_publicacion);

if ($ok) {
    $response = ["status" => "success"];
} else {
    $response = ["status" => "error", "msg" => "No se pudo actualizar"];
}

echo json_encode($response);
