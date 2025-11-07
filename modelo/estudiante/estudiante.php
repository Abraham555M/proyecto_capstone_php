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

    function enviarCodigo($correo, $nombres) {
        require_once("../../configuracion/conexion.php");

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

        // ✉️ Enviar correo
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'gohecaze@gmail.com';
            $mail->Password   = 'tgld ngvj dlsk abll'; // ⚠️ contraseña de aplicación
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
            return ["status" => "ok", "msg" => "Código enviado al correo"];
        } catch (Exception $e) {
            return ["status" => "error", "msg" => "No se pudo enviar el correo"];
        }
    }

    function verificarCodigoYRegistrar($codigo, $correo, $nombres, $apePat, $apeMat, $contrasena, $celular, $id_sexo, $id_sede) {
        require_once("../../configuracion/conexion.php");
        $fecha_reg  = date("Y-m-d H:i:s");
        $id_tipo_usuario = 1; // por defecto

        try {
            // Paso 1: Buscar el registro temporal
            $sql = "SELECT cod_estudiante, cod_expira FROM estudiante WHERE ema_estudiante = ?";
            $stmt = $con->prepare($sql);
            $stmt->bind_param("s", $correo);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();

            if (!$row) {
                return ["status" => "error", "msg" => "No se encontró el correo"];
            }

            $codigo_bd = $row['cod_estudiante'];
            $codigo_expira = $row['cod_expira'];

            // Paso 2: Validar código
            $ahora = new DateTime();
            $expira = new DateTime($codigo_expira);

            if ($codigo != $codigo_bd) {
                return ["status" => "codigo_invalido", "msg" => "Código incorrecto"];
            }

            if ($ahora > $expira) {
                return ["status" => "codigo_expirado", "msg" => "El código ha expirado"];
            }

            // Paso 3: Actualizar registro
            $passwordHash = password_hash($contrasena, PASSWORD_BCRYPT);
            $sqlUpdate = "UPDATE estudiante SET 
                id_sexo = ?, 
                id_sede = ?, 
                id_tipo_usuario = ?, 
                nom_estudiante = ?, 
                ape_pat_estudiante = ?, 
                ape_mat_estudiante = ?, 
                fch_reg_estudiante = ?, 
                est_estudiante = 1,
                tel_estudiante = ?, 
                pas_estudiante = ?
            WHERE ema_estudiante = ?";
            $stmtUp = $con->prepare($sqlUpdate);
            $stmtUp->bind_param(
                "iiisssssss",
                $id_sexo,
                $id_sede,
                $id_tipo_usuario,
                $nombres,
                $apePat,
                $apeMat,
                $fecha_reg,
                $celular,
                $passwordHash,
                $correo
            );
            $stmtUp->execute();

            // Paso 4: Obtener los datos completos del usuario actualizado
            $sqlSelect = "SELECT id_estudiante, nom_estudiante, 
                            CONCAT(ape_pat_estudiante, ' ', ape_mat_estudiante) AS apellidos, 
                            id_tipo_usuario 
                        FROM estudiante WHERE ema_estudiante = ?";
            $stmtSel = $con->prepare($sqlSelect);
            $stmtSel->bind_param("s", $correo);
            $stmtSel->execute();
            $resultSel = $stmtSel->get_result();
            $user = $resultSel->fetch_assoc();

            return [
                "status" => "ok",
                "msg" => "Cuenta creada correctamente",
                "user" => $user
            ];

        } catch (Exception $e) {
            return ["status" => "error", "msg" => $e->getMessage()];
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
                    CONCAT(e.nom_estudiante, ' ', e.ape_pat_estudiante, ' ', e.ape_mat_estudiante) AS nombre,
                    s.nom_sede AS sede,
                    e.tel_estudiante AS telefono
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
                "telefono" => $fila["telefono"],
            ];
        } else {
            return ["error" => "Estudiante no encontrado"];
        }

        $stmt->close();
        $con->close();
    }

            function obtenerInformacionEstudiante($idEstudiante) {
            require_once("../../configuracion/conexion.php");
            global $con;

            $query = "SELECT 
                        e.id_estudiante,
                        e.nom_estudiante,
                        e.ape_pat_estudiante,
                        e.ape_mat_estudiante,
                        e.ema_estudiante,
                        e.tel_estudiante,
                        s.nom_sede AS sede,
                        sx.nom_sexo AS sexo
                    FROM estudiante e
                    LEFT JOIN sede s ON e.id_sede = s.id_sede
                    LEFT JOIN sexo sx ON e.id_sexo = sx.id_sexo
                    WHERE e.id_estudiante = ?";

            $stmt = $con->prepare($query);
            $stmt->bind_param("i", $idEstudiante);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                return $result->fetch_assoc();
            }

            return null;
        }


            function actualizarPerfilEstudiante($idEstudiante, $nombre, $apePat, $apeMat, $correo, $telefono, $sexo, $sede) {
        global $con;

        $query = "UPDATE estudiante 
                SET nom_estudiante = ?, 
                    ape_pat_estudiante = ?, 
                    ape_mat_estudiante = ?, 
                    ema_estudiante = ?, 
                    tel_estudiante = ?, 
                    id_sexo = (SELECT id_sexo FROM sexo WHERE nom_sexo = ? LIMIT 1),
                    id_sede = (SELECT id_sede FROM sede WHERE nom_sede = ? LIMIT 1)
                WHERE id_estudiante = ?";

        $stmt = $con->prepare($query);
        if (!$stmt) return false;

        $stmt->bind_param("sssssssi", $nombre, $apePat, $apeMat, $correo, $telefono, $sexo, $sede, $idEstudiante);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }
    
    function registrarTokenFCM($idEstudiante, $tokenFCM) {
    // 🚨 IMPORTANTE: Ajusta esta ruta si es diferente
    include("../../configuracion/conexion.php"); 

    // Respuesta por defecto
    $data = array("status" => "error", "message" => "No se pudo actualizar el token FCM.");

    if ($con) {
        // Usamos sentencias preparadas para seguridad y eficiencia
        $sql = "UPDATE estudiante 
                SET token_fcm = ? 
                WHERE id_estudiante = ?";

        if ($stmt = mysqli_prepare($con, $sql)) {
            // "si" significa: s=string (token), i=integer (id_estudiante)
            mysqli_stmt_bind_param($stmt, "si", $tokenFCM, $idEstudiante);

            if (mysqli_stmt_execute($stmt)) {
                // Comprobamos si se afectó alguna fila (se actualizó el token)
                if (mysqli_stmt_affected_rows($stmt) > 0) {
                    $data = array(
                        "status" => "success",
                        "message" => "Token FCM registrado correctamente."
                    );
                } else {
                    // Si affected_rows es 0, el estudiante no existe o el token no cambió
                    $data = array(
                        "status" => "warning",
                        "message" => "Estudiante no encontrado o token ya estaba registrado."
                    );
                }
            } else {
                $data = array(
                    "status" => "error",
                    "message" => "Error al ejecutar la actualización: " . mysqli_error($con)
                );
            }

            mysqli_stmt_close($stmt);
        } else {
            $data = array(
                "status" => "error",
                "message" => "Error al preparar la consulta: " . mysqli_error($con)
            );
        }
        mysqli_close($con);
    }
    
    return $data;
}

?>