<?php
header('Content-Type: application/json');
include '../../configuracion/conexion.php';

if(isset($_POST['id_estudiante']) && isset($_POST['men_soporte'])) {
    $id_estudiante = $_POST['id_estudiante'];
    $men_soporte = $_POST['men_soporte'];

    // Insertar con estado por defecto "Pendiente"
    $sql = "INSERT INTO soporte (id_estudiante, men_soporte, fec_soporte, est_soporte) VALUES (?, ?, NOW(), 'Pendiente')";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("is", $id_estudiante, $men_soporte);

    if($stmt->execute()) {
        echo json_encode(["status" => "success", "msg" => "Solicitud enviada correctamente."]);
    } else {
        echo json_encode(["status" => "error", "msg" => "Error al enviar la solicitud."]);
    }

    $stmt->close();
} else {
    echo json_encode(["status"=>"error","msg"=>"Faltan datos."]);
}

$con->close();
?>
