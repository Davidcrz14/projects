<?php
$servername = "localhost"; // Cambiado a localhost
$username = "root";
$password = "JCeqvBtWhSWGvmpklypAAcyezwkFgUfD";
$dbname = "railway";
$port = 3306;

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname, $port);

// Verificar la conexión
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// No es necesario mostrar un mensaje de conexión exitosa aquí

// Tu código adicional aquí

// Cerrar conexión al finalizar
$conn->close();
