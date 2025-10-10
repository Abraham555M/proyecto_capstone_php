<?php
header("Content-Type: application/json; charset=UTF-8");

require_once("../../configuracion/conexion.php");
require_once("../../modelo/estudiante/estudiante.php");

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $idEstudiante = isset($_GET['idEstudiante']) ? intval($_GET['idEstudiante']) : 0;

    if ($idEstudiante <= 0) {
        echo json_encode(["error" => true, "message" => "ID de estudiante inválido"]);
        exit;
    }

    $datos = obtenerInformacionEstudiante($idEstudiante);

    if ($datos) {
        echo json_encode($datos, JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode(["error" => true, "message" => "Estudiante no encontrado"]);
    }
} else {
    echo json_encode(["error" => true, "message" => "Método no permitido"]);
}

if (isset($con)) {
    $con->close();
}
?>
