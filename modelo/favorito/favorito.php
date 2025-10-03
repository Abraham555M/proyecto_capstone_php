<?php 
   function registrarFavorito($idPublicacion, $idEstudiante){
        include("../../configuracion/conexion.php"); 

        // Verificar si ya existe un registro
        $sql_check = "SELECT id_favorito, est_favorito 
                    FROM favorito 
                    WHERE id_publicacion = '$idPublicacion' 
                    AND id_estudiante = '$idEstudiante'";
        $res = mysqli_query($con, $sql_check);

        if(mysqli_num_rows($res) > 0){
            $row = mysqli_fetch_assoc($res);

            if($row['est_favorito'] == 1){
                // Si ya estaba como favorito, desactivarlo
                $sql_update = "UPDATE favorito 
                            SET est_favorito = 0, fch_guardado = NOW() 
                            WHERE id_favorito = '".$row['id_favorito']."'";
                if(mysqli_query($con, $sql_update)){
                    return array("status" => "unfavorited");
                } else {
                    return array("status" => "error", "message" => mysqli_error($con));
                }
            } else {
                // Si estaba inactivo, volver a activarlo
                $sql_update = "UPDATE favorito 
                            SET est_favorito = 1, fch_guardado = NOW() 
                            WHERE id_favorito = '".$row['id_favorito']."'";
                if(mysqli_query($con, $sql_update)){
                    return array("status" => "favorited");
                } else {
                    return array("status" => "error", "message" => mysqli_error($con));
                }
            }
        } else {
            // Si no existe, insertar nuevo registro
            $sql_insert = "INSERT INTO favorito (id_estudiante, id_publicacion, fch_guardado, est_favorito) 
                        VALUES ('$idEstudiante', '$idPublicacion', NOW(), 1)";
            if(mysqli_query($con, $sql_insert)){
                return array("status" => "favorited");
            } else {
                return array("status" => "error", "message" => mysqli_error($con));
            }
        }
    }


?>