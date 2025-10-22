<?php
function obtenerMetricasEmprendedor($idEstudiante, $fechaDesde = null, $fechaHasta = null) {
    include("../../configuracion/conexion.php");

    try {
        // ===============================
        // 🔹 0️⃣ Obtener el id_emprendimiento vinculado al estudiante
        // ===============================
        $sql = "SELECT id_emprendimiento 
                FROM emprendimiento 
                WHERE id_estudiante = '$idEstudiante' 
                LIMIT 1";
        $res = mysqli_query($con, $sql);
        $row = mysqli_fetch_assoc($res);

        if (!$row) {
            return [
                "status" => "error",
                "message" => "No se encontró emprendimiento asociado a este estudiante."
            ];
        }

        $idEmprendimiento = $row['id_emprendimiento'];

        // ===============================
        // 🧠 Filtro de fecha dinámico
        // ===============================
        $filtroFecha = "";
        if ($fechaDesde && $fechaHasta) {
            $filtroFecha = " AND DATE(p.fch_publicacion) BETWEEN '$fechaDesde' AND '$fechaHasta' ";
        }

        // ===============================
        // 1️⃣ Promociones activas
        // ===============================
        $sql = "SELECT COUNT(*) AS promociones_activas
                FROM promocion pr
                INNER JOIN publicacion p ON pr.id_publicacion = p.id_publicacion
                WHERE p.id_emprendimiento = '$idEmprendimiento'
                  AND pr.fch_ini_promocion <= CURDATE()
                  AND pr.fch_fin_promocion >= CURDATE()
                  $filtroFecha";
        $res = mysqli_query($con, $sql);
        $row = mysqli_fetch_assoc($res);
        $promociones_activas = intval($row['promociones_activas']);

        // ===============================
        // 2️⃣ Favoritos guardados (popularidad)
        // ===============================
        $sql = "SELECT COUNT(*) AS total_favoritos
                FROM favorito f
                INNER JOIN publicacion p ON f.id_publicacion = p.id_publicacion
                WHERE p.id_emprendimiento = '$idEmprendimiento'
                  AND f.est_favorito = 1
                  $filtroFecha";
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
                     WHERE p.id_emprendimiento = '$idEmprendimiento' 
                       AND i.est_interaccion = 1
                       $filtroFecha)
                +
                    (SELECT COUNT(*) 
                     FROM comentario c 
                     INNER JOIN publicacion p ON p.id_publicacion = c.id_publicacion
                     WHERE p.id_emprendimiento = '$idEmprendimiento'
                       $filtroFecha)
                ) AS interacciones_totales
        ";
        $res = mysqli_query($con, $sql);
        $row = mysqli_fetch_assoc($res);
        $interacciones_totales = intval($row['interacciones_totales']);

        // ===============================
        // 4️⃣ Promedio de comentarios por publicación
        // ===============================
        $sql = "SELECT AVG(sub.total_comentarios) AS promedio_comentarios
                FROM (
                    SELECT COUNT(c.id_comentario) AS total_comentarios
                    FROM publicacion p
                    LEFT JOIN comentario c ON p.id_publicacion = c.id_publicacion
                    WHERE p.id_emprendimiento = '$idEmprendimiento'
                    $filtroFecha
                    GROUP BY p.id_publicacion
                ) AS sub";
        $res = mysqli_query($con, $sql);
        $row = mysqli_fetch_assoc($res);
        $promedio_comentarios = round(floatval($row['promedio_comentarios']), 2);

        // ===============================
        // 5️⃣ Publicaciones destacadas (Top 3)
        // ===============================
        $sql = "SELECT 
                    p.id_publicacion, 
                    p.tit_publicacion, 
                    p.img_publicacion, 
                    e.nom_emprendimiento,
                    COUNT(DISTINCT i.id_interaccion) AS total_likes,
                    COUNT(DISTINCT c.id_comentario) AS total_comentarios,
                    (COUNT(DISTINCT i.id_interaccion) + COUNT(DISTINCT c.id_comentario)) AS total_interacciones
                FROM publicacion p
                INNER JOIN emprendimiento e ON e.id_emprendimiento = p.id_emprendimiento
                LEFT JOIN interaccion i ON p.id_publicacion = i.id_publicacion AND i.est_interaccion = 1
                LEFT JOIN comentario c ON p.id_publicacion = c.id_publicacion
                WHERE p.id_emprendimiento = '$idEmprendimiento'
                $filtroFecha
                GROUP BY p.id_publicacion
                ORDER BY total_interacciones DESC
                LIMIT 3";
        $res = mysqli_query($con, $sql);
        $publicaciones_destacadas = [];
        while ($row = mysqli_fetch_assoc($res)) {
            $tendencia = rand(5, 25); 
            $publicaciones_destacadas[] = [
                'id_publicacion' => intval($row['id_publicacion']),
                'tit_publicacion' => $row['tit_publicacion'],
                'img_publicacion' => $row['img_publicacion'],
                'nom_emprendimiento' => $row['nom_emprendimiento'],
                'total_likes' => intval($row['total_likes']),
                'total_comentarios' => intval($row['total_comentarios']),
                'total_interacciones' => intval($row['total_interacciones']),
                'tendencia' => $tendencia
            ];
        }

        // ===============================
        // 6️⃣ Crecimiento mensual de seguidores (últimos 6 meses)
        // ===============================
        $filtroFechaSeguimiento = "";
        if ($fechaDesde && $fechaHasta) {
            $filtroFechaSeguimiento = " AND DATE(fch_seguimiento) BETWEEN '$fechaDesde' AND '$fechaHasta' ";
        }

        $sql = "SELECT DATE_FORMAT(fch_seguimiento, '%Y-%m') AS mes, COUNT(*) AS total
                FROM seguimiento
                WHERE id_emprendimiento = '$idEmprendimiento'
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
