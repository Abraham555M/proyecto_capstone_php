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


    function listarPublicacionesFavoritos($idEstudiante) {
        require_once("../../configuracion/conexion.php");

        $idEstudiante = intval($idEstudiante);

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
                            AND i2.est_interaccion = 1
                        ) THEN 1 ELSE 0
                    END AS dio_like,

                    -- ¿Ya sigue este estudiante al emprendimiento?
                    CASE 
                        WHEN EXISTS (
                            SELECT 1
                            FROM seguimiento s
                            WHERE s.id_emprendimiento = e.id_emprendimiento
                            AND s.id_estudiante = $idEstudiante
                            AND s.est_seguimiento = 1
                        ) THEN 1 ELSE 0
                    END AS siguiendo,

                    -- Ya que estamos listando favoritos, siempre será 1
                    1 AS es_favorito

                FROM favorito f
                INNER JOIN publicacion p ON f.id_publicacion = p.id_publicacion
                INNER JOIN emprendimiento e ON p.id_emprendimiento = e.id_emprendimiento
                LEFT JOIN interaccion i 
                    ON i.id_publicacion = p.id_publicacion 
                    AND i.id_tipo_interaccion = 1
                    AND i.est_interaccion = 1
                WHERE f.id_estudiante = $idEstudiante
                AND f.est_favorito = 1
                AND p.est_publicacion = 1
                GROUP BY 
                    p.id_publicacion,
                    e.id_emprendimiento,
                    e.nom_emprendimiento,
                    e.img_per_emprendimiento,
                    p.tit_publicacion,
                    p.con_publicacion,
                    p.img_publicacion
                ORDER BY f.fch_guardado DESC";

        $result = mysqli_query($con, $sql);

        $data = [];
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $data[] = $row;
            }
        } else {
            error_log("Error SQL Favoritos: " . mysqli_error($con));
        }

        return $data;
    }

    function filtrarFavoritosPorCategoria($idEstudiante, $idCategoria) {
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

                CASE 
                    WHEN EXISTS (
                        SELECT 1 FROM interaccion i2
                        WHERE i2.id_publicacion = p.id_publicacion
                        AND i2.id_estudiante = $idEstudiante
                        AND i2.id_tipo_interaccion = 1
                        AND i2.est_interaccion = 1
                    ) THEN 1 ELSE 0 END AS dio_like,

                CASE 
                    WHEN EXISTS (
                        SELECT 1 FROM seguimiento s
                        WHERE s.id_emprendimiento = e.id_emprendimiento
                        AND s.id_estudiante = $idEstudiante
                        AND s.est_seguimiento = 1
                    ) THEN 1 ELSE 0 END AS siguiendo,

                1 AS es_favorito

            FROM favorito f
            INNER JOIN publicacion p ON f.id_publicacion = p.id_publicacion
            INNER JOIN emprendimiento e ON p.id_emprendimiento = e.id_emprendimiento
            LEFT JOIN interaccion i ON i.id_publicacion = p.id_publicacion 
                AND i.id_tipo_interaccion = 1
                AND i.est_interaccion = 1
            WHERE f.id_estudiante = $idEstudiante
              AND f.est_favorito = 1
              AND p.est_publicacion = 1
              AND e.id_categoria = $idCategoria
            GROUP BY p.id_publicacion
            ORDER BY f.fch_guardado DESC";

    $result = mysqli_query($con, $sql);
    $data = [];
    if ($result) while ($row = mysqli_fetch_assoc($result)) $data[] = $row;
    return $data;
}


