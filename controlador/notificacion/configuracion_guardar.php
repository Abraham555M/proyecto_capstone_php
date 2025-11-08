<?php
// RUTA: controlador/notificacion/configuracion_guardar.php

require_once('../../vendor/autoload.php'); // Cargar Composer
require_once("../../modelo/notificacion/notificacion.php"); 
require_once("../../configuracion/conexion.php"); // Necesitamos $con

header('Content-Type: application/json');
global $con; // Obtener la conexión

$idEstudiante = $_POST['idEstudiante'] ?? null;
// Convertimos los 'true'/'false' de Java a 1/0 para la DB
$publicaciones = ($_POST['publicaciones'] ?? 'true') == 'true' ? 1 : 0;
$comentarios = ($_POST['comentarios'] ?? 'true') == 'true' ? 1 : 0;
$likes = ($_POST['likes'] ?? 'true') == 'true' ? 1 : 0;

if (empty($idEstudiante)) {
    echo json_encode(array("status" => "error", "message" => "ID de estudiante requerido."));
    exit;
}

$rpta = guardarConfiguracionNotificaciones($con, $idEstudiante, $publicaciones, $comentarios, $likes);
mysqli_close($con);
echo json_encode($rpta);
?>