<?php 

require_once(__DIR__ . '/../notificacion/notificacion.php'); 

function registrarComentario($idPublicacion, $idEstudiante, $conComentario){
    require_once("../../configuracion/conexion.php");
    $data = array("status" => "error", "message" => "No se pudo registrar el comentario");

    if ($con) {
        $sql = "INSERT INTO comentario (id_publicacion, id_estudiante, con_comentario, fch_comentario, est_comentario) 
                VALUES (?, ?, ?, NOW(), ?)";

        if ($stmt = mysqli_prepare($con, $sql)) {
            $estado = 1; 
            mysqli_stmt_bind_param($stmt, "iisi", $idPublicacion, $idEstudiante, $conComentario, $estado);

            if (mysqli_stmt_execute($stmt)) {
                $id_insertado = mysqli_insert_id($con);
                
                $data = array(
                    "status" => "success",
                    "message" => "Comentario registrado correctamente",
                    "id_insertado" => $id_insertado
                );
                
                // --- 🚨 LÓGICA DE NOTIFICACIÓN ACTUALIZADA (DB y FCM) 🚨 ---
                $datos_notificacion = obtenerDatosNotificacionComentario($con, $id_insertado, $idEstudiante, true); 
                
                if ($datos_notificacion) { 
                    $id_receptor = $datos_notificacion['id_receptor_final']; 

                    if ($id_receptor != $idEstudiante) { 
                        
                        // Verificamos si el receptor quiere recibir notificaciones de COMENTARIOS
                        if ($datos_notificacion['notif_comentarios'] == 1) {

                            $token_destino = $datos_notificacion['token_dueno'];
                            $nombre_interactor = $datos_notificacion['nombre_interactor'];
                            
                            $titulo_fcm = "¡Nuevo Comentario! 💬";
                            $cuerpo_fcm = $nombre_interactor . " ha comentado tu publicación.";
                            $id_tipo_notif = 3; // 3 = 'nuevoComentario'

                            // 1. REGISTRAR EN LA TABLA NOTIFICACION
                            $rpta_db = registrarNotificacionDB($con, $id_receptor, $idEstudiante, $id_tipo_notif, $titulo_fcm, $cuerpo_fcm);
                            $data['notificacion_db'] = $rpta_db; 

                            // 2. ENVIAR NOTIFICACIÓN PUSH FCM
                            $payload = array(
                                "action" => "NEW_COMMENT", 
                                "id_publicacion" => $idPublicacion,
                                "id_emprendimiento" => $datos_notificacion['id_emprendimiento'] ?? null
                            );
                            $rpta_fcm = enviarNotificacionFCM($token_destino, $titulo_fcm, $cuerpo_fcm, $payload);
                            $data['fcm_result'] = $rpta_fcm;
                        }
                    }
                }
                // --- FIN LÓGICA NOTIFICACIÓN ---
                
            } else { 
                 $data = array("status" => "error", "message" => "Error al ejecutar la consulta: " . mysqli_error($con));
            }
            mysqli_stmt_close($stmt);
        } else {
            $data = array("status" => "error", "message" => "Error al preparar la consulta: " . mysqli_error($con));
        }
    } else {
        $data = array("status" => "error", "message" => "Error en la conexión a la BD");
    }

    mysqli_close($con);
    return $data; 
}

