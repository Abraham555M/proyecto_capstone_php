<?php 
   function registrarLike($idPublicacion, $idEstudiante){
        include("../../configuracion/conexion.php"); // aquí tienes $con

        // Verificar si ya existe el like
        $sql_check = "SELECT id_interaccion, est_interaccion 
                      FROM interaccion 
                      WHERE id_publicacion = '$idPublicacion' 
                      AND id_estudiante = '$idEstudiante' 
                      AND id_tipo_interaccion = 1";
        $res = mysqli_query($con, $sql_check);

        if(mysqli_num_rows($res) > 0){
            $row = mysqli_fetch_assoc($res);

            if($row['est_interaccion'] == 1){
                // Si ya estaba activo, desactivarlo
                $sql_update = "UPDATE interaccion 
                               SET est_interaccion = 0 
                               WHERE id_interaccion = '".$row['id_interaccion']."'";
                if(mysqli_query($con, $sql_update)){
                    return array("status" => "unliked");
                } else {
                    return array("status" => "error", "message" => mysqli_error($con));
                }
            } else {
                // Si estaba inactivo, volver a activarlo
                $sql_update = "UPDATE interaccion 
                               SET est_interaccion = 1 
                               WHERE id_interaccion = '".$row['id_interaccion']."'";
                if(mysqli_query($con, $sql_update)){
                    return array("status" => "liked");
                } else {
                    return array("status" => "error", "message" => mysqli_error($con));
                }
            }
        } else {
            // Si no existe, insertar un nuevo registro activo
            $sql_insert = "INSERT INTO interaccion (id_estudiante, id_publicacion, id_tipo_interaccion, est_interaccion) 
                           VALUES ('$idEstudiante', '$idPublicacion', 1, 1)";
            if(mysqli_query($con, $sql_insert)){
                return array("status" => "liked");
            } else {
                return array("status" => "error", "message" => mysqli_error($con));
            }
        }
   }
?>
