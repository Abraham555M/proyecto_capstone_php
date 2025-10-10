<?php 
   function registrarComentario($idPublicacion, $idEstudiante, $conComentario){
      require_once("../../configuracion/conexion.php");

      // Respuesta por defecto
      $data = array("status" => "error", "message" => "No se pudo registrar el comentario");

      if ($con) {
         // Insertamos con est_comentario en estado activo (1 por defecto)
         $sql = "INSERT INTO comentario (id_publicacion, id_estudiante, con_comentario, fch_comentario, est_comentario) 
                  VALUES (?, ?, ?, NOW(), ?)";

         if ($stmt = mysqli_prepare($con, $sql)) {
               $estado = 1; // estado activo
               mysqli_stmt_bind_param($stmt, "iisi", $idPublicacion, $idEstudiante, $conComentario, $estado);

               if (mysqli_stmt_execute($stmt)) {
                  $data = array(
                     "status" => "success",
                     "message" => "Comentario registrado correctamente",
                     "id_insertado" => mysqli_insert_id($con)
                  );
               } else {
                  $data = array(
                     "status" => "error",
                     "message" => "Error al ejecutar la consulta: " . mysqli_error($con)
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
      return $data; // Listo para json_encode
   }

    function registrarComentarioLike($idComentario, $idEstudiante){
        include("../../configuracion/conexion.php"); 

        // Verificar si ya existe un like en el comentario
        $sql_check = "SELECT id_interaccion, est_interaccion 
                      FROM interaccion 
                      WHERE id_comentario = '$idComentario' 
                      AND id_estudiante = '$idEstudiante' 
                      AND id_tipo_interaccion = 1";
        $res = mysqli_query($con, $sql_check);

        if(mysqli_num_rows($res) > 0){
            $row = mysqli_fetch_assoc($res);

            if($row['est_interaccion'] == 1){
                // Si ya estaba activo, desactivarlo
                $sql_update = "UPDATE interaccion 
                               SET est_interaccion = 0, fch_interaccion = NOW() 
                               WHERE id_interaccion = '".$row['id_interaccion']."'";
                if(mysqli_query($con, $sql_update)){
                    return array("status" => "unliked");
                } else {
                    return array("status" => "error", "message" => mysqli_error($con));
                }
            } else {
                // Si estaba inactivo, volver a activarlo
                $sql_update = "UPDATE interaccion 
                               SET est_interaccion = 1, fch_interaccion = NOW() 
                               WHERE id_interaccion = '".$row['id_interaccion']."'";
                if(mysqli_query($con, $sql_update)){
                    return array("status" => "liked");
                } else {
                    return array("status" => "error", "message" => mysqli_error($con));
                }
            }
        } else {
            // Si no existe, insertar nuevo registro con like activo
            $sql_insert = "INSERT INTO interaccion (id_estudiante, id_comentario, id_tipo_interaccion, est_interaccion, fch_interaccion) 
                           VALUES ('$idEstudiante', '$idComentario', 1, 1, NOW())";
            if(mysqli_query($con, $sql_insert)){
                return array("status" => "liked");
            } else {
                return array("status" => "error", "message" => mysqli_error($con));
            }
        }
   }
   
   function eliminarComentario($idComentario, $idEstudiante) {
    require_once("../../configuracion/conexion.php");

    $data = array("status" => "error", "message" => "No se pudo eliminar el comentario");

    if ($con) {
        // IMPORTANTE: Validar que el comentario pertenezca al estudiante
        $sql = "UPDATE comentario 
                SET est_comentario = 0 
                WHERE id_comentario = ? 
                AND id_estudiante = ?";

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