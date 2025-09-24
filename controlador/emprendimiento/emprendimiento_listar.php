<?php
require_once("../../modelo/emprendimiento/emprendimiento.php");

if (isset($_GET['id_estudiante'])) {
    $id_estudiante = intval($_GET['id_estudiante']);
    $rpta = listarEmprendimientos($id_estudiante);
    echo json_encode($rpta, JSON_UNESCAPED_UNICODE);
} else {
    echo json_encode(array(
        "status" => "error",
        "message" => "Falta el parámetro id_estudiante",
        "emprendimientos" => array()
    ));
}
