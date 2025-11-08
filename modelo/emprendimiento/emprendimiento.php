<?php
    function listarEmprendimientos($id_estudiante) {
        require_once("../../configuracion/conexion.php");

        $data = array(
            "status" => "error",
            "message" => "No se encontraron emprendimientos",
            "emprendimientos" => array()
        );

        if ($con) {
            // Consulta filtrando por id_estudiante y emprendimientos activos
            $sql = "SELECT * FROM emprendimiento 
                    WHERE id_estudiante = ? 
                    AND est_emprendimiento = 1
                    ORDER BY id_emprendimiento DESC";

            if ($stmt = mysqli_prepare($con, $sql)) {
                mysqli_stmt_bind_param($stmt, "i", $id_estudiante);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);

                if ($result && mysqli_num_rows($result) > 0) {
                    $emprendimientos = array();
                    while ($row = mysqli_fetch_assoc($result)) {
                        $emprendimientos[] = $row;
                    }
                    $data = array(
                        "status" => "success",
                        "message" => "Emprendimientos activos encontrados",
                        "emprendimientos" => $emprendimientos
                    );
                } else {
                    $data = array(
                        "status" => "success",
                        "message" => "No hay emprendimientos activos registrados para este estudiante",
                        "emprendimientos" => array()
                    );
                }
                mysqli_stmt_close($stmt);
            } else {
                $data = array(
                    "status" => "error",
                    "message" => "Error al preparar la consulta",
                    "emprendimientos" => array()
                );
            }
        } else {
            $data = array(
                "status" => "error",
                "message" => "Error en la conexión a la BD",
                "emprendimientos" => array()
            );
        }

        mysqli_close($con);
        return $data;
    }

    function listarEmprendimientosConPublicaciones($idEstudiante){
        require_once("../../configuracion/conexion.php"); // $con = mysqli_connect(...)

        $data = [];

        // 1. Primero traemos todos los emprendimientos del estudiante
        $sqlEmp = "SELECT id_emprendimiento, nom_emprendimiento, img_per_emprendimiento 
                FROM emprendimiento 
                WHERE id_estudiante = ? AND est_emprendimiento = 1";

        $stmtEmp = $con->prepare($sqlEmp);
        $stmtEmp->bind_param("i", $idEstudiante);
        $stmtEmp->execute();
        $resultEmp = $stmtEmp->get_result();

        while($emp = $resultEmp->fetch_assoc()){
            $idEmprendimiento = $emp['id_emprendimiento'];

            // 2. Para cada emprendimiento traemos sus publicaciones
            $sqlPub = "SELECT id_publicacion, tit_publicacion, con_publicacion, img_publicacion, fch_publicacion 
                    FROM publicacion 
                    WHERE id_emprendimiento = ? AND est_publicacion = 1
                    ORDER BY fch_publicacion DESC";

            $stmtPub = $con->prepare($sqlPub);
            $stmtPub->bind_param("i", $idEmprendimiento);
            $stmtPub->execute();
            $resultPub = $stmtPub->get_result();

            $publicaciones = [];
            while($pub = $resultPub->fetch_assoc()){
                $publicaciones[] = $pub;
            }

            // 3. Guardamos el emprendimiento con sus publicaciones
            $emp['publicaciones'] = $publicaciones;
            $data[] = $emp;
        }

        return $data;
    }

    function actualizarEmprendimiento($id_emprendimiento, $nom_emprendimiento, $des_emprendimiento, $img_por_emprendimiento) {
        require_once("../../configuracion/conexion.php");

        $data = array(
            "status" => "error",
            "message" => "Error al actualizar el emprendimiento"
        );

        if ($con) {
            $sql = "UPDATE emprendimiento SET 
                    nom_emprendimiento = ?, 
                    des_emprendimiento = ?, 
                    img_por_emprendimiento = ? 
                    WHERE id_emprendimiento = ?";

            if ($stmt = mysqli_prepare($con, $sql)) {
                mysqli_stmt_bind_param($stmt, "sssi", $nom_emprendimiento, $des_emprendimiento, $img_por_emprendimiento, $id_emprendimiento);
                
                if (mysqli_stmt_execute($stmt)) {
                    if (mysqli_stmt_affected_rows($stmt) > 0) {
                        $data = array(
                            "status" => "success",
                            "message" => "Emprendimiento actualizado correctamente"
                        );
                    } else {
                        $data = array(
                            "status" => "warning",
                            "message" => "No se realizaron cambios (datos iguales)"
                        );
                    }
                } else {
                    $data = array(
                        "status" => "error",
                        "message" => "Error al ejecutar la actualización: " . mysqli_stmt_error($stmt)
                    );
                }
                mysqli_stmt_close($stmt);
            } else {
                $data = array(
                    "status" => "error",
                    "message" => "Error al preparar la consulta: " . mysqli_error($con)
                );
            }
        } else {
            $data = array(
                "status" => "error",
                "message" => "Error en la conexión a la BD"
            );
        }

        mysqli_close($con);
        return $data;
    }

    function eliminarEmprendimiento($id_emprendimiento) {
        require_once("../../configuracion/conexion.php");

        $data = array(
            "status" => "error",
            "message" => "Error al cambiar el estado del emprendimiento"
        );

        if ($con) {
            // Verificar si existe el emprendimiento
            $sqlCheck = "SELECT id_emprendimiento, est_emprendimiento FROM emprendimiento WHERE id_emprendimiento = ?";
            if ($stmtCheck = mysqli_prepare($con, $sqlCheck)) {
                mysqli_stmt_bind_param($stmtCheck, "i", $id_emprendimiento);
                mysqli_stmt_execute($stmtCheck);
                mysqli_stmt_store_result($stmtCheck);
                
                if (mysqli_stmt_num_rows($stmtCheck) == 0) {
                    mysqli_stmt_close($stmtCheck);
                    mysqli_close($con);
                    return array(
                        "status" => "error",
                        "message" => "El emprendimiento no existe"
                    );
                }
                mysqli_stmt_close($stmtCheck);
            }

            // Actualizar el estado a 0 (inactivo)
            $sql = "UPDATE emprendimiento SET est_emprendimiento = 0 WHERE id_emprendimiento = ?";
            
            if ($stmt = mysqli_prepare($con, $sql)) {
                mysqli_stmt_bind_param($stmt, "i", $id_emprendimiento);
                
                if (mysqli_stmt_execute($stmt)) {
                    if (mysqli_stmt_affected_rows($stmt) > 0) {
                        $data = array(
                            "status" => "success",
                            "message" => "Emprendimiento desactivado correctamente"
                        );
                    } else {
                        $data = array(
                            "status" => "error",
                            "message" => "No se pudo actualizar el estado del emprendimiento"
                        );
                    }
                } else {
                    $data = array(
                        "status" => "error",
                        "message" => "Error al ejecutar la actualización: " . mysqli_stmt_error($stmt)
                    );
                }
                mysqli_stmt_close($stmt);
            } else {
                $data = array(
                    "status" => "error",
                    "message" => "Error al preparar la consulta: " . mysqli_error($con)
                );
            }
        } else {
            $data = array(
                "status" => "error",
                "message" => "Error en la conexión a la BD"
            );
        }

        mysqli_close($con);
        return $data;
    }

    //gonzalo
    function agregarEmprendimiento($idEstudiante, $idCategoria, $nomEmprendimiento, $desEmprendimiento, $imgPorEmprendimiento, $imgPerEmprendimiento) {
    
        global $con;

        try {
            $sql = "INSERT INTO emprendimiento 
                    (id_estudiante, id_categoria, nom_emprendimiento, des_emprendimiento, img_por_emprendimiento, img_per_emprendimiento, est_emprendimiento) 
                    VALUES (?, ?, ?, ?, ?, ?, 1)";
            
            $stmt = $con->prepare($sql);
            if (!$stmt) {
                return ["error" => "Error en prepare: " . $con->error];
            }

            $stmt->bind_param(
                "iissss",
                $idEstudiante,
                $idCategoria,
                $nomEmprendimiento,
                $desEmprendimiento,
                $imgPorEmprendimiento,
                $imgPerEmprendimiento
            );

            if ($stmt->execute()) {
                $idInsertado = $stmt->insert_id;
                return [
                    "success" => true,
                    "message" => "Emprendimiento agregado correctamente",
                    "id_emprendimiento" => $idInsertado
                ];
            } else {
                return ["error" => "Error al ejecutar: " . $stmt->error];
            }
        } catch (Exception $e) {
            return ["error" => $e->getMessage()];
        }
    }

    function obtenerEstudianteDelEmprendimiento($idEmprendimiento) {
        require_once("../../configuracion/conexion.php");

        $sql = "SELECT 
                    e.id_estudiante,
                    CONCAT(e.nom_estudiante, ' ', e.ape_pat_estudiante, ' ', e.ape_mat_estudiante) AS nombre_completo,
                    s.nom_sede,
                    e.tel_estudiante
                FROM emprendimiento em
                INNER JOIN estudiante e ON em.id_estudiante = e.id_estudiante
                INNER JOIN sede s ON e.id_sede = s.id_sede
                WHERE em.id_emprendimiento = ?";

        $stmt = $con->prepare($sql);
        $stmt->bind_param("i", $idEmprendimiento);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($fila = $resultado->fetch_assoc()) {
            return $fila; // Devolvemos todo el array asociativo
        } else {
            return null;
        }

        $stmt->close();
        $con->close();
    }


?>
