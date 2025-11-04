<?php
include("../../configuracion/conexion.php");

$id_soporte = $_POST['id_soporte'];
$nuevo_estado = $_POST['nuevo_estado'];

// Traducir valores recibidos desde Android
if ($nuevo_estado == "Aprobada") {
    $nuevo_estado = "Aceptado";
} elseif ($nuevo_estado == "Rechazada") {
    $nuevo_estado = "Rechazado";
}

$query = "UPDATE soporte SET est_soporte = '$nuevo_estado' WHERE id_soporte = $id_soporte";
$result = mysqli_query($con, $query);

if ($result) {
    echo json_encode(["success" => true, "message" => "Estado actualizado correctamente"]);
} else {
    echo json_encode(["success" => false, "message" => "Error al actualizar el estado"]);
}
?>
