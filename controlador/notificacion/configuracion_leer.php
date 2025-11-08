<?php
// RUTA: controlador/notificacion/configuracion_leer.php

require_once('../../vendor/autoload.php'); // Cargar Composer
require_once("../../modelo/notificacion/notificacion.php"); 
require_once("../../configuracion/conexion.php"); // Necesitamos $con

header('Content-Type: application/json');
global $con; // Obtener la conexión

$idEstudiante = $_GET['idEstudiante'] ?? null; // Usamos GET para leer

if (empty($idEstudiante)) {
    echo json_encode(array("status" => "error", "message" => "ID de estudiante requerido."));
    exit;
}

$rpta = leerConfiguracionNotificaciones($con, $idEstudiante);
mysqli_close($con);
echo json_encode($rpta);
?>