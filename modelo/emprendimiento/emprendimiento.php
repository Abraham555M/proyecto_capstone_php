<?php
function listarEmprendimientos($id_estudiante) {
    require_once("../../configuracion/conexion.php");

    $data = array(
        "status" => "error",
        "message" => "No se encontraron emprendimientos",
        "emprendimientos" => array()
    );

    if ($con) {
        // Consulta filtrando por id_estudiante
        $sql = "SELECT * FROM emprendimiento 
                WHERE id_estudiante = ?
                ORDER BY id_emprendimiento DESC";

        if ($stmt = mysqli_prepare($con, $sql)) {
            mysqli_stmt_bind_param($stmt, "i", $id_estudiante);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);

            if ($result && mysqli_num_rows($result) > 0) {
                $emprendimientos = array();
                while ($row = mysqli_fetch_assoc($result)) {
                    $emprendimientos[] = $row;
                }
                $data = array(
                    "status" => "success",
                    "message" => "Emprendimientos encontrados",
                    "emprendimientos" => $emprendimientos
                );
            } else {
                $data = array(
                    "status" => "success",
                    "message" => "No hay emprendimientos registrados para este estudiante",
                    "emprendimientos" => array()
                );
            }
            mysqli_stmt_close($stmt);
        } else {
            $data = array(
                "status" => "error",
                "message" => "Error al preparar la consulta",
                "emprendimientos" => array()
            );
        }
    } else {
        $data = array(
            "status" => "error",
            "message" => "Error en la conexión a la BD",
            "emprendimientos" => array()
        );
    }

    mysqli_close($con);
    return $data;
}
function actualizarEmprendimiento($id_emprendimiento, $nom_emprendimiento, $des_emprendimiento, $img_por_emprendimiento) {
    require_once("../../configuracion/conexion.php");

    $data = array(
        "status" => "error",
        "message" => "Error al actualizar el emprendimiento"
    );

    if ($con) {
        $sql = "UPDATE emprendimiento SET 
                nom_emprendimiento = ?, 
                des_emprendimiento = ?, 
                img_por_emprendimiento = ? 
                WHERE id_emprendimiento = ?";

        if ($stmt = mysqli_prepare($con, $sql)) {
            mysqli_stmt_bind_param($stmt, "sssi", $nom_emprendimiento, $des_emprendimiento, $img_por_emprendimiento, $id_emprendimiento);
            
            if (mysqli_stmt_execute($stmt)) {
                if (mysqli_stmt_affected_rows($stmt) > 0) {
                    $data = array(
                        "status" => "success",
                        "message" => "Emprendimiento actualizado correctamente"
                    );
                } else {
                    $data = array(
                        "status" => "warning",
                        "message" => "No se realizaron cambios (datos iguales)"
                    );
                }
            } else {
                $data = array(
                    "status" => "error",
                    "message" => "Error al ejecutar la actualización: " . mysqli_stmt_error($stmt)
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
    return $data;
}


function eliminarEmprendimiento($id_emprendimiento) {
    require_once("../../configuracion/conexion.php");

    $data = array(
        "status" => "error",
        "message" => "Error al eliminar el emprendimiento"
    );

    if ($con) {
        // Opcional: primero verificar si existe
        $sqlCheck = "SELECT id_emprendimiento FROM emprendimiento WHERE id_emprendimiento = ?";
        if ($stmtCheck = mysqli_prepare($con, $sqlCheck)) {
            mysqli_stmt_bind_param($stmtCheck, "i", $id_emprendimiento);
            mysqli_stmt_execute($stmtCheck);
            mysqli_stmt_store_result($stmtCheck);
            
            if (mysqli_stmt_num_rows($stmtCheck) == 0) {
                mysqli_stmt_close($stmtCheck);
                mysqli_close($con);
                return array(
                    "status" => "error",
                    "message" => "El emprendimiento no existe"
                );
            }
            mysqli_stmt_close($stmtCheck);
        }

        // Proceder a eliminar
        $sql = "DELETE FROM emprendimiento WHERE id_emprendimiento = ?";
        
        if ($stmt = mysqli_prepare($con, $sql)) {
            mysqli_stmt_bind_param($stmt, "i", $id_emprendimiento);
            
            if (mysqli_stmt_execute($stmt)) {
                if (mysqli_stmt_affected_rows($stmt) > 0) {
                    $data = array(
                        "status" => "success",
                        "message" => "Emprendimiento eliminado correctamente"
                    );
                } else {
                    $data = array(
                        "status" => "error",
                        "message" => "No se pudo eliminar el emprendimiento"
                    );
                }
            } else {
                $data = array(
                    "status" => "error",
                    "message" => "Error al ejecutar la eliminación: " . mysqli_stmt_error($stmt)
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
    return $data;
}

?>
