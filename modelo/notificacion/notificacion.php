<?php
// RUTA: modelo/notificacion/notificacion.php

// Dependencias que necesita este modelo
require_once(__DIR__ . '/../../configuracion/FCMService.php'); 
require_once(__DIR__ . '/../estudiante/estudiante.php'); 

/**
 * 🚨 ACTUALIZADO: Guarda la notificación en la DB.
 * Ahora acepta el ID del interactor y el tipo de notificación.
 */
function registrarNotificacionDB($con, $id_receptor, $id_interactor, $id_tipo_notif, $titulo, $mensaje) {
    // Nuevas columnas: id_estudiante_emprendedor (interactor), id_tipo_notificacion
    $sql = "INSERT INTO notificacion (id_estudiante, id_estudiante_emprendedor, id_tipo_notificacion, tit_notificacion, men_notificacion) 
            VALUES (?, ?, ?, ?, ?)";

    if ($stmt = mysqli_prepare($con, $sql)) {
        // iiiss = int (receptor), int (interactor), int (tipo), string (titulo), string (mensaje)
        mysqli_stmt_bind_param($stmt, "iiiss", $id_receptor, $id_interactor, $id_tipo_notif, $titulo, $mensaje);
        
        if (mysqli_stmt_execute($stmt)) {
            $id_insertado = mysqli_insert_id($con);
            mysqli_stmt_close($stmt);
            return array("status" => "db_notif_success", "id_notificacion" => $id_insertado);
        } else {
            // Loguear el error real
            error_log("Error DB al insertar notificación: " . mysqli_error($con));
            mysqli_stmt_close($stmt);
            return array("status" => "db_notif_error", "message" => "Fallo al registrar notificación en DB.");
        }
    }
    return array("status" => "db_notif_error", "message" => "Fallo al preparar consulta de notificación.");
}

/**
 * Obtiene datos para notificar sobre una PUBLICACIÓN.
 * (Usado para Likes a Publicación y Comentarios a Publicación)
 */
function obtenerDatosNotificacionPublicacion($con, $idPublicacion, $idEstudianteInteractor) {
    $sql = "SELECT 
                e_dueno.token_fcm AS token_dueno,
                e_dueno.id_estudiante AS id_dueno,
                e_dueno.notif_publicaciones, 
                e_dueno.notif_comentarios, 
                e_dueno.notif_likes,
                CONCAT(e_interactor.nom_estudiante, ' ', e_interactor.ape_pat_estudiante) AS nombre_interactor,
                p.id_emprendimiento 
            FROM publicacion p
            JOIN emprendimiento em ON p.id_emprendimiento = em.id_emprendimiento
            JOIN estudiante e_dueno ON em.id_estudiante = e_dueno.id_estudiante
            JOIN estudiante e_interactor ON e_interactor.id_estudiante = ?
            WHERE p.id_publicacion = ?";

    $data = null;
    if ($stmt = mysqli_prepare($con, $sql)) {
        mysqli_stmt_bind_param($stmt, "ii", $idEstudianteInteractor, $idPublicacion);
        mysqli_stmt_execute($stmt);
        $data = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);
    }
    return $data;
}

/**
 * Obtiene datos para notificar sobre un COMENTARIO.
 * (Usado para Likes a Comentario y Respuestas a Comentario)
 */
function obtenerDatosNotificacionComentario($con, $idComentario, $idEstudianteInteractor, $esComentario = false) {
    // 1. Obtener datos del comentario y post
    $sqlPub = "SELECT 
                    c.id_publicacion, 
                    p.id_emprendimiento,
                    c.id_estudiante AS id_dueno_comentario,
                    em.id_estudiante AS id_dueno_post
                FROM comentario c
                JOIN publicacion p ON c.id_publicacion = p.id_publicacion
                JOIN emprendimiento em ON p.id_emprendimiento = em.id_emprendimiento
                WHERE c.id_comentario = ?";
    
    $stmtPub = mysqli_prepare($con, $sqlPub);
    mysqli_stmt_bind_param($stmtPub, "i", $idComentario);
    mysqli_stmt_execute($stmtPub);
    $resultPub = mysqli_stmt_get_result($stmtPub);
    $rowPub = mysqli_fetch_assoc($resultPub);
    mysqli_stmt_close($stmtPub);

    // 2. Determinar el RECEPTOR
    $idReceptorFinal = $esComentario ? $rowPub['id_dueno_post'] : $rowPub['id_dueno_comentario'];

    // 3. Obtener token del receptor y nombre del interactor
    $sql = "SELECT 
                e_receptor.token_fcm AS token_dueno,
                e_receptor.notif_publicaciones, 
                e_receptor.notif_comentarios, 
                e_receptor.notif_likes,
                CONCAT(e_interactor.nom_estudiante, ' ', e_interactor.ape_pat_estudiante) AS nombre_interactor
            FROM estudiante e_receptor 
            JOIN estudiante e_interactor ON e_interactor.id_estudiante = ?
            WHERE e_receptor.id_estudiante = ?";

    $data = null;
    if ($stmt = mysqli_prepare($con, $sql)) {
        mysqli_stmt_bind_param($stmt, "ii", $idEstudianteInteractor, $idReceptorFinal);
        mysqli_stmt_execute($stmt);
        $data = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);
    }
    
    $data['id_receptor_final'] = $idReceptorFinal; 
    $data['id_publicacion'] = $rowPub['id_publicacion'];
    $data['id_emprendimiento'] = $rowPub['id_emprendimiento'];

    return $data;
}

?>