function registrarComentarioLike($idComentario, $idEstudiante){
    include("../../configuracion/conexion.php"); 
    $response = array("status" => "error", "message" => "Error desconocido");

    // 1. Verificar si ya existe (Usando Sentencias Preparadas)
    $sql_check = "SELECT id_interaccion, est_interaccion 
                  FROM interaccion 
                  WHERE id_comentario = ? AND id_estudiante = ? AND id_tipo_interaccion = 1";
    
    $stmt_check = mysqli_prepare($con, $sql_check);
    mysqli_stmt_bind_param($stmt_check, "ii", $idComentario, $idEstudiante);
    mysqli_stmt_execute($stmt_check);
    $res = mysqli_stmt_get_result($stmt_check);

    $is_liked = false;
    
    if(mysqli_num_rows($res) > 0){
        $row = mysqli_fetch_assoc($res);
        $id_interaccion = $row['id_interaccion'];
        $es_activo_actual = $row['est_interaccion'];

        if($es_activo_actual == 1){
            // 2a. UNLIKE
            $sql_update = "UPDATE interaccion SET est_interaccion = 0, fch_interaccion = NOW() WHERE id_interaccion = ?";
            $stmt_update = mysqli_prepare($con, $sql_update);
            mysqli_stmt_bind_param($stmt_update, "i", $id_interaccion);
            if(mysqli_stmt_execute($stmt_update)){
                $response = array("status" => "unliked");
            } else {
                $response = array("status" => "error", "message" => mysqli_error($con));
            }
            mysqli_stmt_close($stmt_update); 
        } else {
            // 2b. REACTIVACIÓN (LIKED)
            $sql_update = "UPDATE interaccion SET est_interaccion = 1, fch_interaccion = NOW() WHERE id_interaccion = ?";
            $stmt_update = mysqli_prepare($con, $sql_update);
            mysqli_stmt_bind_param($stmt_update, "i", $id_interaccion);
                           
            if(mysqli_stmt_execute($stmt_update)){
                $response = array("status" => "liked");
                $is_liked = true; 
            } else {
                $response = array("status" => "error", "message" => mysqli_error($con));
            }
            mysqli_stmt_close($stmt_update); 
        }
    } else {
        // 3. NEW LIKE
        $sql_insert = "INSERT INTO interaccion (id_estudiante, id_comentario, id_tipo_interaccion, est_interaccion, fch_interaccion) 
                       VALUES (?, ?, ?, ?, NOW())"; 
        
        $stmt_insert = mysqli_prepare($con, $sql_insert);
        $tipo_interaccion = 1;
        $estado = 1;
        mysqli_stmt_bind_param($stmt_insert, "iiii", $idEstudiante, $idComentario, $tipo_interaccion, $estado);
                       
        if(mysqli_stmt_execute($stmt_insert)){
            $response = array("status" => "liked");
            $is_liked = true; 
        } else {
            $response = array("status" => "error", "message" => mysqli_error($con));
        }
        mysqli_stmt_close($stmt_insert); 
    }
    
    mysqli_stmt_close($stmt_check); 

    // --- 🚨 LÓGICA DE NOTIFICACIÓN ACTUALIZADA (DB y FCM) 🚨 ---
    if ($is_liked) {
        $datos_notificacion = obtenerDatosNotificacionComentario($con, $idComentario, $idEstudiante, false); 
        
        if ($datos_notificacion) { 
            $id_receptor = $datos_notificacion['id_receptor_final']; 

            if ($id_receptor != $idEstudiante) { 
            
                // Verificamos si el receptor (dueño del comentario) quiere recibir notificaciones de LIKES
                if ($datos_notificacion['notif_likes'] == 1) {
            
                    $token_destino = $datos_notificacion['token_dueno'];
                    $nombre_interactor = $datos_notificacion['nombre_interactor'];
                    $idPublicacion = $datos_notificacion['id_publicacion'];
                    
                    $titulo_fcm = "¡Reacción en Comentario! 👍";
                    $cuerpo_fcm = $nombre_interactor . " le ha dado Me gusta a tu comentario.";
                    $id_tipo_notif = 1; // 1 = 'likeComentario'

                    // 1. REGISTRAR EN LA TABLA NOTIFICACION
                    $rpta_db = registrarNotificacionDB($con, $id_receptor, $idEstudiante, $id_tipo_notif, $titulo_fcm, $cuerpo_fcm);
                    $response['notificacion_db'] = $rpta_db;

                    // 2. ENVIAR NOTIFICACIÓN PUSH FCM
                    $payload = array(
                        "action" => "NEW_LIKE_COMMENT", 
                        "id_publicacion" => $idPublicacion,
                        "id_emprendimiento" => $datos_notificacion['id_emprendimiento'] ?? null
                    );
                    $rpta_fcm = enviarNotificacionFCM($token_destino, $titulo_fcm, $cuerpo_fcm, $payload);
                    $response['fcm_result'] = $rpta_fcm;
                }
            }
        }
    }
    // --- FIN LÓGICA NOTIFICACIÓN ---
    
    mysqli_close($con);
    return $response;
}

function eliminarComentario($idComentario, $idEstudiante) {
    require_once("../../configuracion/conexion.php");
    $data = array("status" => "error", "message" => "No se pudo eliminar el comentario");

    if ($con) {
        $sql = "UPDATE comentario 
                SET est_comentario = 0 
                WHERE id_comentario = ? AND id_estudiante = ?";

        if ($stmt = mysqli_prepare($con, $sql)) {
            mysqli_stmt_bind_param($stmt, "ii", $idComentario, $idEstudiante);

            if (mysqli_stmt_execute($stmt)) {
                if (mysqli_stmt_affected_rows($stmt) > 0) {
                    $data = array(
                        "status" => "success",
                        "message" => "Comentario eliminado correctamente"
                    );
                } else {
                    $data = array(
                        "status" => "error",
                        "message" => "No tienes permiso para eliminar este comentario"
                    );
                }
            }
            mysqli_stmt_close($stmt);
        }
    }
    mysqli_close($con);
    return $data;
}
?>