<?php
function obtenerMetricasEmprendedor($idEstudiante) {
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
        // 1️⃣ Total de publicaciones
        // ===============================
        $sql = "SELECT COUNT(*) AS total_publicaciones 
                FROM publicacion 
                WHERE id_emprendimiento = '$idEmprendimiento'";
        $res = mysqli_query($con, $sql);
        $row = mysqli_fetch_assoc($res);
        $total_publicaciones = intval($row['total_publicaciones']);

        // ===============================
        // 2️⃣ Total de seguidores
        // ===============================
        $sql = "SELECT COUNT(*) AS total_seguidores 
                FROM seguimiento 
                WHERE id_emprendimiento = '$idEmprendimiento'";
        $res = mysqli_query($con, $sql);
        $row = mysqli_fetch_assoc($res);
        $total_seguidores = intval($row['total_seguidores']);

        // ===============================
        // 3️⃣ Interacciones totales (likes + comentarios)
        // ===============================
        $sql = "
            SELECT 
                (
                    (SELECT COUNT(*) 
                     FROM interaccion i 
                     INNER JOIN publicacion p ON p.id_publicacion = i.id_publicacion
                     WHERE p.id_emprendimiento = '$idEmprendimiento' AND i.est_interaccion = 1)
                +
                    (SELECT COUNT(*) 
                     FROM comentario c 
                     INNER JOIN publicacion p ON p.id_publicacion = c.id_publicacion
                     WHERE p.id_emprendimiento = '$idEmprendimiento')
                ) AS interacciones_totales
        ";
        $res = mysqli_query($con, $sql);
        $row = mysqli_fetch_assoc($res);
        $interacciones_totales = intval($row['interacciones_totales']);

        // ===============================
        // 4️⃣ Total de vistas/interacciones por publicación
        // ===============================
        $sql = "SELECT p.id_publicacion, p.tit_publicacion, 
                       COUNT(i.id_interaccion) AS total_interacciones
                FROM publicacion p
                LEFT JOIN interaccion i ON p.id_publicacion = i.id_publicacion
                WHERE p.id_emprendimiento = '$idEmprendimiento'
                GROUP BY p.id_publicacion, p.tit_publicacion
                ORDER BY total_interacciones DESC";
        $res = mysqli_query($con, $sql);
        $publicaciones_interacciones = [];
        while ($row = mysqli_fetch_assoc($res)) {
            $publicaciones_interacciones[] = $row;
        }

        // ===============================
        // 5️⃣ Promedio de comentarios por publicación
        // ===============================
        $sql = "SELECT AVG(sub.total_comentarios) AS promedio_comentarios
                FROM (
                    SELECT COUNT(c.id_comentario) AS total_comentarios
                    FROM publicacion p
                    LEFT JOIN comentario c ON p.id_publicacion = c.id_publicacion
                    WHERE p.id_emprendimiento = '$idEmprendimiento'
                    GROUP BY p.id_publicacion
                ) AS sub";
        $res = mysqli_query($con, $sql);
        $row = mysqli_fetch_assoc($res);
        $promedio_comentarios = round(floatval($row['promedio_comentarios']), 2);

        // ===============================
        // 6️⃣ Publicaciones destacadas (Top 3)
        // ===============================
        $sql = "SELECT p.id_publicacion, p.tit_publicacion, p.img_publicacion, e.nom_emprendimiento,
                       COUNT(i.id_interaccion) AS total_interacciones
                FROM publicacion p
                INNER JOIN emprendimiento e ON e.id_emprendimiento = p.id_emprendimiento
                LEFT JOIN interaccion i ON p.id_publicacion = i.id_publicacion
                WHERE p.id_emprendimiento = '$idEmprendimiento'
                GROUP BY p.id_publicacion
                ORDER BY total_interacciones DESC
                LIMIT 3";
        $res = mysqli_query($con, $sql);
        $publicaciones_destacadas = [];
        while ($row = mysqli_fetch_assoc($res)) {
            $publicaciones_destacadas[] = $row;
        }

        // ===============================
        // 7️⃣ Crecimiento mensual de seguidores (últimos 6 meses)
        // ===============================
        $sql = "SELECT DATE_FORMAT(fch_seguimiento, '%Y-%m') AS mes, COUNT(*) AS total
                FROM seguimiento
                WHERE id_emprendimiento = '$idEmprendimiento'
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
                "total_publicaciones" => $total_publicaciones,
                "total_seguidores" => $total_seguidores,
                "interacciones_totales" => $interacciones_totales,
                "promedio_comentarios" => $promedio_comentarios,
                "publicaciones_interacciones" => $publicaciones_interacciones,
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
