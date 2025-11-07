<?php 
// RUTA: modelo/interaccion/interaccion.php

require_once("../../configuracion/FCMService.php"); 

// ----------------------------------------------------------------------
// FUNCIÓN AUXILIAR: Obtiene el token del dueño de la Publicación y el nombre del interactor
// ----------------------------------------------------------------------
function obtenerDatosNotificacionPublicacion($con, $idPublicacion, $idEstudianteInteractor) {
    // Se asume que el token está en estudiante.token_fcm
    $sql = "SELECT 
                e_dueno.token_fcm AS token_dueno,
                CONCAT(e_interactor.nom_estudiante, ' ', e_interactor.ape_pat_estudiante) AS nombre_interactor,
                e_dueno.id_estudiante AS id_dueno
            FROM 
                publicacion p
            JOIN 
                emprendimiento em ON p.id_emprendimiento = em.id_emprendimiento
            JOIN 
                estudiante e_dueno ON em.id_estudiante = e_dueno.id_estudiante
            JOIN 
                estudiante e_interactor ON e_interactor.id_estudiante = ?
            WHERE 
                p.id_publicacion = ?";

    $data = null;
    if ($stmt = mysqli_prepare($con, $sql)) {
        mysqli_stmt_bind_param($stmt, "ii", $idEstudianteInteractor, $idPublicacion);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $data = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
    }
    return $data;
}

// ----------------------------------------------------------------------
// FUNCIÓN PRINCIPAL: Registrar Like de Publicación (Con Lógica FCM)
// ----------------------------------------------------------------------
function registrarLike($idPublicacion, $idEstudiante){
    include("../../configuracion/conexion.php"); 
    
    $response = array("status" => "error", "message" => "Error desconocido");

    // 1. Verificar si ya existe el like
    $sql_check = "SELECT id_interaccion, est_interaccion 
                  FROM interaccion 
                  WHERE id_publicacion = '$idPublicacion' 
                  AND id_estudiante = '$idEstudiante' 
                  AND id_tipo_interaccion = 1";
    $res = mysqli_query($con, $sql_check);

    if(mysqli_num_rows($res) > 0){
        $row = mysqli_fetch_assoc($res);
        $id_interaccion = $row['id_interaccion'];
        $es_activo_actual = $row['est_interaccion'];

        if($es_activo_actual == 1){
            // 2a. UNLIKE - NO se notifica
            $sql_update = "UPDATE interaccion 
                           SET est_interaccion = 0, fch_interaccion = NOW() 
                           WHERE id_interaccion = '$id_interaccion'";
            if(mysqli_query($con, $sql_update)){
                $response = array("status" => "unliked");
            } else {
                $response = array("status" => "error", "message" => mysqli_error($con));
            }
        } else {
            // 2b. REACTIVACIÓN (LIKED) - SÍ se notifica
            $sql_update = "UPDATE interaccion 
                           SET est_interaccion = 1, fch_interaccion = NOW()
                           WHERE id_interaccion = '$id_interaccion'";
                           
            if(mysqli_query($con, $sql_update)){
                $response = array("status" => "liked");
                
                // --- LÓGICA FCM ---
                $datos_notificacion = obtenerDatosNotificacionPublicacion($con, $idPublicacion, $idEstudiante);
                
                if ($datos_notificacion && $datos_notificacion['id_dueno'] != $idEstudiante) { 
                    $token_destino = $datos_notificacion['token_dueno'];
                    $nombre_interactor = $datos_notificacion['nombre_interactor'];
                    
                    $titulo_fcm = "¡Nueva Reacción! 👍";
                    $cuerpo_fcm = $nombre_interactor . " le ha dado Me gusta a tu publicación.";
                    $payload = array("action" => "NEW_LIKE_POST", "id_publicacion" => $idPublicacion);
                    
                    $rpta_fcm = enviarNotificacionFCM($token_destino, $titulo_fcm, $cuerpo_fcm, $payload);
                    $response['fcm_result'] = $rpta_fcm; 
                }
            } else {
                $response = array("status" => "error", "message" => mysqli_error($con));
            }
        }
    } else {
        // 3. NEW LIKE - SÍ se notifica
        $sql_insert = "INSERT INTO interaccion (id_estudiante, id_publicacion, id_tipo_interaccion, est_interaccion, fch_interaccion) 
                       VALUES ('$idEstudiante', '$idPublicacion', 1, 1, NOW())";
                       
        if(mysqli_query($con, $sql_insert)){
            $response = array("status" => "liked");
            
            // --- LÓGICA FCM ---
            $datos_notificacion = obtenerDatosNotificacionPublicacion($con, $idPublicacion, $idEstudiante);
            
            if ($datos_notificacion && $datos_notificacion['id_dueno'] != $idEstudiante) { 
                $token_destino = $datos_notificacion['token_dueno'];
                $nombre_interactor = $datos_notificacion['nombre_interactor'];
                
                $titulo_fcm = "¡Nueva Reacción! 👍";
                $cuerpo_fcm = $nombre_interactor . " le ha dado Me gusta a tu publicación.";
                $payload = array("action" => "NEW_LIKE_POST", "id_publicacion" => $idPublicacion);
                
                $rpta_fcm = enviarNotificacionFCM($token_destino, $titulo_fcm, $cuerpo_fcm, $payload);
                $response['fcm_result'] = $rpta_fcm;
            }
        } else {
            $response = array("status" => "error", "message" => mysqli_error($con));
        }
    }
    
    mysqli_close($con);
    return $response;
}