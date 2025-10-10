<?php 
    function registrarSeguimiento($idEstudiante, $idEmprendimiento){
        include("../../configuracion/conexion.php"); // conexión en $con

        $sqlCheck = "SELECT id_seguimiento, est_seguimiento 
                    FROM seguimiento 
                    WHERE id_estudiante = ? AND id_emprendimiento = ?";
        $stmtCheck = $con->prepare($sqlCheck);
        $stmtCheck->bind_param("ii", $idEstudiante, $idEmprendimiento);
        $stmtCheck->execute();
        $result = $stmtCheck->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();

            if ($row['est_seguimiento'] == 1) {
                // Dejar de seguir
                $sqlUpdate = "UPDATE seguimiento 
                            SET est_seguimiento = 0 
                            WHERE id_seguimiento = ?";
                $stmtUpdate = $con->prepare($sqlUpdate);
                $stmtUpdate->bind_param("i", $row['id_seguimiento']);
                if ($stmtUpdate->execute()) {
                    echo json_encode(["status" => "no_seguido"]);
                } else {
                    echo json_encode(["status" => "error", "message" => "Error al dejar de seguir"]);
                }
            } else {
                // Reactivar seguimiento
                $sqlUpdate = "UPDATE seguimiento 
                            SET est_seguimiento = 1, fch_seguimiento = NOW() 
                            WHERE id_seguimiento = ?";
                $stmtUpdate = $con->prepare($sqlUpdate);
                $stmtUpdate->bind_param("i", $row['id_seguimiento']);
                if ($stmtUpdate->execute()) {
                    echo json_encode(["status" => "seguido"]);
                } else {
                    echo json_encode(["status" => "error", "message" => "Error al reactivar seguimiento"]);
                }
            }
        } else {
            // Nuevo seguimiento
            $sqlInsert = "INSERT INTO seguimiento (id_estudiante, id_emprendimiento, fch_seguimiento, est_seguimiento) 
                        VALUES (?, ?, NOW(), 1)";
            $stmtInsert = $con->prepare($sqlInsert);
            $stmtInsert->bind_param("ii", $idEstudiante, $idEmprendimiento);
            if ($stmtInsert->execute()) {
                echo json_encode(["status" => "seguido"]);
            } else {
                echo json_encode(["status" => "error", "message" => "Error al registrar seguimiento"]);
            }
        }
    }

    function obtenerCantidadSeguidores($idEmprendedor) {
        require_once("../../configuracion/conexion.php"); // tu conexión MySQLi (variable $con)

        $sql = "SELECT COUNT(s.id_seguimiento) AS total_seguidores
                FROM seguimiento s
                INNER JOIN emprendimiento e ON s.id_emprendimiento = e.id_emprendimiento
                WHERE e.id_estudiante = ?";

        if ($stmt = $con->prepare($sql)) {
            $stmt->bind_param("i", $idEmprendedor);  // i = integer
            $stmt->execute();
            $resultado = $stmt->get_result()->fetch_assoc();

            $stmt->close();

            if ($resultado) {
                return $resultado['total_seguidores'];
            } else {
                return 0;
            }
        } else {
            error_log("Error en prepare: " . $con->error);
            return 0;
        }
    }
?>
