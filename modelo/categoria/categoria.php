<?php 
   function listarCategoria(){
      require_once("../../configuracion/conexion.php");
      
      $sql = "SELECT * FROM categoria"; 
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