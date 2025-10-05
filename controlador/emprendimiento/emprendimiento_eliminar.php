<?php
require_once("../../modelo/emprendimiento/emprendimiento.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_emprendimiento = isset($_POST['id_emprendimiento']) ? intval($_POST['id_emprendimiento']) : 0;

    if ($id_emprendimiento > 0) {
        $rpta = eliminarEmprendimiento($id_emprendimiento);
        echo json_encode($rpta, JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode(array(
            "status" => "error",
            "message" => "ID de emprendimiento inválido"
        ));
    }
} else {
    echo json_encode(array(
        "status" => "error",
        "message" => "Método no permitido. Use POST"
    ));
}
?>