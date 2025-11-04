<?php
include("../../configuracion/conexion.php");

$response = [];

$sql = "SELECT s.id_soporte, e.nom_estudiante, s.men_soporte, s.fec_soporte, s.est_soporte
        FROM soporte s
        INNER JOIN estudiante e ON s.id_estudiante = e.id_estudiante
        ORDER BY s.fec_soporte DESC";

$query = mysqli_query($con, $sql);

while ($row = mysqli_fetch_assoc($query)) {
    $response[] = $row;
}

echo json_encode($response);
?>
