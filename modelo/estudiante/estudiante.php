<?php
require_once "../../configuracion/conexion.php";

// Importar PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../../vendor/autoload.php'; // asegúrate de tener PHPMailer instalado con Composer

function crearCuenta($con, $nombres, $apePat, $apeMat, $correo, $contrasena, $celular, $sexo, $sede) {
    // Cifrar la contraseña
    $passwordHash = password_hash($contrasena, PASSWORD_BCRYPT);

    // Generar código de verificación de 4 dígitos
    $codigo = rand(1000, 9999);

    // ======= Enviar correo con PHPMailer =======
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'gohecaze@gmail.com'; // tu Gmail
        $mail->Password   = 'tgld ngvj dlsk abll';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('gohecaze@gmail.com', 'TuApp');
        $mail->addAddress($correo, $nombres);

        $mail->isHTML(true);
        $mail->Subject = 'Código de verificación - TuApp';
        $mail->Body    = "Hola <b>$nombres</b>,<br><br>Tu código de verificación es: <b>$codigo</b><br><br>Por favor ingrésalo en la aplicación para activar tu cuenta.";

        $mail->send();
    } catch (Exception $e) {
        return ["status" => "error", "msg" => "No se pudo enviar el correo: {$mail->ErrorInfo}"];
    }

    // ======= Guardar datos en BD =======
    $sql = "INSERT INTO estudiante 
        (id_sexo, id_sede, id_tipo_usuario, nom_estudiante, ape_pat_estudiante, ape_mat_estudiante, fch_reg_estudiante, est_estudiante, ema_estudiante, pas_estudiante, cod_estudiante) 
        VALUES (1, 1, 1, ?, ?, ?, NOW(), 1, ?, ?, ?)";

    $stmt = $con->prepare($sql);

    if ($stmt === false) {
        return ["status" => "error", "msg" => $con->error];
    }

    // Vincular parámetros dinámicos
    $stmt->bind_param("ssssss", $nombres, $apePat, $apeMat, $correo, $passwordHash, $codigo);

    if ($stmt->execute()) {
        return [
            "status" => "ok",
            "msg" => "Cuenta creada correctamente. Código enviado a tu correo.",
            "codigo" => $codigo
        ];
    } else {
        return ["status" => "error", "msg" => $stmt->error];
    }
}

function validarCodigo($con, $email, $codigo) {
    $sql = "SELECT * FROM estudiante WHERE ema_estudiante = ? AND cod_estudiante = ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("ss", $email, $codigo);
    $stmt->execute();
    $result = $stmt->get_result();

    $existe = $result->num_rows > 0;

    // Opcional: borrar código usado para que no se pueda reutilizar
    if ($existe) {
        $updateStmt = $con->prepare("UPDATE estudiante SET cod_estudiante = NULL WHERE ema_estudiante = ?");
        $updateStmt->bind_param("s", $email);
        $updateStmt->execute();
    }

    $stmt->close();
    return $existe;
}

?>
