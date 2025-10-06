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
                // ✅ Comparación con hash de contraseña
                if (password_verify($password, $row['pas_estudiante'])) {
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

            // Generar un código de 4 dígitos
            $codigo = rand(1000, 9999);

            // Guardar el código en la BD
            $sqlUpdate = "UPDATE estudiante 
              SET cod_estudiante = ?, cod_expira = DATE_ADD(NOW(), INTERVAL 10 MINUTE) 
              WHERE id_estudiante = ?";
            $stmtUpdate = mysqli_prepare($con, $sqlUpdate);
            mysqli_stmt_bind_param($stmtUpdate, "ii", $codigo, $idEstudiante);

            if (mysqli_stmt_execute($stmtUpdate)) {
                // Enviar correo con PHPMailer
                $mail = new PHPMailer(true);

                try {
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'alexxanderay@gmail.com';
                    $mail->Password = 'qqcyozqvrldympqq';
                    $mail->SMTPSecure = 'tls';
                    $mail->Port = 587;

                    $mail->setFrom('alexxanderay@gmail.com', 'Soporte Proyecto Capstone');
                    $mail->addAddress($correo);

                    $mail->isHTML(true);
                    $mail->Subject = 'Codigo de recuperacion';
                    $mail->Body = "<h2>Recuperación de contraseña</h2>
                                   <p>Tu código de recuperacion es: <b>$codigo</b></p>
                                   <p>Si no solicitaste este correo, omitir mensaje.</p>";

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

    $data = ["status" => "error", "message" => "Código inválido o expirado"];

    if ($con) {
        $sql = "SELECT cod_estudiante, cod_expira 
                FROM estudiante 
                WHERE ema_estudiante = ?";
        $stmt = mysqli_prepare($con, $sql);
        mysqli_stmt_bind_param($stmt, "s", $correo);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($row = mysqli_fetch_assoc($result)) {
            $codigoBD = $row['cod_estudiante'];
            $expira   = $row['cod_expira'];

            // Verificar código y expiración
            if ((string)$codigoBD === (string)$codigo) {
                if ($expira && strtotime($expira) > time()) {
                    $data = ["status" => "success", "message" => "Código válido"];

                    // 🔹 Borrar el código para que solo se use una vez
                    $sqlBorrar = "UPDATE estudiante 
                                  SET cod_estudiante = NULL, cod_expira = NULL 
                                  WHERE ema_estudiante = ?";
                    $stmtBorrar = mysqli_prepare($con, $sqlBorrar);
                    mysqli_stmt_bind_param($stmtBorrar, "s", $correo);
                    mysqli_stmt_execute($stmtBorrar);
                    mysqli_stmt_close($stmtBorrar);
                } else {
                    $data = ["status" => "error", "message" => "El código ha expirado"];
                }
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

    // Cifrar la nueva contraseña
    $passwordHash = password_hash($newPass, PASSWORD_BCRYPT);

    $sql = "UPDATE estudiante SET pas_estudiante=? WHERE ema_estudiante=?";
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, "ss", $passwordHash, $correo);

    if (mysqli_stmt_execute($stmt)) {
        return ["status"=>"success","message"=>"Contraseña actualizada correctamente"];
    } else {
        return ["status"=>"error","message"=>"Error al actualizar la contraseña"];
    }
}

    // Gonzalo
    function crearCuenta($con, $nombres, $apePat, $apeMat, $correo, $contrasena, $celular, $sexo, $sede) {
        require_once("../../configuracion/conexion.php");

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
            VALUES (?, ?, 1, ?, ?, ?, NOW(), 1, ?, ?, ?)";

        $stmt = $con->prepare($sql);

        if ($stmt === false) {
            return ["status" => "error", "msg" => $con->error];
        }

        // Vincular parámetros dinámicos
        $stmt->bind_param("iissssss", $sexo, $sede, $nombres, $apePat, $apeMat, $correo, $passwordHash, $codigo);

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

    function obtenerSedes($con){
        $sedes = array();

        $sql = "SELECT id_sede, nom_sede FROM sede ORDER BY id_sede ASC";
        $stmt = $con->prepare($sql);
        if ($stmt) {
            $stmt->execute();
            $resultado = $stmt->get_result();

            while ($fila = $resultado->fetch_assoc()) {
                $sedes[] = array(
                    "id_sede" => (int)$fila['id_sede'],
                    "nom_sede" => $fila['nom_sede']
                );
            }

            $stmt->close();
        } else {
            // Opcional: devolver un mensaje de error si falla la consulta
            $sedes[] = array("error" => "Error al preparar la consulta: " . $con->error);
        }

        return $sedes;
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
    
    function obtenerSexo($con){
        $sexo = array();

        $sql = "SELECT id_sexo, nom_sexo FROM sexo ORDER BY id_sexo ASC";
        $stmt = $con->prepare($sql);
        if ($stmt) {
            $stmt->execute();
            $resultado = $stmt->get_result();

            while ($fila = $resultado->fetch_assoc()) {
                $sexo[] = array(
                    "id_sexo" => (int)$fila['id_sexo'],
                    "nom_sexo" => $fila['nom_sexo']
                );
            }

            $stmt->close();
        } else {
            // Opcional: devolver un mensaje de error si falla la consulta
            $sexo[] = array("error" => "Error al preparar la consulta: " . $con->error);
        }

        return $sexo;
    }

    function obtenerInformacionPerfil($idEstudiante) {
        require_once("../../configuracion/conexion.php");

        $sql = "SELECT 
                    e.nom_estudiante AS nombre,
                    s.nom_sede AS sede
                FROM estudiante e
                INNER JOIN sede s ON e.id_sede = s.id_sede
                WHERE e.id_estudiante = ?";

        $stmt = $con->prepare($sql);
        $stmt->bind_param("i", $idEstudiante);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($fila = $resultado->fetch_assoc()) {
            return [
                "nombre" => $fila["nombre"],
                "sede" => $fila["sede"],
            ];
        } else {
            return ["error" => "Estudiante no encontrado"];
        }

        $stmt->close();
        $con->close();
    }



?>