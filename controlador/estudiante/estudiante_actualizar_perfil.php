<?php
header("Content-Type: application/json; charset=UTF-8");

require_once("../../configuracion/conexion.php");
require_once("../../modelo/estudiante/estudiante.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $idEstudiante = isset($_POST['id_estudiante']) ? intval($_POST['id_estudiante']) : 0;
    $nombre = trim($_POST['nom_estudiante'] ?? '');
    $apePat = trim($_POST['ape_pat_estudiante'] ?? '');
    $apeMat = trim($_POST['ape_mat_estudiante'] ?? '');
    $correo = trim($_POST['ema_estudiante'] ?? '');
    $telefono = trim($_POST['tel_estudiante'] ?? '');
    $sexo = trim($_POST['sexo'] ?? '');
    $sede = trim($_POST['sede'] ?? '');

    if ($idEstudiante <= 0 || empty($nombre) || empty($apePat) || empty($correo)) {
        echo json_encode(["success" => false, "message" => "Datos incompletos."]);
        exit;
    }

    $resultado = actualizarPerfilEstudiante($idEstudiante, $nombre, $apePat, $apeMat, $correo, $telefono, $sexo, $sede);

    if ($resultado) {
        echo json_encode(["success" => true, "message" => "Perfil actualizado correctamente."]);
    } else {
        echo json_encode(["success" => false, "message" => "Error al actualizar el perfil."]);
    }

} else {
    echo json_encode(["success" => false, "message" => "Método no permitido."]);
}

if (isset($con)) {
    $con->close();
}
?>
