<?php
include("../../configuracion/conexion.php");

$response = [];

// Recibir parámetros GET
$fecha_inicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : '';
$fecha_fin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : '';

// Base de las consultas
$sqlPublicaciones = "SELECT COUNT(*) AS total FROM publicacion WHERE est_publicacion = 1";
$sqlColabAceptadas = "SELECT COUNT(*) AS total FROM colaboracion WHERE est_colaboracion = 1";
$sqlColabPendientes = "SELECT COUNT(*) AS total FROM colaboracion WHERE est_colaboracion = 0";
$sqlReportes = "SELECT COUNT(*) AS total FROM reporte WHERE est_reporte = 1";

// Si hay rango de fechas, se agrega a las consultas
if (!empty($fecha_inicio) && !empty($fecha_fin)) {
    $sqlPublicaciones .= " AND DATE(fch_publicacion) BETWEEN '$fecha_inicio' AND '$fecha_fin'";
    $sqlColabAceptadas .= " AND DATE(fch_colaboracion) BETWEEN '$fecha_inicio' AND '$fecha_fin'";
    $sqlColabPendientes .= " AND DATE(fch_colaboracion) BETWEEN '$fecha_inicio' AND '$fecha_fin'";
    $sqlReportes .= " AND DATE(fch_reporte) BETWEEN '$fecha_inicio' AND '$fecha_fin'";
}

// Ejecutar las consultas
$q1 = mysqli_query($con, $sqlPublicaciones);
$q2 = mysqli_query($con, $sqlColabAceptadas);
$q3 = mysqli_query($con, $sqlColabPendientes);
$q4 = mysqli_query($con, $sqlReportes);

// Construir respuesta JSON
$response['publicaciones_activas'] = mysqli_fetch_assoc($q1)['total'];
$response['colaboraciones_aceptadas'] = mysqli_fetch_assoc($q2)['total'];
$response['colaboraciones_pendientes'] = mysqli_fetch_assoc($q3)['total'];
$response['reportes_pendientes'] = mysqli_fetch_assoc($q4)['total'];

echo json_encode($response);
?>

