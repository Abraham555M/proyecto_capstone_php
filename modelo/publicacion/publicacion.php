<?php 
    function listarPublicacionInicio($idEstudiante){
        require_once("../../configuracion/conexion.php");
        
        $sql = "SELECT 
                    p.id_publicacion,
                    e.nom_emprendimiento,
                    e.img_per_emprendimiento,
                    p.tit_publicacion,
                    p.con_publicacion,
                    p.img_publicacion,
                    COUNT(i.id_interaccion) AS total_me_gusta,
                    -- Nuevo campo: ¿ya le dio like este estudiante?
                    CASE 
                        WHEN EXISTS (
                            SELECT 1
                            FROM interaccion i2
                            WHERE i2.id_publicacion = p.id_publicacion
                            AND i2.id_estudiante = $idEstudiante
                            AND i2.id_tipo_interaccion = 1
                        ) THEN 1
                        ELSE 0
                    END AS dio_like
                FROM publicacion p
                INNER JOIN emprendimiento e 
                    ON p.id_emprendimiento = e.id_emprendimiento
                LEFT JOIN interaccion i 
                    ON i.id_publicacion = p.id_publicacion 
                    AND i.id_tipo_interaccion = 1
                WHERE p.est_publicacion = 1
                GROUP BY 
                    p.id_publicacion,
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


?>