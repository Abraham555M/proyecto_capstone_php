<?php 
   function listarPublicacionInicio($idEstudiante){
        require_once("../../configuracion/conexion.php");
        
        $sql = "SELECT 
                    p.id_publicacion,
                    e.id_emprendimiento,
                    e.nom_emprendimiento,
                    e.img_per_emprendimiento,
                    p.tit_publicacion,
                    p.con_publicacion,
                    p.img_publicacion,
                    COUNT(i.id_interaccion) AS total_me_gusta,

                    -- ¿Ya le dio like este estudiante?
                    CASE 
                        WHEN EXISTS (
                            SELECT 1
                            FROM interaccion i2
                            WHERE i2.id_publicacion = p.id_publicacion
                            AND i2.id_estudiante = $idEstudiante
                            AND i2.id_tipo_interaccion = 1
                        ) THEN 1
                        ELSE 0
                    END AS dio_like,

                    -- ¿Ya sigue este estudiante al emprendimiento?
                    CASE 
                        WHEN EXISTS (
                            SELECT 1
                            FROM seguimiento s
                            WHERE s.id_emprendimiento = e.id_emprendimiento
                            AND s.id_estudiante = $idEstudiante
                            AND s.est_seguimiento = 1
                        ) THEN 1
                        ELSE 0
                    END AS siguiendo

                FROM publicacion p
                INNER JOIN emprendimiento e 
                    ON p.id_emprendimiento = e.id_emprendimiento
                LEFT JOIN interaccion i 
                    ON i.id_publicacion = p.id_publicacion 
                    AND i.id_tipo_interaccion = 1
                WHERE p.est_publicacion = 1
                GROUP BY 
                    p.id_publicacion,
                    e.id_emprendimiento,
                    e.nom_emprendimiento,
                    e.img_per_emprendimiento,
                    p.tit_publicacion,
                    p.con_publicacion,
                    p.img_publicacion
                ORDER BY p.fch_publicacion DESC";

        $result = mysqli_query($con, $sql);

        $data = [];
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $data[] = $row; 
            }
        }

        return $data; 
    }


    function listarPublicacionPerfil($idEstudiante, $idEmprendimiento){
        
    }

    function listarComentariosPublicacion($idPublicacion){
        require_once("../../configuracion/conexion.php"); // Aquí está $con
        
        $sql = "SELECT c.id_comentario,
                    c.con_comentario,
                    c.fch_comentario,
                    e.nom_estudiante,
                    e.ape_pat_estudiante,
                    e.ape_mat_estudiante
                FROM comentario c
                INNER JOIN estudiante e ON c.id_estudiante = e.id_estudiante
                WHERE c.id_publicacion = ?
                ORDER BY c.fch_comentario DESC";

        $comentarios = [];

        if ($stmt = mysqli_prepare($con, $sql)) {
            mysqli_stmt_bind_param($stmt, "i", $idPublicacion);
            mysqli_stmt_execute($stmt);
            $resultado = mysqli_stmt_get_result($stmt);

            while ($row = mysqli_fetch_assoc($resultado)) {
                $comentarios[] = [
                    "id_comentario"  => $row["id_comentario"],
                    "con_comentario" => $row["con_comentario"],
                    "fch_comentario" => $row["fch_comentario"],
                    "estudiante"     => $row["nom_estudiante"] . " " . $row["ape_pat_estudiante"] . " " . $row["ape_mat_estudiante"]
                ];
            }

            mysqli_stmt_close($stmt);
        }

        return $comentarios;
    }



?>