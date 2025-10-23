<?php
function obtenerMetricasEmprendedor($idEstudiante, $fechaDesde = null, $fechaHasta = null) {
    include("../../configuracion/conexion.php");

    try {
        // ===============================
        // 🧠 Filtros de fecha dinámicos
        // ===============================
        $filtroFechaPublicacion = "";
        $filtroInteracciones = "";
        $filtroComentarios = "";
        $filtroFechaSeguimiento = "";
        
        if ($fechaDesde && $fechaHasta) {
            $filtroFechaPublicacion = " AND DATE(p.fch_publicacion) BETWEEN '$fechaDesde' AND '$fechaHasta' ";
            $filtroInteracciones = " AND DATE(i.fch_interaccion) BETWEEN '$fechaDesde' AND '$fechaHasta' ";
            $filtroComentarios = " AND DATE(c.fch_comentario) BETWEEN '$fechaDesde' AND '$fechaHasta' ";
            $filtroFechaSeguimiento = " AND DATE(s.fch_seguimiento) BETWEEN '$fechaDesde' AND '$fechaHasta' ";
        }

        // ===============================
        // 1️⃣ Promociones activas
        // ===============================
        $sql = "SELECT COUNT(*) AS promociones_activas
                FROM promocion pr
                INNER JOIN publicacion p ON pr.id_publicacion = p.id_publicacion
                INNER JOIN emprendimiento e ON p.id_emprendimiento = e.id_emprendimiento
                WHERE e.id_estudiante = '$idEstudiante'
                  AND pr.fch_ini_promocion <= CURDATE()
                  AND pr.fch_fin_promocion >= CURDATE()
                  AND p.est_publicacion = 1
                  $filtroFechaPublicacion";
        $res = mysqli_query($con, $sql);
        $row = mysqli_fetch_assoc($res);
        $promociones_activas = intval($row['promociones_activas']);

        // ===============================
        // 2️⃣ Favoritos guardados (popularidad)
        // ===============================
        $sql = "SELECT COUNT(*) AS total_favoritos
                FROM favorito f
                INNER JOIN publicacion p ON f.id_publicacion = p.id_publicacion
                INNER JOIN emprendimiento e ON p.id_emprendimiento = e.id_emprendimiento
                WHERE e.id_estudiante = '$idEstudiante'
                  AND p.est_publicacion = 1
                  AND f.est_favorito = 1
                  $filtroFechaPublicacion";
        $res = mysqli_query($con, $sql);
        $row = mysqli_fetch_assoc($res);
        $total_favoritos = intval($row['total_favoritos']);

        // ===============================
        // 3️⃣ Interacciones totales (likes + comentarios)
        // ===============================
        $sql = "
            SELECT 
                (
                    (SELECT COUNT(*) 
                     FROM interaccion i 
                     INNER JOIN publicacion p ON p.id_publicacion = i.id_publicacion
                     INNER JOIN emprendimiento e ON p.id_emprendimiento = e.id_emprendimiento
                     WHERE e.id_estudiante = '$idEstudiante' 
                       AND p.est_publicacion = 1
                       AND i.est_interaccion = 1
                       $filtroInteracciones)
                +
                    (SELECT COUNT(*) 
                     FROM comentario c 
                     INNER JOIN publicacion p ON p.id_publicacion = c.id_publicacion
                     INNER JOIN emprendimiento e ON p.id_emprendimiento = e.id_emprendimiento
                     WHERE e.id_estudiante = '$idEstudiante'
                       AND p.est_publicacion = 1
                       $filtroComentarios)
                ) AS interacciones_totales
        ";
        $res = mysqli_query($con, $sql);
        $row = mysqli_fetch_assoc($res);
        $interacciones_totales = intval($row['interacciones_totales']);

        // ===============================
        // 4️⃣ Promedio de comentarios por publicación activa
        // ===============================
        $sql = "SELECT AVG(sub.total_comentarios) AS promedio_comentarios
                FROM (
                    SELECT COUNT(c.id_comentario) AS total_comentarios
                    FROM publicacion p
                    INNER JOIN emprendimiento e ON p.id_emprendimiento = e.id_emprendimiento
                    LEFT JOIN comentario c ON p.id_publicacion = c.id_publicacion
                    WHERE e.id_estudiante = '$idEstudiante'
                      AND p.est_publicacion = 1
                      $filtroFechaPublicacion
                    GROUP BY p.id_publicacion
                ) AS sub";
        $res = mysqli_query($con, $sql);
        $row = mysqli_fetch_assoc($res);
        $promedio_comentarios = round(floatval($row['promedio_comentarios']), 2);

        // ===============================
        // 🔹 Publicaciones destacadas (Top 3)
        // ===============================
        $publicaciones_destacadas = [];

        $sql = "
            SELECT 
                p.id_publicacion,
                p.tit_publicacion,
                p.img_publicacion,
                e.nom_emprendimiento,
                COUNT(DISTINCT i.id_interaccion) AS total_likes,
                COUNT(DISTINCT c.id_comentario) AS total_comentarios,
                (COUNT(DISTINCT i.id_interaccion) + COUNT(DISTINCT c.id_comentario)) AS total_interacciones
            FROM publicacion p
            INNER JOIN emprendimiento e ON e.id_emprendimiento = p.id_emprendimiento
            LEFT JOIN interaccion i ON i.id_publicacion = p.id_publicacion AND i.est_interaccion = 1 $filtroInteracciones
            LEFT JOIN comentario c ON c.id_publicacion = p.id_publicacion $filtroComentarios
            WHERE e.id_estudiante = '$idEstudiante'
              AND p.est_publicacion = 1
              $filtroFechaPublicacion
            GROUP BY p.id_publicacion, p.tit_publicacion, p.img_publicacion, e.nom_emprendimiento
            ORDER BY total_interacciones DESC, p.fch_publicacion DESC
            LIMIT 3
        ";

        $resDestacadas = mysqli_query($con, $sql);

        if (!$resDestacadas) {
            error_log("Error en publicaciones destacadas: " . mysqli_error($con));
        } else {
            while ($rowDestacada = mysqli_fetch_assoc($resDestacadas)) {
                $publicaciones_destacadas[] = [
                    'id_publicacion' => intval($rowDestacada['id_publicacion']),
                    'tit_publicacion' => $rowDestacada['tit_publicacion'],
                    'img_publicacion' => $rowDestacada['img_publicacion'],
                    'nom_emprendimiento' => $rowDestacada['nom_emprendimiento'],
                    'total_likes' => intval($rowDestacada['total_likes']),
                    'total_comentarios' => intval($rowDestacada['total_comentarios']),
                    'total_interacciones' => intval($rowDestacada['total_interacciones'])
                ];
            }
        }

        // ===============================
        // 6️⃣ Crecimiento mensual de seguidores (últimos 6 meses)
        // ===============================
        $sql = "SELECT DATE_FORMAT(s.fch_seguimiento, '%Y-%m') AS mes, COUNT(*) AS total
                FROM seguimiento s
                INNER JOIN emprendimiento e ON s.id_emprendimiento = e.id_emprendimiento
                WHERE e.id_estudiante = '$idEstudiante'
                $filtroFechaSeguimiento
                GROUP BY mes
                ORDER BY mes DESC
                LIMIT 6";
        $res = mysqli_query($con, $sql);
        $crecimiento_mensual = [];
        while ($row = mysqli_fetch_assoc($res)) {
            $crecimiento_mensual[] = $row;
        }

        // ===============================
        // 📦 Respuesta final
        // ===============================
        return [
            "status" => "success",
            "data" => [
                "promociones_activas" => $promociones_activas,
                "total_favoritos" => $total_favoritos,
                "interacciones_totales" => $interacciones_totales,
                "promedio_comentarios" => $promedio_comentarios,
                "publicaciones_destacadas" => $publicaciones_destacadas,
                "crecimiento_mensual" => $crecimiento_mensual
            ]
        ];

    } catch (Exception $e) {
        return [
            "status" => "error",
            "message" => "Error al obtener métricas",
            "error" => $e->getMessage()
        ];
    }
}
?>