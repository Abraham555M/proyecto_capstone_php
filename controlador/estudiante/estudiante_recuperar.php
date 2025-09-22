<?php
$accion = isset($_POST['accion']) ? $_POST['accion'] : null;
require_once("../../modelo/estudiante/estudiante.php");

switch ($accion) {
    case "enviar_codigo":
    $correo = $_POST['ema_estudiante'];
    $rpta = EnviarCodigoRecuperacion($correo);
    echo json_encode($rpta);
    break;

case "validar_codigo":
    $correo = $_POST['ema_estudiante'];
    $codigo = $_POST['codigo'];
    $rpta = ValidarCodigoRecuperacion($correo, $codigo);
    echo json_encode($rpta);
    break;

case "cambiar_password":
    $correo = $_POST['ema_estudiante'];
    $newPass = $_POST['nueva_password'];
    $rpta = CambiarPassword($correo, $newPass);
    echo json_encode($rpta);
    break;

    default:
        echo json_encode(["status" => "error", "message" => "Acción no válida"]);
        break;
}
?>
