<?php
    $correo   = isset($_POST['ema_estudiante']) ? $_POST['ema_estudiante'] : null;
    $password = isset($_POST['pas_estudiante']) ? $_POST['pas_estudiante'] : null;

    require_once("../../modelo/estudiante/estudiante.php");

    if ($correo && $password) {
        $rpta = LoginEstudiante($correo, $password);
    } else {
        $rpta = array("status" => "error", "message" => "Faltan parámetros");
    }

    echo json_encode($rpta);
?>