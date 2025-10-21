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
                    p.id_tipo_publicacion,
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

                    1 AS es_favorito,

                    -- Campos específicos según tipo_publicacion
                    prod.prc_producto,
                    prod.stk_producto,
                    prom.dsc_promocion,
                    prom.fch_ini_promocion,
                    prom.fch_fin_promocion,
                    ev.fch_evento,
                    ev.lgr_evento

                FROM favorito f
                INNER JOIN publicacion p ON f.id_publicacion = p.id_publicacion
                INNER JOIN emprendimiento e ON p.id_emprendimiento = e.id_emprendimiento
                LEFT JOIN interaccion i 
                    ON i.id_publicacion = p.id_publicacion 
                    AND i.id_tipo_interaccion = 1
                    AND i.est_interaccion = 1
                LEFT JOIN producto prod ON p.id_publicacion = prod.id_publicacion AND p.id_tipo_publicacion = 1 AND prod.est_producto = 1
                LEFT JOIN promocion prom ON p.id_publicacion = prom.id_publicacion AND p.id_tipo_publicacion = 2 AND prom.est_promocion = 1
                LEFT JOIN evento ev ON p.id_publicacion = ev.id_publicacion AND p.id_tipo_publicacion = 3 AND ev.est_evento = 1
                WHERE f.id_estudiante = $idEstudiante
                AND f.est_favorito = 1
                AND p.est_publicacion = 1
                GROUP BY 
                    p.id_publicacion,
                    p.id_tipo_publicacion,
                    e.id_emprendimiento,
                    e.nom_emprendimiento,
                    e.img_per_emprendimiento,
                    p.tit_publicacion,
                    p.con_publicacion,
                    p.img_publicacion,
                    prod.prc_producto,
                    prod.stk_producto,
                    prom.dsc_promocion,
                    prom.fch_ini_promocion,
                    prom.fch_fin_promocion,
                    ev.fch_evento,
                    ev.lgr_evento
                ORDER BY f.fch_guardado DESC";

        $result = mysqli_query($con, $sql);
        $data = [];

        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $dataItem = [
                    "publicacion" => [
                        "id" => $row["id_publicacion"],
                        "titulo" => $row["tit_publicacion"],
                        "contenido" => $row["con_publicacion"],
                        "imagen" => $row["img_publicacion"],
                        "likes" => (int)$row["total_me_gusta"],
                        "es_favorito" => true, // Siempre será favorito
                        "dio_like" => (bool)$row["dio_like"],
                        "tipo_publicacion" => (int)$row["id_tipo_publicacion"],
                    ],
                    "emprendimiento" => [
                        "id" => $row["id_emprendimiento"],
                        "nombre" => $row["nom_emprendimiento"],
                        "imagen_perfil" => $row["img_per_emprendimiento"],
                        "siguiendo" => (bool)$row["siguiendo"]
                    ]
                ];

                // Agregar datos específicos según tipo_publicacion
                if ($row["id_tipo_publicacion"] == 1) { // Producto
                    $dataItem["producto"] = [
                        "precio" => $row["prc_producto"],
                        "stock" => $row["stk_producto"]
                    ];
                } elseif ($row["id_tipo_publicacion"] == 2) { // Promoción
                    $dataItem["promocion"] = [
                        "descripcion" => $row["dsc_promocion"],
                        "fecha_inicio" => $row["fch_ini_promocion"],
                        "fecha_fin" => $row["fch_fin_promocion"]
                    ];
                } elseif ($row["id_tipo_publicacion"] == 3) { // Evento
                    $dataItem["evento"] = [
                        "fecha" => $row["fch_evento"],
                        "lugar" => $row["lgr_evento"]
                    ];
                }

                $data[] = $dataItem;
            }
        } else {
            error_log("Error SQL Favoritos: " . mysqli_error($con));
        }

        return $data;
    }

    function filtrarFavoritosPorCategoria($idEstudiante, $idCategoria) {
        require_once("../../configuracion/conexion.php");

        $idEstudiante = intval($idEstudiante);
        $idCategoria = intval($idCategoria);

        $sql = "SELECT 
                    p.id_publicacion,
                    p.id_tipo_publicacion,
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

                    1 AS es_favorito,

                    -- Campos específicos según tipo_publicacion
                    prod.prc_producto,
                    prod.stk_producto,
                    prom.dsc_promocion,
                    prom.fch_ini_promocion,
                    prom.fch_fin_promocion,
                    ev.fch_evento,
                    ev.lgr_evento

                FROM favorito f
                INNER JOIN publicacion p ON f.id_publicacion = p.id_publicacion
                INNER JOIN emprendimiento e ON p.id_emprendimiento = e.id_emprendimiento
                LEFT JOIN interaccion i 
                    ON i.id_publicacion = p.id_publicacion 
                    AND i.id_tipo_interaccion = 1
                    AND i.est_interaccion = 1
                LEFT JOIN producto prod 
                    ON p.id_publicacion = prod.id_publicacion 
                    AND p.id_tipo_publicacion = 1 
                    AND prod.est_producto = 1
                LEFT JOIN promocion prom 
                    ON p.id_publicacion = prom.id_publicacion 
                    AND p.id_tipo_publicacion = 2 
                    AND prom.est_promocion = 1
                LEFT JOIN evento ev 
                    ON p.id_publicacion = ev.id_publicacion 
                    AND p.id_tipo_publicacion = 3 
                    AND ev.est_evento = 1
                WHERE f.id_estudiante = $idEstudiante
                AND f.est_favorito = 1
                AND p.est_publicacion = 1
                AND e.id_categoria = $idCategoria
                GROUP BY 
                    p.id_publicacion,
                    p.id_tipo_publicacion,
                    e.id_emprendimiento,
                    e.nom_emprendimiento,
                    e.img_per_emprendimiento,
                    p.tit_publicacion,
                    p.con_publicacion,
                    p.img_publicacion,
                    prod.prc_producto,
                    prod.stk_producto,
                    prom.dsc_promocion,
                    prom.fch_ini_promocion,
                    prom.fch_fin_promocion,
                    ev.fch_evento,
                    ev.lgr_evento
                ORDER BY f.fch_guardado DESC";

        $result = mysqli_query($con, $sql);
        $data = [];

        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $dataItem = [
                    "publicacion" => [
                        "id" => $row["id_publicacion"],
                        "titulo" => $row["tit_publicacion"],
                        "contenido" => $row["con_publicacion"],
                        "imagen" => $row["img_publicacion"],
                        "likes" => (int)$row["total_me_gusta"],
                        "es_favorito" => true,
                        "dio_like" => (bool)$row["dio_like"],
                        "tipo_publicacion" => (int)$row["id_tipo_publicacion"]
                    ],
                    "emprendimiento" => [
                        "id" => $row["id_emprendimiento"],
                        "nombre" => $row["nom_emprendimiento"],
                        "imagen_perfil" => $row["img_per_emprendimiento"],
                        "siguiendo" => (bool)$row["siguiendo"]
                    ]
                ];

                // Datos específicos según el tipo de publicación
                if ($row["id_tipo_publicacion"] == 1) { // Producto
                    $dataItem["producto"] = [
                        "precio" => $row["prc_producto"],
                        "stock" => $row["stk_producto"]
                    ];
                } elseif ($row["id_tipo_publicacion"] == 2) { // Promoción
                    $dataItem["promocion"] = [
                        "descripcion" => $row["dsc_promocion"],
                        "fecha_inicio" => $row["fch_ini_promocion"],
                        "fecha_fin" => $row["fch_fin_promocion"]
                    ];
                } elseif ($row["id_tipo_publicacion"] == 3) { // Evento
                    $dataItem["evento"] = [
                        "fecha" => $row["fch_evento"],
                        "lugar" => $row["lgr_evento"]
                    ];
                }

                $data[] = $dataItem;
            }
        } else {
            error_log("Error SQL Filtrar Favoritos por Categoría: " . mysqli_error($con));
        }

        return $data;
    }


    function buscarFavoritosConCategoria($idEstudiante, $texto, $idCategoria = null) {
        require_once("../../configuracion/conexion.php");

        $idEstudiante = intval($idEstudiante);
        $texto = mysqli_real_escape_string($con, $texto);

        $sql = "SELECT 
                    p.id_publicacion,
                    p.id_tipo_publicacion,
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

                    1 AS es_favorito,

                    -- Campos específicos según tipo_publicacion
                    prod.prc_producto,
                    prod.stk_producto,
                    prom.dsc_promocion,
                    prom.fch_ini_promocion,
                    prom.fch_fin_promocion,
                    ev.fch_evento,
                    ev.lgr_evento

                FROM favorito f
                INNER JOIN publicacion p ON f.id_publicacion = p.id_publicacion
                INNER JOIN emprendimiento e ON p.id_emprendimiento = e.id_emprendimiento
                LEFT JOIN interaccion i 
                    ON i.id_publicacion = p.id_publicacion 
                    AND i.id_tipo_interaccion = 1
                    AND i.est_interaccion = 1
                LEFT JOIN producto prod 
                    ON p.id_publicacion = prod.id_publicacion 
                    AND p.id_tipo_publicacion = 1 
                    AND prod.est_producto = 1
                LEFT JOIN promocion prom 
                    ON p.id_publicacion = prom.id_publicacion 
                    AND p.id_tipo_publicacion = 2 
                    AND prom.est_promocion = 1
                LEFT JOIN evento ev 
                    ON p.id_publicacion = ev.id_publicacion 
                    AND p.id_tipo_publicacion = 3 
                    AND ev.est_evento = 1
                WHERE f.id_estudiante = $idEstudiante
                AND f.est_favorito = 1
                AND p.est_publicacion = 1
                AND (p.tit_publicacion LIKE '%$texto%' OR p.con_publicacion LIKE '%$texto%')";

        // ⭐ Filtro adicional por categoría (si se envía)
        if ($idCategoria !== null && $idCategoria > 0) {
            $sql .= " AND e.id_categoria = $idCategoria";
        }

        $sql .= " 
                GROUP BY 
                    p.id_publicacion,
                    p.id_tipo_publicacion,
                    e.id_emprendimiento,
                    e.nom_emprendimiento,
                    e.img_per_emprendimiento,
                    p.tit_publicacion,
                    p.con_publicacion,
                    p.img_publicacion,
                    prod.prc_producto,
                    prod.stk_producto,
                    prom.dsc_promocion,
                    prom.fch_ini_promocion,
                    prom.fch_fin_promocion,
                    ev.fch_evento,
                    ev.lgr_evento
                ORDER BY f.fch_guardado DESC";

        $result = mysqli_query($con, $sql);
        $data = [];

        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $dataItem = [
                    "publicacion" => [
                        "id" => $row["id_publicacion"],
                        "titulo" => $row["tit_publicacion"],
                        "contenido" => $row["con_publicacion"],
                        "imagen" => $row["img_publicacion"],
                        "likes" => (int)$row["total_me_gusta"],
                        "es_favorito" => true,
                        "dio_like" => (bool)$row["dio_like"],
                        "tipo_publicacion" => (int)$row["id_tipo_publicacion"]
                    ],
                    "emprendimiento" => [
                        "id" => $row["id_emprendimiento"],
                        "nombre" => $row["nom_emprendimiento"],
                        "imagen_perfil" => $row["img_per_emprendimiento"],
                        "siguiendo" => (bool)$row["siguiendo"]
                    ]
                ];

                // Agregar datos específicos según tipo_publicacion
                if ($row["id_tipo_publicacion"] == 1) { // Producto
                    $dataItem["producto"] = [
                        "precio" => $row["prc_producto"],
                        "stock" => $row["stk_producto"]
                    ];
                } elseif ($row["id_tipo_publicacion"] == 2) { // Promoción
                    $dataItem["promocion"] = [
                        "descripcion" => $row["dsc_promocion"],
                        "fecha_inicio" => $row["fch_ini_promocion"],
                        "fecha_fin" => $row["fch_fin_promocion"]
                    ];
                } elseif ($row["id_tipo_publicacion"] == 3) { // Evento
                    $dataItem["evento"] = [
                        "fecha" => $row["fch_evento"],
                        "lugar" => $row["lgr_evento"]
                    ];
                }

                $data[] = $dataItem;
            }
        } else {
            error_log("Error SQL Buscar Favoritos con Categoría: " . mysqli_error($con));
        }

        return $data;
    }

?>