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
                            AND i2.est_interaccion = 1
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
                    END AS siguiendo,

                    -- ¿Ya lo marcó como favorito?
                    CASE
                        WHEN EXISTS (
                            SELECT 1
                            FROM favorito f
                            WHERE f.id_publicacion = p.id_publicacion
                            AND f.id_estudiante = $idEstudiante
                            AND f.est_favorito = 1
                        ) THEN 1
                        ELSE 0
                    END AS es_favorito

                FROM publicacion p
                INNER JOIN emprendimiento e 
                    ON p.id_emprendimiento = e.id_emprendimiento
                LEFT JOIN interaccion i 
                    ON i.id_publicacion = p.id_publicacion 
                    AND i.id_tipo_interaccion = 1
                    AND i.est_interaccion = 1
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

   function listarComentariosPublicacion($idPublicacion, $idEstudiante){
        require_once("../../configuracion/conexion.php");
        
        $sql = "SELECT c.id_comentario,
                    c.con_comentario,
                    c.fch_comentario,
                    e.nom_estudiante,
                    e.ape_pat_estudiante,
                    e.ape_mat_estudiante,
                    COUNT(DISTINCT CASE WHEN i.est_interaccion = 1 THEN i.id_interaccion END) as total_likes,
                    MAX(CASE WHEN i.id_estudiante = ? AND i.id_tipo_interaccion = 1 AND i.est_interaccion = 1 THEN 1 ELSE 0 END) as dio_like
                FROM comentario c
                INNER JOIN estudiante e ON c.id_estudiante = e.id_estudiante
                LEFT JOIN interaccion i ON c.id_comentario = i.id_comentario AND i.id_tipo_interaccion = 1
                WHERE c.id_publicacion = ?
                GROUP BY c.id_comentario, c.con_comentario, c.fch_comentario, 
                        e.nom_estudiante, e.ape_pat_estudiante, e.ape_mat_estudiante
                ORDER BY c.fch_comentario DESC";

        $comentarios = [];

        if ($stmt = mysqli_prepare($con, $sql)) {
            mysqli_stmt_bind_param($stmt, "ii", $idEstudiante, $idPublicacion);
            mysqli_stmt_execute($stmt);
            $resultado = mysqli_stmt_get_result($stmt);

            while ($row = mysqli_fetch_assoc($resultado)) {
                $comentarios[] = [
                    "id_comentario"  => $row["id_comentario"],
                    "con_comentario" => $row["con_comentario"],
                    "fch_comentario" => $row["fch_comentario"],
                    "estudiante"     => $row["nom_estudiante"] . " " . $row["ape_pat_estudiante"] . " " . $row["ape_mat_estudiante"],
                    "total_likes"    => (int)$row["total_likes"],
                    "dio_like"       => (int)$row["dio_like"]
                ];
            }

            mysqli_stmt_close($stmt);
        }

        return $comentarios;
    }

    function cantidadPublicacionesPerfil($idEstudiante){
        require_once("../../configuracion/conexion.php"); // archivo con la conexión $conexion

        // Query para contar las publicaciones de todos los emprendimientos de un estudiante
        $sql = "SELECT COUNT(p.id_publicacion) AS total_publicaciones
                FROM emprendimiento e
                INNER JOIN publicacion p ON e.id_emprendimiento = p.id_emprendimiento
                WHERE e.id_estudiante = ? AND p.est_publicacion = 1"; 

        if($stmt = $con->prepare($sql)){
            $stmt->bind_param("i", $idEstudiante); 
            $stmt->execute();
            $stmt->bind_result($total);
            $stmt->fetch();
            $stmt->close();
            return $total;
        } else {
            return 0; 
        }
    }

    function cantidadSeguidores($idEstudiante){
        // Ajusta la ruta a tu archivo de conexión
        require_once("../../configuracion/conexion.php"); 

        // Query para contar los seguidores de todos los emprendimientos de un estudiante
        $sql = "SELECT COUNT(s.id_seguimiento) AS total_seguidores
                FROM seguimiento s
                INNER JOIN emprendimiento e ON s.id_emprendimiento = e.id_emprendimiento
                WHERE e.id_estudiante = ? AND s.est_seguimiento = 1"; // est_seguimiento = 1 (Activo)

        if($stmt = $con->prepare($sql)){
            $stmt->bind_param("i", $idEstudiante); 
            $stmt->execute();
            $stmt->bind_result($total);
            $stmt->fetch();
            $stmt->close();
            // $con->close(); // Si usas conexión persistente, no cierres aquí.

            return $total ?? 0; // Retorna 0 si no hay resultados o es NULL
        } else {
            // Manejo de error si la preparación falla
            error_log("Error al preparar la consulta de seguidores: " . $con->error);
            return 0; 
        }
    }

    function cantidadSeguidos($idEstudiante){
        // Ajusta la ruta a tu archivo de conexión
        require_once("../../configuracion/conexion.php"); 

        // Query para contar los emprendimientos que está siguiendo el estudiante
        $sql = "SELECT COUNT(id_seguimiento) AS total_seguidos
                FROM seguimiento
                WHERE id_estudiante = ? AND est_seguimiento = 1"; // est_seguimiento = 1 (Activo)

        if($stmt = $con->prepare($sql)){
            $stmt->bind_param("i", $idEstudiante); 
            $stmt->execute();
            $stmt->bind_result($total);
            $stmt->fetch();
            $stmt->close();
            // $con->close(); // Si usas conexión persistente, no cierres aquí.

            return $total ?? 0; // Retorna 0 si no hay resultados o es NULL
        } else {
            // Manejo de error si la preparación falla
            error_log("Error al preparar la consulta de seguidos: " . $con->error);
            return 0; 
        }
    }

    function buscarPublicaciones($idEstudiante, $textoBusqueda, $idCategoria = null) {
    require_once("../../configuracion/conexion.php");

    $idEstudiante = intval($idEstudiante);
    $textoBusqueda = mysqli_real_escape_string($con, $textoBusqueda);

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
                        SELECT 1
                        FROM interaccion i2
                        WHERE i2.id_publicacion = p.id_publicacion
                        AND i2.id_estudiante = $idEstudiante
                        AND i2.id_tipo_interaccion = 1
                        AND i2.est_interaccion = 1
                    ) THEN 1 ELSE 0
                END AS dio_like,

                CASE 
                    WHEN EXISTS (
                        SELECT 1
                        FROM seguimiento s
                        WHERE s.id_emprendimiento = e.id_emprendimiento
                        AND s.id_estudiante = $idEstudiante
                        AND s.est_seguimiento = 1
                    ) THEN 1 ELSE 0
                END AS siguiendo,

                CASE
                    WHEN EXISTS (
                        SELECT 1
                        FROM favorito f
                        WHERE f.id_publicacion = p.id_publicacion
                        AND f.id_estudiante = $idEstudiante
                        AND f.est_favorito = 1
                    ) THEN 1 ELSE 0
                END AS es_favorito

            FROM publicacion p
            INNER JOIN emprendimiento e 
                ON p.id_emprendimiento = e.id_emprendimiento
            LEFT JOIN interaccion i 
                ON i.id_publicacion = p.id_publicacion 
                AND i.id_tipo_interaccion = 1
                AND i.est_interaccion = 1
            WHERE p.est_publicacion = 1
              AND p.tit_publicacion LIKE '%$textoBusqueda%'";

    // 🔥 Si se pasa una categoría, agregarla al filtro
    if ($idCategoria !== null && $idCategoria > 0) {
        $sql .= " AND e.id_categoria = " . intval($idCategoria);
    }

    $sql .= " GROUP BY 
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
    } else {
        error_log("Error SQL: " . mysqli_error($con));
    }

    return $data; 
}