function buscarFavoritosPublicacion($idEstudiante, $texto) {
    require_once("../../configuracion/conexion.php");
    $texto = mysqli_real_escape_string($con, $texto);

    $sql = "SELECT 
                p.id_publicacion,
                e.id_emprendimiento,
                e.nom_emprendimiento,
                e.img_per_emprendimiento,
                p.tit_publicacion,
                p.con_publicacion,
                p.img_publicacion,
                COUNT(i.id_interaccion) AS total_me_gusta,

                CASE 
                    WHEN EXISTS (
                        SELECT 1 FROM interaccion i2
                        WHERE i2.id_publicacion = p.id_publicacion
                        AND i2.id_estudiante = $idEstudiante
                        AND i2.id_tipo_interaccion = 1
                        AND i2.est_interaccion = 1
                    ) THEN 1 ELSE 0 END AS dio_like,

                CASE 
                    WHEN EXISTS (
                        SELECT 1 FROM seguimiento s
                        WHERE s.id_emprendimiento = e.id_emprendimiento
                        AND s.id_estudiante = $idEstudiante
                        AND s.est_seguimiento = 1
                    ) THEN 1 ELSE 0 END AS siguiendo,

                1 AS es_favorito

            FROM favorito f
            INNER JOIN publicacion p ON f.id_publicacion = p.id_publicacion
            INNER JOIN emprendimiento e ON p.id_emprendimiento = e.id_emprendimiento
            LEFT JOIN interaccion i ON i.id_publicacion = p.id_publicacion 
                AND i.id_tipo_interaccion = 1
                AND i.est_interaccion = 1
            WHERE f.id_estudiante = $idEstudiante
              AND f.est_favorito = 1
              AND p.est_publicacion = 1
              AND (p.tit_publicacion LIKE '%$texto%' OR e.nom_emprendimiento LIKE '%$texto%')
            GROUP BY p.id_publicacion
            ORDER BY f.fch_guardado DESC";

    $result = mysqli_query($con, $sql);
    $data = [];
    if ($result) while ($row = mysqli_fetch_assoc($result)) $data[] = $row;
    return $data;
}
function buscarFavoritosConCategoria($idEstudiante, $texto, $idCategoria = null) {
    require_once("../../configuracion/conexion.php");
    
    $texto = mysqli_real_escape_string($con, $texto);
    
    $sql = "SELECT 
                p.id_publicacion,
                e.id_emprendimiento,
                e.nom_emprendimiento,
                e.img_per_emprendimiento,
                p.tit_publicacion,
                p.con_publicacion,
                p.img_publicacion,
                COUNT(i.id_interaccion) AS total_me_gusta,
                
                CASE 
                    WHEN EXISTS (
                        SELECT 1 FROM interaccion i2
                        WHERE i2.id_publicacion = p.id_publicacion
                        AND i2.id_estudiante = $idEstudiante
                        AND i2.id_tipo_interaccion = 1
                        AND i2.est_interaccion = 1
                    ) THEN 1 ELSE 0
                END AS dio_like,
                
                CASE 
                    WHEN EXISTS (
                        SELECT 1 FROM seguimiento s
                        WHERE s.id_emprendimiento = e.id_emprendimiento
                        AND s.id_estudiante = $idEstudiante
                        AND s.est_seguimiento = 1
                    ) THEN 1 ELSE 0
                END AS siguiendo,
                
                1 AS es_favorito
                
            FROM favorito f
            INNER JOIN publicacion p ON f.id_publicacion = p.id_publicacion
            INNER JOIN emprendimiento e ON p.id_emprendimiento = e.id_emprendimiento
            LEFT JOIN interaccion i ON i.id_publicacion = p.id_publicacion 
                AND i.id_tipo_interaccion = 1
                AND i.est_interaccion = 1
            WHERE f.id_estudiante = $idEstudiante
            AND f.est_favorito = 1
            AND p.est_publicacion = 1
            AND p.tit_publicacion LIKE '%$texto%'";
    
    // ⭐ AGREGAR FILTRO DE CATEGORÍA SI EXISTE
    if ($idCategoria !== null && $idCategoria > 0) {
        $sql .= " AND e.id_categoria = $idCategoria";
    }
    
    $sql .= " GROUP BY p.id_publicacion, e.id_emprendimiento, e.nom_emprendimiento,
              e.img_per_emprendimiento, p.tit_publicacion, p.con_publicacion, p.img_publicacion
              ORDER BY f.fch_guardado DESC";
    
    $result = mysqli_query($con, $sql);
    
    $data = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }
    }
    
    return $data;
}
?>