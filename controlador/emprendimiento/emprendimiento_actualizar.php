<?php
require_once("../../modelo/emprendimiento/emprendimiento.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_emprendimiento = isset($_POST['id_emprendimiento']) ? intval($_POST['id_emprendimiento']) : 0;
    $nom_emprendimiento = isset($_POST['nom_emprendimiento']) ? trim($_POST['nom_emprendimiento']) : '';
    $des_emprendimiento = isset($_POST['des_emprendimiento']) ? trim($_POST['des_emprendimiento']) : '';
    $img_por_emprendimiento = isset($_POST['img_por_emprendimiento']) ? trim($_POST['img_por_emprendimiento']) : '';

    if ($id_emprendimiento > 0 && !empty($nom_emprendimiento) && !empty($des_emprendimiento)) {
        $rpta = actualizarEmprendimiento($id_emprendimiento, $nom_emprendimiento, $des_emprendimiento, $img_por_emprendimiento);
        echo json_encode($rpta, JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode(array(
            "status" => "error",
            "message" => "Faltan datos obligatorios para actualizar"
        ));
    }
} else {
    echo json_encode(array(
        "status" => "error",
        "message" => "Método no permitido. Use POST"
    ));
}
?>