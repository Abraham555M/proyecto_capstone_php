<?php 
    function listarPublicacionInicio($idEstudiante) {
        require_once("../../configuracion/conexion.php");

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

                    CASE
                        WHEN EXISTS (
                            SELECT 1
                            FROM favorito f
                            WHERE f.id_publicacion = p.id_publicacion
                            AND f.id_estudiante = $idEstudiante
                            AND f.est_favorito = 1
                        ) THEN 1 ELSE 0
                    END AS es_favorito,

                    -- 🔹 Nuevo campo basado en el valor real de la columna
                    CASE WHEN p.est_actualizado = 1 THEN 1 ELSE 0 END AS es_actualizado,

                    -- Campos específicos según tipo_publicacion
                    prod.prc_producto,
                    prod.stk_producto,
                    prom.dsc_promocion,
                    prom.fch_ini_promocion,
                    prom.fch_fin_promocion,
                    ev.fch_evento,
                    ev.lgr_evento

                FROM publicacion p
                INNER JOIN emprendimiento e 
                    ON p.id_emprendimiento = e.id_emprendimiento
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
                WHERE p.est_publicacion = 1
                GROUP BY 
                    p.id_publicacion,
                    p.id_tipo_publicacion,
                    e.id_emprendimiento,
                    e.nom_emprendimiento,
                    e.img_per_emprendimiento,
                    p.tit_publicacion,
                    p.con_publicacion,
                    p.img_publicacion,
                    p.est_actualizado, -- ✅ importante agregar este campo
                    prod.prc_producto,
                    prod.stk_producto,
                    prom.dsc_promocion,
                    prom.fch_ini_promocion,
                    prom.fch_fin_promocion,
                    ev.fch_evento,
                    ev.lgr_evento
                ORDER BY p.fch_publicacion DESC";

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
                        "es_favorito" => (bool)$row["es_favorito"],
                        "dio_like" => (bool)$row["dio_like"],
                        "tipo_publicacion" => (int)$row["id_tipo_publicacion"],
                        "es_actualizado" => (int)$row["es_actualizado"], // 👈 campo nuevo correcto
                    ],
                    "emprendimiento" => [
                        "id" => $row["id_emprendimiento"],
                        "nombre" => $row["nom_emprendimiento"],
                        "imagen_perfil" => $row["img_per_emprendimiento"],
                        "siguiendo" => (bool)$row["siguiendo"]
                    ]
                ];

                // Agregar datos según tipo_publicacion
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
        }

        return $data;
    }



    function listarPublicacionPerfil($idEstudiante, $idEmprendimiento){
        
    }

    function listarComentariosPublicacion($idPublicacion, $idEstudiante){
        require_once("../../configuracion/conexion.php");
        
        $sql = "SELECT 
                    c.id_comentario,
                    c.con_comentario,
                    c.fch_comentario,
                    c.id_estudiante,  
                    e.nom_estudiante,
                    e.ape_pat_estudiante,
                    e.ape_mat_estudiante,
                    COUNT(DISTINCT CASE WHEN i.est_interaccion = 1 THEN i.id_interaccion END) AS total_likes,
                    MAX(CASE 
                            WHEN i.id_estudiante = ? 
                            AND i.id_tipo_interaccion = 1 
                            AND i.est_interaccion = 1 
                            THEN 1 ELSE 0 
                        END) AS dio_like
                FROM comentario c
                INNER JOIN estudiante e ON c.id_estudiante = e.id_estudiante
                LEFT JOIN interaccion i 
                    ON c.id_comentario = i.id_comentario 
                AND i.id_tipo_interaccion = 1
                WHERE c.id_publicacion = ? 
                AND c.est_comentario = 1
                GROUP BY c.id_comentario, c.con_comentario, c.fch_comentario, 
                        c.id_estudiante, e.nom_estudiante, e.ape_pat_estudiante, e.ape_mat_estudiante
                ORDER BY c.fch_comentario DESC";

        $comentarios = [];

        if ($stmt = mysqli_prepare($con, $sql)) {
            mysqli_stmt_bind_param($stmt, "ii", $idEstudiante, $idPublicacion);
            mysqli_stmt_execute($stmt);
            $resultado = mysqli_stmt_get_result($stmt);

            while ($row = mysqli_fetch_assoc($resultado)) {
                $comentarios[] = [
                    "id_comentario"   => $row["id_comentario"],
                    "con_comentario"  => $row["con_comentario"],
                    "fch_comentario"  => $row["fch_comentario"],
                    "id_estudiante"   => $row["id_estudiante"], // 👈 ahora sí lo envías al adapter
                    "estudiante"      => $row["nom_estudiante"] . " " . 
                                        $row["ape_pat_estudiante"] . " " . 
                                        $row["ape_mat_estudiante"],
                    "total_likes"     => (int)$row["total_likes"],
                    "dio_like"        => (int)$row["dio_like"]
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
                WHERE e.id_estudiante = ? AND p.est_publicacion = 1 AND e.est_emprendimiento = 1"; 

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

                    CASE
                        WHEN EXISTS (
                            SELECT 1
                            FROM favorito f
                            WHERE f.id_publicacion = p.id_publicacion
                            AND f.id_estudiante = $idEstudiante
                            AND f.est_favorito = 1
                        ) THEN 1 ELSE 0
                    END AS es_favorito,

                    -- 🔹 Usar directamente el campo est_actualizado (0/1)
                    CASE WHEN p.est_actualizado = 1 THEN 1 ELSE 0 END AS es_actualizado,

                    -- Campos específicos según tipo de publicación
                    prod.prc_producto,
                    prod.stk_producto,
                    prom.dsc_promocion,
                    prom.fch_ini_promocion,
                    prom.fch_fin_promocion,
                    ev.fch_evento,
                    ev.lgr_evento

                FROM publicacion p
                INNER JOIN emprendimiento e 
                    ON p.id_emprendimiento = e.id_emprendimiento
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
                WHERE p.est_publicacion = 1
                AND (p.tit_publicacion LIKE '%$textoBusqueda%' OR p.con_publicacion LIKE '%$textoBusqueda%')";

        // 🔥 Si se pasa una categoría, agregarla al filtro
        if ($idCategoria !== null && $idCategoria > 0) {
            $sql .= " AND e.id_categoria = " . intval($idCategoria);
        }

        $sql .= " GROUP BY 
                    p.id_publicacion,
                    p.id_tipo_publicacion,
                    e.id_emprendimiento,
                    e.nom_emprendimiento,
                    e.img_per_emprendimiento,
                    p.tit_publicacion,
                    p.con_publicacion,
                    p.img_publicacion,
                    p.est_actualizado,           -- agregado al GROUP BY
                    prod.prc_producto,
                    prod.stk_producto,
                    prom.dsc_promocion,
                    prom.fch_ini_promocion,
                    prom.fch_fin_promocion,
                    ev.fch_evento,
                    ev.lgr_evento
                ORDER BY p.fch_publicacion DESC";

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
                        "es_favorito" => (bool)$row["es_favorito"],
                        "dio_like" => (bool)$row["dio_like"],
                        "tipo_publicacion" => (int)$row["id_tipo_publicacion"],
                        "es_actualizado" => (int)$row["es_actualizado"], // 👈 ahora proviene de est_actualizado
                    ],
                    "emprendimiento" => [
                        "id" => $row["id_emprendimiento"],
                        "nombre" => $row["nom_emprendimiento"],
                        "imagen_perfil" => $row["img_per_emprendimiento"],
                        "siguiendo" => (bool)$row["siguiendo"]
                    ]
                ];

                // Agregar datos según tipo_publicacion
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

                    CASE
                        WHEN EXISTS (
                            SELECT 1
                            FROM favorito f
                            WHERE f.id_publicacion = p.id_publicacion
                            AND f.id_estudiante = $idEstudiante
                            AND f.est_favorito = 1
                        ) THEN 1 ELSE 0
                    END AS es_favorito,

                    -- 🔹 Nuevo campo para controlar si la publicación fue actualizada
                    CASE WHEN p.est_actualizado = 1 THEN 1 ELSE 0 END AS es_actualizado,

                    -- Campos según tipo de publicación
                    prod.prc_producto,
                    prod.stk_producto,
                    prom.dsc_promocion,
                    prom.fch_ini_promocion,
                    prom.fch_fin_promocion,
                    ev.fch_evento,
                    ev.lgr_evento

                FROM publicacion p
                INNER JOIN emprendimiento e 
                    ON p.id_emprendimiento = e.id_emprendimiento
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
                WHERE p.est_publicacion = 1
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
                    p.est_actualizado, -- ✅ Agregado al GROUP BY
                    prod.prc_producto,
                    prod.stk_producto,
                    prom.dsc_promocion,
                    prom.fch_ini_promocion,
                    prom.fch_fin_promocion,
                    ev.fch_evento,
                    ev.lgr_evento
                ORDER BY p.fch_publicacion DESC";

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
                        "es_favorito" => (bool)$row["es_favorito"],
                        "dio_like" => (bool)$row["dio_like"],
                        "tipo_publicacion" => (int)$row["id_tipo_publicacion"],
                        "es_actualizado" => (int)$row["es_actualizado"], // 👈 Campo agregado
                    ],
                    "emprendimiento" => [
                        "id" => $row["id_emprendimiento"],
                        "nombre" => $row["nom_emprendimiento"],
                        "imagen_perfil" => $row["img_per_emprendimiento"],
                        "siguiendo" => (bool)$row["siguiendo"]
                    ]
                ];

                // Agregar datos según tipo_publicacion
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
            error_log("Error SQL: " . mysqli_error($con));
        }

        return $data;
    }

    //Gonzalo
    function agregarPublicacion($data) {
        require("../../configuracion/conexion.php");

        $respuesta = [
            "success" => false,
            "message" => ""
        ];

        // Datos principales
        $idEmprendimiento = $data["id_emprendimiento"];
        $idTipo = $data["id_tipo_publicacion"];
        $titulo = $data["tit_publicacion"];
        $contenido = $data["con_publicacion"];
        $estado = $data["est_publicacion"];
        $imgUrl = $data["img_publicacion"] ?? null;
        $fecha = date("Y-m-d H:i:s");

        // Insertar publicación
        $sql = "INSERT INTO publicacion 
                (id_emprendimiento, id_tipo_publicacion, tit_publicacion, con_publicacion, img_publicacion, fch_publicacion, est_publicacion)
                VALUES (?, ?, ?, ?, ?, ?, ?)";

        $stmt = $con->prepare($sql);
        $stmt->bind_param("iissssi", $idEmprendimiento, $idTipo, $titulo, $contenido, $imgUrl, $fecha, $estado);

        if (!$stmt->execute()) {
            $respuesta["message"] = "Error al registrar publicación";
            return $respuesta;
        }

        $idPublicacion = $con->insert_id;

        //-----------------------------------------
        //  INSERTAR DETALLES SEGÚN TIPO
        //-----------------------------------------
        switch ($idTipo) {

            case 1: // PRODUCTO
                $precio = $data["prc_producto"] ?? 0;
                $stock = $data["stk_producto"] ?? 0;

                $sqlDet = "INSERT INTO producto (id_publicacion, prc_producto, stk_producto, est_producto)
                        VALUES (?, ?, ?, 1)";
                $stmt2 = $con->prepare($sqlDet);
                $stmt2->bind_param("idd", $idPublicacion, $precio, $stock);

                if (!$stmt2->execute()) {
                    $respuesta["message"] = "Error al registrar producto";
                    return $respuesta;
                }
                break;

            case 2: // PROMOCIÓN
                $desc = $data["dsc_promocion"] ?? 0;
                $ini = $data["fch_ini_promocion"] ?? null;
                $fin = $data["fch_fin_promocion"] ?? null;

                $sqlDet = "INSERT INTO promocion (id_publicacion, dsc_promocion, fch_ini_promocion, fch_fin_promocion, est_promocion)
                        VALUES (?, ?, ?, ?, 1)";
                $stmt2 = $con->prepare($sqlDet);
                $stmt2->bind_param("isss", $idPublicacion, $desc, $ini, $fin);

                if (!$stmt2->execute()) {
                    $respuesta["message"] = "Error al registrar promoción";
                    return $respuesta;
                }
                break;

            case 3: // EVENTO
                $fechaEvento = $data["fch_evento"] ?? null;
                $lugar = $data["lgr_evento"] ?? null;

                $sqlDet = "INSERT INTO evento (id_publicacion, fch_evento, lgr_evento, est_evento)
                        VALUES (?, ?, ?, 1)";
                $stmt2 = $con->prepare($sqlDet);
                $stmt2->bind_param("iss", $idPublicacion, $fechaEvento, $lugar);

                if (!$stmt2->execute()) {
                    $respuesta["message"] = "Error al registrar evento";
                    return $respuesta;
                }
                break;

            default:
                // Sin tabla adicional
                break;
        }

        // Todo correcto
        $respuesta["success"] = true;
        $respuesta["message"] = "Publicación creada correctamente";

        return $respuesta;
    }

    function eliminarPublicacion($con, $id_publicacion) {
        $sql = "UPDATE publicacion SET est_publicacion = 0 WHERE id_publicacion = ?";
        $stmt = $con->prepare($sql);
        $stmt->bind_param("i", $id_publicacion);
        return $stmt->execute();
    }

    function actualizarPublicacion($con, $id_tipo_publicacion, $tit_publicacion, $con_publicacion, $img_publicacion, $id_publicacion) {
        $sql = "UPDATE publicacion 
                SET id_tipo_publicacion = ?, 
                    tit_publicacion = ?, 
                    con_publicacion = ?, 
                    img_publicacion = ?, 
                    est_actualizado = 1
                WHERE id_publicacion = ?";
        $stmt = $con->prepare($sql);
        $stmt->bind_param("ssssi", $id_tipo_publicacion, $tit_publicacion, $con_publicacion, $img_publicacion, $id_publicacion);
        $stmt->execute();

        return $stmt->affected_rows >= 0;
    }

    function actualizarProducto($con, $prc_producto, $stk_producto, $id_publicacion) {
        $sql = "UPDATE producto 
                SET prc_producto = ?, stk_producto = ?
                WHERE id_publicacion = ?";
        $stmt = $con->prepare($sql);
        $stmt->bind_param("ssi", $prc_producto, $stk_producto, $id_publicacion);
        return $stmt->execute();
    }

    function actualizarEvento($con, $fch_evento, $lgr_evento, $id_publicacion) {
        $sql = "UPDATE evento 
                SET fch_evento = ?, lgr_evento = ?
                WHERE id_publicacion = ?";
        $stmt = $con->prepare($sql);
        $stmt->bind_param("ssi", $fch_evento, $lgr_evento, $id_publicacion);
        return $stmt->execute();
    }

    function actualizarPromocion($con, $dsc_promocion, $fch_ini, $fch_fin, $id_publicacion) {
        $sql = "UPDATE promocion 
                SET dsc_promocion = ?, fch_ini_promocion = ?, fch_fin_promocion = ?
                WHERE id_publicacion = ?";
        $stmt = $con->prepare($sql);
        $stmt->bind_param("sssi", $dsc_promocion, $fch_ini, $fch_fin, $id_publicacion);
        return $stmt->execute();
    }

    function obtenerDetallePublicacion($idPublicacion) {
        require("../../configuracion/conexion.php");

        $respuesta = [
            "status" => "error",
            "data" => null
        ];

        // Consulta principal
        $sql = "SELECT 
                    p.id_publicacion,
                    p.id_emprendimiento,
                    p.id_tipo_publicacion,
                    p.tit_publicacion,
                    p.con_publicacion,
                    p.img_publicacion,
                    p.fch_publicacion,
                    p.est_publicacion,
                    tp.nom_tipo_publicacion
                FROM publicacion p
                INNER JOIN tipo_publicacion tp 
                    ON p.id_tipo_publicacion = tp.id_tipo_publicacion
                WHERE p.id_publicacion = ?";

        $stmt = $con->prepare($sql);
        $stmt->bind_param("i", $idPublicacion);
        $stmt->execute();
        $result = $stmt->get_result();

        // Validar existencia
        if ($result->num_rows == 0) {
            return $respuesta;
        }

        $publicacion = $result->fetch_assoc();
        $idTipo = intval($publicacion['id_tipo_publicacion']);
        $datosExtra = [];

        // Obtener datos adicionales según tipo
        switch ($idTipo) {

            case 3: // Evento
                $sqlEvento = "SELECT 
                                id_evento,
                                id_publicacion,
                                fch_evento,
                                lgr_evento
                            FROM evento
                            WHERE id_publicacion = ?";
                $stmt2 = $con->prepare($sqlEvento);
                $stmt2->bind_param("i", $idPublicacion);
                $stmt2->execute();
                $extra = $stmt2->get_result()->fetch_assoc();
                if ($extra) { $datosExtra = $extra; }
                break;

            case 2: // Promoción
                $sqlPromo = "SELECT 
                                id_promocion,
                                id_publicacion,
                                dsc_promocion,
                                fch_ini_promocion,
                                fch_fin_promocion
                            FROM promocion
                            WHERE id_publicacion = ?";
                $stmt2 = $con->prepare($sqlPromo);
                $stmt2->bind_param("i", $idPublicacion);
                $stmt2->execute();
                $extra = $stmt2->get_result()->fetch_assoc();
                if ($extra) { $datosExtra = $extra; }
                break;

            case 1: // Producto
                $sqlProducto = "SELECT 
                                    id_producto,
                                    id_publicacion,
                                    prc_producto,
                                    stk_producto
                                FROM producto
                                WHERE id_publicacion = ?";
                $stmt2 = $con->prepare($sqlProducto);
                $stmt2->bind_param("i", $idPublicacion);
                $stmt2->execute();
                $extra = $stmt2->get_result()->fetch_assoc();
                if ($extra) { $datosExtra = $extra; }
                break;

            case 4: // Servicio (sin tabla extra)
                $datosExtra = [];
                break;

            default:
                return [
                    "status" => "error",
                    "msg" => "Tipo de publicación no reconocido"
                ];
        }

        // Respuesta final
        return [
            "status" => "success",
            "data" => [
                "publicacion" => $publicacion,
                "detalles" => $datosExtra
            ]
        ];
    }

    function listarCategoriasPublicacion($idEstudiante){
        require("../../configuracion/conexion.php");

        $categorias = [];

        if ($idEstudiante <= 0) {
            return $categorias;
        }

        $sql = "SELECT 
                    e.id_emprendimiento, 
                    c.id_categoria, 
                    c.nom_categoria, 
                    c.img_categoria
                FROM emprendimiento e
                INNER JOIN categoria c 
                    ON e.id_categoria = c.id_categoria
                WHERE e.id_estudiante = ?
                AND e.est_emprendimiento = 1";

        $stmt = $con->prepare($sql);
        $stmt->bind_param("i", $idEstudiante);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $categorias[] = $row;
        }

        return $categorias;
    }

    function listarPublicacionesCategorias($idEstudiante, $idCategoria, $idEmprendimiento){
        require("../../configuracion/conexion.php");

        $publicaciones = [];

        if ($idEstudiante <= 0 || $idCategoria <= 0 || $idEmprendimiento <= 0) {
            return $publicaciones;
        }

        $sql = "SELECT 
                    p.id_publicacion AS id,
                    p.tit_publicacion AS titulo,
                    p.con_publicacion AS descripcion,
                    p.img_publicacion AS imagen_url
                FROM publicacion p
                INNER JOIN emprendimiento e 
                    ON p.id_emprendimiento = e.id_emprendimiento
                WHERE e.id_estudiante = ?
                AND e.id_categoria = ?
                AND p.id_emprendimiento = ?
                AND p.est_publicacion = 1";

        $stmt = $con->prepare($sql);
        $stmt->bind_param("iii", $idEstudiante, $idCategoria, $idEmprendimiento);
        $stmt->execute();

        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $publicaciones[] = $row;
        }

        return $publicaciones;
    }

    function listarPublicaciones($idEstudiante){
        require("../../configuracion/conexion.php");

        $publicaciones = [];

        if ($idEstudiante <= 0) {
            return $publicaciones;
        }

        $sql = "SELECT 
                    p.id_publicacion AS id,
                    p.tit_publicacion AS titulo,
                    p.con_publicacion AS descripcion,
                    p.img_publicacion AS imagen_url
                FROM publicacion p
                INNER JOIN emprendimiento e 
                    ON p.id_emprendimiento = e.id_emprendimiento
                WHERE e.id_estudiante = ? 
                AND p.est_publicacion = 1
                AND e.est_emprendimiento = 1
                ORDER BY p.fch_publicacion DESC";

        $stmt = $con->prepare($sql);
        $stmt->bind_param("i", $idEstudiante);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $publicaciones[] = $row;
        }

        return $publicaciones;
    }

    function listarTiposPublicacion() {
        require("../../configuracion/conexion.php");

        $tipos = [];

        $sql = "SELECT id_tipo_publicacion, nom_tipo_publicacion 
                FROM tipo_publicacion";

        $stmt = $con->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $tipos[] = $row;
        }

        return $tipos;
    }
?>