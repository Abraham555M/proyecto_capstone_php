<?php 
    function listarSolicitudesSoporte($idEstudiante){
        require_once("../../configuracion/conexion.php"); // tu conexión MySQLi (variable $con)

        // Verificar que el parámetro sea válido
        if (empty($idEstudiante) || !is_numeric($idEstudiante)) {
            return ["status" => "error", "message" => "ID de estudiante no válido."];
        }

        // Consulta para obtener las solicitudes del estudiante
        $sql = "SELECT id_soporte, id_estudiante, men_soporte, fec_soporte, est_soporte 
                FROM soporte 
                WHERE id_estudiante = ? 
                ORDER BY fec_soporte DESC";

        // Preparar la sentencia
        if ($stmt = $con->prepare($sql)) {
            $stmt->bind_param("i", $idEstudiante);
            $stmt->execute();
            $resultado = $stmt->get_result();

            $solicitudes = [];
            while ($fila = $resultado->fetch_assoc()) {
                $solicitudes[] = $fila;
            }

            $stmt->close();
            $con->close();

            return $solicitudes;
        } else {
            return ["status" => "error", "message" => "Error al preparar la consulta."];
        }
    }

    function listarActividadesNotificaciones($idEmprendedor) {
        require("../../configuracion/conexion.php"); // tu conexión MySQLi en variable $con

        $sql = "SELECT 
                    n.id_notificacion,
                    n.tit_notificacion,
                    n.men_notificacion,
                    n.fch_notificacion,
                    n.est_leida_notificacion,
                    tn.nom_tipo_notificacion,
                    e.nom_estudiante,
                    e.ape_pat_estudiante,
                    e.ape_mat_estudiante,
                    e.ema_estudiante
                FROM notificacion n
                INNER JOIN tipo_notificacion tn ON n.id_tipo_notificacion = tn.id_tipo_notificacion
                INNER JOIN estudiante e ON n.id_estudiante = e.id_estudiante
                WHERE n.id_estudiante_emprendedor = ?
                ORDER BY n.fch_notificacion DESC";

        $stmt = mysqli_prepare($con, $sql);
        mysqli_stmt_bind_param($stmt, "i", $idEmprendedor);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        $notificaciones = [];

        while ($row = mysqli_fetch_assoc($result)) {
            $notificaciones[] = [
                'id_notificacion' => $row['id_notificacion'],
                'titulo' => $row['tit_notificacion'],
                'mensaje' => $row['men_notificacion'],
                'fecha' => $row['fch_notificacion'],
                'leida' => $row['est_leida_notificacion'],
                'tipo' => $row['nom_tipo_notificacion'],
                'nombre_emisor' => trim($row['nom_estudiante'] . ' ' . $row['ape_pat_estudiante']),
                'correo_emisor' => $row['ema_estudiante']
            ];
        }

        mysqli_stmt_close($stmt);
        mysqli_close($con);

        return $notificaciones;
    }
?>

