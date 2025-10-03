<?php 
    function registrarColaboracion($idEstudiante, $idPublicacion, $idEmprendimiento, $menSolicitud){
        include("../../configuracion/conexion.php"); // conexión en $con
        
        $respuesta = array();

        try {
            // preparar consulta con est_solicitud = 0 (pendiente)
            $sql = "INSERT INTO solicitud 
                        (id_estudiante, id_emprendimiento, id_publicacion, men_solicitud, fch_solicitud, est_solicitud) 
                    VALUES (?, ?, ?, ?, NOW(), 1)";

            if ($stmt = mysqli_prepare($con, $sql)) {
                mysqli_stmt_bind_param($stmt, "iiis", $idEstudiante, $idEmprendimiento, $idPublicacion, $menSolicitud);

                if (mysqli_stmt_execute($stmt)) {
                    $respuesta["status"] = "success";
                    $respuesta["message"] = "Solicitud registrada correctamente";
                    $respuesta["id_solicitud"] = mysqli_insert_id($con);
                    $respuesta["est_solicitud"] = 1; // siempre pendiente al inicio
                } else {
                    $respuesta["status"] = "error";
                    $respuesta["message"] = "Error al registrar: " . mysqli_error($con);
                }

                mysqli_stmt_close($stmt);
            } else {
                $respuesta["status"] = "error";
                $respuesta["message"] = "Error en la preparación de la consulta: " . mysqli_error($con);
            }

            mysqli_close($con);
        } catch (Exception $e) {
            $respuesta["status"] = "error";
            $respuesta["message"] = "Excepción: " . $e->getMessage();
        }

        return $respuesta;
    }
?>