function filtrarPublicacionesPorCategoria($idEstudiante, $idCategoria) {
    require_once("../../configuracion/conexion.php");

    $idEstudiante = intval($idEstudiante);
    $idCategoria = intval($idCategoria);

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
                        SELECT 1
                        FROM interaccion i2
                        WHERE i2.id_publicacion = p.id_publicacion
                        AND i2.id_estudiante = $idEstudiante
                        AND i2.id_tipo_interaccion = 1
                        AND i2.est_interaccion = 1
                    ) THEN 1 ELSE 0
                END AS dio_like,

                CASE 
                    WHEN EXISTS (
                        SELECT 1
                        FROM seguimiento s
                        WHERE s.id_emprendimiento = e.id_emprendimiento
                        AND s.id_estudiante = $idEstudiante
                        AND s.est_seguimiento = 1
                    ) THEN 1 ELSE 0
                END AS siguiendo,

                CASE
                    WHEN EXISTS (
                        SELECT 1
                        FROM favorito f
                        WHERE f.id_publicacion = p.id_publicacion
                        AND f.id_estudiante = $idEstudiante
                        AND f.est_favorito = 1
                    ) THEN 1 ELSE 0
                END AS es_favorito

            FROM publicacion p
            INNER JOIN emprendimiento e ON p.id_emprendimiento = e.id_emprendimiento
            LEFT JOIN interaccion i 
                ON i.id_publicacion = p.id_publicacion 
                AND i.id_tipo_interaccion = 1
                AND i.est_interaccion = 1
            WHERE p.est_publicacion = 1
              AND e.id_categoria = $idCategoria
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
    } else {
        error_log("Error SQL: " . mysqli_error($con));
    }

    return $data;
}

?>