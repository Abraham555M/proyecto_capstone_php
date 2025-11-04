<?php
include("../../configuracion/conexion.php");

$id_estudiante = $_GET['id_estudiante'];

$query = "SELECT id_soporte, est_soporte 
          FROM soporte 
          WHERE id_estudiante = $id_estudiante 
          ORDER BY fec_soporte DESC
          LIMIT 1";

$result = mysqli_query($con, $query);

if ($row = mysqli_fetch_assoc($result)) {
    echo json_encode([
        "success" => true,
        "id_soporte" => $row['id_soporte'],
        "estado" => $row['est_soporte']
    ]);
} else {
    echo json_encode([
        "success" => false,
        "estado" => "Sin solicitudes"
    ]);
}
?>
