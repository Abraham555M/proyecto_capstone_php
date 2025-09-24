<?php
include '../../configuracion/conexion.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $correo = $_POST['correo'];

    $sql = "SELECT id_estudiante FROM estudiante WHERE ema_estudiante = ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        echo json_encode(["success" => false, "mensaje" => "El correo ya está registrado"]);
    } else {
        echo json_encode(["success" => true, "mensaje" => "Correo válido"]);
    }

    $stmt->close();
    $con->close();
}
?>

