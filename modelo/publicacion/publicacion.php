<?php 
   function listarPublicacionInicio(){
      require_once("../../configuracion/conexion.php");
      
      $sql = "SELECT 
        p.id_publicacion,
        e.nom_emprendimiento,
        e.img_per_emprendimiento,
        p.tit_publicacion,
        p.con_publicacion,
        p.img_publicacion,
        COUNT(i.id_interaccion) AS total_me_gusta
    FROM publicacion p
    INNER JOIN emprendimiento e 
        ON p.id_emprendimiento = e.id_emprendimiento
    LEFT JOIN interaccion i 
        ON i.id_publicacion = p.id_publicacion 
    AND i.id_tipo_interaccion = 1
    GROUP BY 
        p.id_publicacion,
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
?>