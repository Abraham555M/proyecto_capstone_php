<?php 
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;

    require '../../vendor/autoload.php';

    function LoginEstudiante($correo, $password){
        require_once("../../configuracion/conexion.php");

        $data = array("status" => "error", "message" => "No se pudo iniciar sesión");

        if ($con) {
            $sql = "SELECT id_estudiante, nom_estudiante, ape_pat_estudiante, ape_mat_estudiante, 
                        pas_estudiante, id_tipo_usuario
                    FROM estudiante 
                    WHERE ema_estudiante = ? AND est_estudiante = 1";

            if ($stmt = mysqli_prepare($con, $sql)) {
                mysqli_stmt_bind_param($stmt, "s", $correo);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);

                if ($row = mysqli_fetch_assoc($result)) {
                    // ✅ Comparación simple sin hash
                    if ($password === $row['pas_estudiante']) {
                        $data = array(
                            "status" => "success",
                            "message" => "Inicio de sesión correcto",
                            "usuario" => array(
                                "id_estudiante" => $row['id_estudiante'],
                                "nombre" => $row['nom_estudiante'],
                                "apellidos" => $row['ape_pat_estudiante']." ".$row['ape_mat_estudiante'],
                                "tipo_usuario" => $row['id_tipo_usuario']
                            )
                        );
                    } else {
                        $data = array(
                            "status" => "error",
                            "message" => "La contraseña ingresada es incorrecta"
                        );
                    }
                } else {
                    $data = array(
                        "status" => "error",
                        "message" => "El correo no está registrado"
                    );
                }

                mysqli_stmt_close($stmt);
            } else {
                $data = array(
                    "status" => "error",
                    "message" => "Error al preparar la consulta: " . mysqli_error($con)
                );
            }
        } else {
            $data = array(
                "status" => "error",
                "message" => "Error en la conexión a la BD"
            );
        }

        mysqli_close($con);
        return $data;
    }

    function EnviarCodigoRecuperacion($correo) {
        require_once("../../configuracion/conexion.php");

        $data = array("status" => "error", "message" => "No se pudo enviar el código");

        if ($con) {
            // Verificar si existe el correo
            $sql = "SELECT id_estudiante FROM estudiante WHERE ema_estudiante = ?";
            $stmt = mysqli_prepare($con, $sql);
            mysqli_stmt_bind_param($stmt, "s", $correo);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);

            if ($row = mysqli_fetch_assoc($result)) {
                $idEstudiante = $row['id_estudiante'];

                // Generar un código de 6 dígitos
                $codigo = rand(100000, 999999);

                // Guardar el código en la BD
                $sqlUpdate = "UPDATE estudiante SET codigo_recuperacion = ? WHERE id_estudiante = ?";
                $stmtUpdate = mysqli_prepare($con, $sqlUpdate);
                mysqli_stmt_bind_param($stmtUpdate, "ii", $codigo, $idEstudiante);

                if (mysqli_stmt_execute($stmtUpdate)) {
                    // Enviar correo con PHPMailer
                    $mail = new PHPMailer(true);

                    try {
                        // Configuración SMTP (ejemplo con Gmail)
                        $mail->isSMTP();
                        $mail->Host = 'smtp.gmail.com';
                        $mail->SMTPAuth = true;
                        $mail->Username = 'alexxanderay@gmail.com'; // 👉 pon tu correo
                        $mail->Password = 'qqcyozqvrldympqq'; // 👉 NO la contraseña normal, usa clave de aplicación
                        $mail->SMTPSecure = 'tls';
                        $mail->Port = 587;

                        // Remitente y destinatario
                        $mail->setFrom('tu_correo@gmail.com', 'Soporte Proyecto Capstone');
                        $mail->addAddress($correo);

                        // Contenido
                        $mail->isHTML(true);
                        $mail->Subject = 'Código de recuperación';
                        $mail->Body = "<h2>Recuperación de contraseña</h2>
                                    <p>Tu código de recuperación es: <b>$codigo</b></p>
                                    <p>Si no solicitaste este correo, ignóralo.</p>";

                        $mail->send();

                        $data = array("status" => "success", "message" => "Código enviado al correo");
                    } catch (Exception $e) {
                        $data = array("status" => "error", "message" => "No se pudo enviar el correo. Error: {$mail->ErrorInfo}");
                    }
                }
                mysqli_stmt_close($stmtUpdate);
            } else {
                $data = array("status" => "error", "message" => "El correo no está registrado");
            }

            mysqli_stmt_close($stmt);
            mysqli_close($con);
        } else {
            $data = array("status" => "error", "message" => "Error en la conexión a la BD");
        }

        return $data;
    }

    function ValidarCodigoRecuperacion($correo, $codigo) {
        require_once("../../configuracion/conexion.php");

        $data = ["status" => "error", "message" => "Código inválido"];

        if ($con) {
            $sql = "SELECT codigo_recuperacion FROM estudiante WHERE ema_estudiante = ?";
            $stmt = mysqli_prepare($con, $sql);
            mysqli_stmt_bind_param($stmt, "s", $correo);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);

            if ($row = mysqli_fetch_assoc($result)) {
                // Comparación flexible por si uno es string y otro int
                if ((string)$row['codigo_recuperacion'] == (string)$codigo) {
                    $data = ["status" => "success", "message" => "Código válido"];
                }
            }

            mysqli_stmt_close($stmt);
            mysqli_close($con);
        } else {
            $data = ["status" => "error", "message" => "Error en la conexión a la BD"];
        }

        return $data;
    }
    
    function CambiarPassword($correo, $newPass) {
        require_once("../../configuracion/conexion.php");

        $sql = "UPDATE estudiante SET pas_estudiante=? WHERE ema_estudiante=?";
        $stmt = mysqli_prepare($con, $sql);
        mysqli_stmt_bind_param($stmt, "ss", $newPass, $correo);

        if (mysqli_stmt_execute($stmt)) {
            return ["status"=>"success","message"=>"Contraseña actualizada correctamente"];
        } else {
            return ["status"=>"error","message"=>"Error al actualizar la contraseña"];
        }
    }

?>