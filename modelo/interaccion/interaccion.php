<?php 
   function registrarLike($idPublicacion, $idEstudiante){
        include("../../configuracion/conexion.php"); // aquí tienes $con

        // Verificar si ya existe el like (para hacer toggle)
        $sql_check = "SELECT id_interaccion FROM interaccion 
                    WHERE id_publicacion = '$idPublicacion' 
                    AND id_estudiante = '$idEstudiante' 
                    AND id_tipo_interaccion = 1";
        $res = mysqli_query($con, $sql_check);

        if(mysqli_num_rows($res) > 0){
            // Si ya existe, eliminar el like
            $sql_delete = "DELETE FROM interaccion 
                        WHERE id_publicacion = '$idPublicacion' 
                        AND id_estudiante = '$idEstudiante' 
                        AND id_tipo_interaccion = 1";
            if(mysqli_query($con, $sql_delete)){
                return array("status" => "unliked");
            } else {
                return array("status" => "error", "message" => mysqli_error($con));
            }
        } else {
            // Insertar el nuevo like
            $sql_insert = "INSERT INTO interaccion (id_estudiante, id_publicacion, id_tipo_interaccion) 
                        VALUES ('$idEstudiante', '$idPublicacion', 1)";
            if(mysqli_query($con, $sql_insert)){
                return array("status" => "liked");
            } else {
                return array("status" => "error", "message" => mysqli_error($con));
            }
        }
   }
?>