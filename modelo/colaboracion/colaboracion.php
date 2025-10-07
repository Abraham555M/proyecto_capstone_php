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

    function listarColaboracionDelPerfil($idEmprendedor){
        include("../../configuracion/conexion.php");

        $sql = "SELECT 
                    c.id_colaboracion,
                    c.men_colaboracion,
                    c.fch_colaboracion,
                    c.est_colaboracion,
                    
                    colab.id_estudiante AS id_colaborador,
                    colab.nom_estudiante,
                    colab.ape_pat_estudiante,
                    colab.ape_mat_estudiante,
                    colab.ema_estudiante,
                    
                    e.id_emprendimiento,
                    e.nom_emprendimiento,
                    
                    p.id_publicacion,
                    p.tit_publicacion
                FROM colaboracion c
                INNER JOIN estudiante colab 
                    ON c.id_estudiante = colab.id_estudiante
                INNER JOIN emprendimiento e 
                    ON c.id_emprendimiento = e.id_emprendimiento
                INNER JOIN publicacion p 
                    ON c.id_publicacion = p.id_publicacion
                WHERE e.id_estudiante = ?
                    AND c.est_colaboracion = 1";

        $stmt = $con->prepare($sql);
        $stmt->bind_param("i", $idEmprendedor);
        $stmt->execute();
        $result = $stmt->get_result();

        $colaboraciones = [];
        while($row = $result->fetch_assoc()){

            // Traducimos el estado numérico a texto
            $estadoTexto = match($row['est_colaboracion']) {
                0 => "Pendiente",
                1 => "Aceptado",
                2 => "Rechazado",
                default => "Desconocido"
            };

            $colaboraciones[] = [
                "id_colaboracion" => $row["id_colaboracion"],
                "men_colaboracion" => $row["men_colaboracion"],
                "fch_colaboracion" => $row["fch_colaboracion"],
                "est_colaboracion" => $row["est_colaboracion"],
                "estado_texto" => $estadoTexto,

                "id_colaborador" => $row["id_colaborador"],
                "nom_estudiante" => $row["nom_estudiante"],
                "ape_pat_estudiante" => $row["ape_pat_estudiante"],
                "ape_mat_estudiante" => $row["ape_mat_estudiante"],
                "ema_estudiante" => $row["ema_estudiante"],

                "id_emprendimiento" => $row["id_emprendimiento"],
                "nom_emprendimiento" => $row["nom_emprendimiento"],
                "id_publicacion" => $row["id_publicacion"],
                "tit_publicacion" => $row["tit_publicacion"]
            ];
        }

        $stmt->close();
        $con->close();

        return $colaboraciones;
    }




?>
