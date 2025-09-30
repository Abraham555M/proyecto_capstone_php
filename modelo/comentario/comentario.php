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

?>