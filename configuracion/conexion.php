<?php
    $con = mysqli_connect("localhost", "root","", "bd_proyecto_capstone", "3306");
    if ($con) {
        // Asegurar que MySQL use la misma zona horaria
        mysqli_query($con, "SET time_zone = '-05:00'"); // Perú (Lima)

        // Asegurar que PHP también use la misma zona horaria
        date_default_timezone_set("America/Lima");
    }
?>
