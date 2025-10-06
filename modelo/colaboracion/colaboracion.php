<?php 
function registrarColaboracion($idEstudiante, $idPublicacion, $idEmprendimiento, $menColaboracion) {
    include("../../configuracion/conexion.php"); // conexión en $con
    
    $respuesta = array();

    try {
        // Inserta una colaboración con estado 0 = Pendiente
        $sql = "INSERT INTO colaboracion 
                    (id_estudiante, id_emprendimiento, id_publicacion, men_colaboracion, fch_colaboracion, est_colaboracion) 
                VALUES (?, ?, ?, ?, NOW(), 0)";

        if ($stmt = mysqli_prepare($con, $sql)) {
            mysqli_stmt_bind_param($stmt, "iiis", $idEstudiante, $idEmprendimiento, $idPublicacion, $menColaboracion);

            if (mysqli_stmt_execute($stmt)) {
                $respuesta["status"] = "success";
                $respuesta["message"] = "Colaboración registrada correctamente";
                $respuesta["id_colaboracion"] = mysqli_insert_id($con);
                $respuesta["est_colaboracion"] = 0; // Pendiente
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
