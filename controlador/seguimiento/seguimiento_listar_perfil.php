<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");

require_once("../../modelo/seguimiento/seguimiento.php");

if (isset($_GET['idEmprendedor'])) {
    $idEmprendedor = intval($_GET['idEmprendedor']);
    $rpta = obtenerCantidadSeguidores($idEmprendedor);

    echo json_encode([
        "total_seguidores" => $rpta
    ]);
} else {
    echo json_encode([
        "error" => "Falta el parámetro idEmprendedor"
    ]);
}
?>
