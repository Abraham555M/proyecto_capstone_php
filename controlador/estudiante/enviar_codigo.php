<?php
require_once("../../configuracion/conexion.php");
require '../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

error_reporting(0);
header('Content-Type: application/json');

$correo = isset($_POST['correo']) ? trim($_POST['correo']) : '';
$nombres = isset($_POST['nombres']) ? trim($_POST['nombres']) : '';

if (empty($correo) || empty($nombres)) {
    echo json_encode(["status" => "error", "msg" => "Faltan datos"]);
    exit;
}

$codigo = rand(1000, 9999);
$expira = date("Y-m-d H:i:s", strtotime("+10 minutes"));

// ✅ Verificar si ya existe un registro con ese correo
$sqlCheck = "SELECT id_estudiante FROM estudiante WHERE ema_estudiante = ?";
$stmtCheck = $con->prepare($sqlCheck);
$stmtCheck->bind_param("s", $correo);
$stmtCheck->execute();
$result = $stmtCheck->get_result();

if ($result->num_rows > 0) {
    // 📌 Si ya existe, solo actualizar código y expiración
    $sqlUpdate = "UPDATE estudiante SET cod_estudiante = ?, cod_expira = ?, est_estudiante = 0 WHERE ema_estudiante = ?";
    $stmtUp = $con->prepare($sqlUpdate);
    $stmtUp->bind_param("sss", $codigo, $expira, $correo);
    $stmtUp->execute();
} else {
    // 🆕 Si no existe, insertar registro vacío con código
    $sqlInsert = "INSERT INTO estudiante (ema_estudiante, cod_estudiante, cod_expira, est_estudiante) VALUES (?, ?, ?, 0)";
    $stmtInsert = $con->prepare($sqlInsert);
    $stmtInsert->bind_param("sss", $correo, $codigo, $expira);
    $stmtInsert->execute();
}

// ✉️ Enviar el correo con el código
$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'gohecaze@gmail.com';
    $mail->Password   = 'tgld ngvj dlsk abll'; // ⚠️ tu contraseña de aplicación
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    $mail->setFrom('gohecaze@gmail.com', 'TuApp');
    $mail->addAddress($correo, $nombres);

    $mail->isHTML(true);
    $mail->Subject = 'Código de verificación - TuApp';
    $mail->Body = "
        Hola <b>$nombres</b>,<br><br>
        Tu código de verificación es: <b>$codigo</b><br><br>
        Este código expirará en 10 minutos.
    ";

    $mail->send();
    echo json_encode(["status" => "ok", "msg" => "Código enviado al correo"]);

} catch (Exception $e) {
    echo json_encode(["status" => "error", "msg" => "No se pudo enviar el correo"]);
}
?>
