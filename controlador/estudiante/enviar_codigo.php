<?php
require_once("../../configuracion/conexion.php");
require '../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Evitar warnings para que JSON sea válido
error_reporting(0);

// Recibir datos del POST
$correo = isset($_POST['correo']) ? $_POST['correo'] : '';
$nombres = isset($_POST['nombres']) ? $_POST['nombres'] : '';

header('Content-Type: application/json');

if (empty($correo) || empty($nombres)) {
    echo json_encode(["status" => "error", "msg" => "Faltan datos"]);
    exit;
}

// Generar código de verificación de 4 dígitos
$codigo = rand(1000, 9999);

// Guardar el código en cod_estudiante
$stmt = $con->prepare("UPDATE estudiante SET cod_estudiante = ? WHERE ema_estudiante = ?");
$stmt->bind_param("ss", $codigo, $correo);

if(!$stmt->execute()){
    echo json_encode(["status" => "error", "msg" => "No se pudo guardar el código"]);
    exit;
}

// ======= Enviar correo con PHPMailer =======
$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'gohecaze@gmail.com'; // tu Gmail
    $mail->Password   = 'tgld ngvj dlsk abll'; // password de aplicación
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    $mail->setFrom('gohecaze@gmail.com', 'TuApp');
    $mail->addAddress($correo, $nombres);

    $mail->isHTML(true);
    $mail->Subject = 'Código de verificación - TuApp';
    $mail->Body    = "Hola <b>$nombres</b>,<br><br>Tu codigo de verificacion es: <b>$codigo</b><br><br>Por favor ingresalo en la aplicacion para activar tu cuenta.";

    $mail->send();

    // Retornar JSON con el código
    echo json_encode(["status" => "ok", "codigo" => (string)$codigo]);

} catch (Exception $e) {
    echo json_encode(["status" => "error", "msg" => "No se pudo enviar el correo: {$mail->ErrorInfo}"]);
}
?>
