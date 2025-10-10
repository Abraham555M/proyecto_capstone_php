<?php
require_once("../../modelo/comentario/comentario.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idComentario = isset($_POST['idComentario']) ? intval($_POST['idComentario']) : 0;
    $idEstudiante = isset($_POST['idEstudiante']) ? intval($_POST['idEstudiante']) : 0;

    if ($idComentario > 0 && $idEstudiante > 0) {
        $rpta = eliminarComentario($idComentario, $idEstudiante);
        echo json_encode($rpta, JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "Parámetros inválidos"
        ]);
    }
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Método no permitido"
    ]);
}
?>