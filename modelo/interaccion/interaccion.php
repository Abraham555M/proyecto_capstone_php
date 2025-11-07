<?php 
// RUTA: modelo/interaccion/interaccion.php

/*
 * ----------------------------------------------------------------------
 * INCLUSIONES GLOBALES
 * ----------------------------------------------------------------------
 * 1. Incluimos el nuevo modelo de notificación (que contiene las funciones auxiliares).
 */
require_once(__DIR__ . '/../notificacion/notificacion.php'); 


// =========================================================================
// FUNCIÓN PRINCIPAL: Registrar Like de Publicación (Segura y Completa)
// =========================================================================
function registrarLike($idPublicacion, $idEstudiante){ // $idEstudiante es el INTERACTOR
    // Incluimos la conexión local para esta función
    include("../../configuracion/conexion.php"); 
    
    $response = array("status" => "error", "message" => "Error desconocido");
    $is_liked = false; // Flag para controlar la notificación

    // 1. Verificar si ya existe el like (Usando Sentencias Preparadas)
    $sql_check = "SELECT id_interaccion, est_interaccion 
                  FROM interaccion 
                  WHERE id_publicacion = ? 
                  AND id_estudiante = ? 
                  AND id_tipo_interaccion = 1";
    
    $stmt_check = mysqli_prepare($con, $sql_check);
    mysqli_stmt_bind_param($stmt_check, "ii", $idPublicacion, $idEstudiante);
    mysqli_stmt_execute($stmt_check);
    $res = mysqli_stmt_get_result($stmt_check);

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
                $is_liked = true; // Activar lógica de notificación
            } else {
                $response = array("status" => "error", "message" => mysqli_error($con));
            }
            mysqli_stmt_close($stmt_update);
        }
        
    } else {
        // 3. NEW LIKE
        $sql_insert = "INSERT INTO interaccion (id_estudiante, id_publicacion, id_tipo_interaccion, est_interaccion, fch_interaccion) 
                       VALUES (?, ?, 1, 1, NOW())";
        
        $stmt_insert = mysqli_prepare($con, $sql_insert);
        mysqli_stmt_bind_param($stmt_insert, "ii", $idEstudiante, $idPublicacion);
                       
        if(mysqli_stmt_execute($stmt_insert)){
            $response = array("status" => "liked");
            $is_liked = true; // Activar lógica de notificación
        } else {
            $response = array("status" => "error", "message" => mysqli_error($con));
        }
        mysqli_stmt_close($stmt_insert);
    }
    
    mysqli_stmt_close($stmt_check); // Cerrar el statement de verificación

    // --- 🚨 LÓGICA DE NOTIFICACIÓN ACTUALIZADA (DB y FCM) 🚨 ---
    if ($is_liked) {
        $datos_notificacion = obtenerDatosNotificacionPublicacion($con, $idPublicacion, $idEstudiante); 
        
        if ($datos_notificacion && $datos_notificacion['id_dueno'] != $idEstudiante) { 
            
            // Verificamos si el receptor (id_dueno) quiere recibir notificaciones de LIKES
            if ($datos_notificacion['notif_likes'] == 1) {
            
                $id_receptor = $datos_notificacion['id_dueno'];
                $token_destino = $datos_notificacion['token_dueno'];
                $nombre_interactor = $datos_notificacion['nombre_interactor'];
                
                $titulo_fcm = "¡Nueva Reacción! 👍";
                $cuerpo_fcm = $nombre_interactor . " le ha dado Me gusta a tu publicación.";
                $id_tipo_notif = 2; // 2 = 'likePublicacion'

                // 1. REGISTRAR EN LA TABLA NOTIFICACION (Llamada actualizada)
                $rpta_db = registrarNotificacionDB($con, $id_receptor, $idEstudiante, $id_tipo_notif, $titulo_fcm, $cuerpo_fcm);
                $response['notificacion_db'] = $rpta_db;

                // 2. ENVIAR NOTIFICACIÓN PUSH FCM
                $payload = array(
                    "action" => "NEW_LIKE_POST", 
                    "id_publicacion" => $idPublicacion,
                    "id_emprendimiento" => $datos_notificacion['id_emprendimiento'] ?? null 
                );
                $rpta_fcm = enviarNotificacionFCM($token_destino, $titulo_fcm, $cuerpo_fcm, $payload);
                $response['fcm_result'] = $rpta_fcm;
            }
        }
    }
    // --- FIN LÓGICA NOTIFICACIÓN ---
    
    mysqli_close($con);
    return $response;
}
?>