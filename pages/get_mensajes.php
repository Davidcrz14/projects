<?php
session_start();
include 'conection.php';

if (!isset($_SESSION['id_user'])) {
    header('HTTP/1.1 403 Forbidden');
    exit();
}

$id_grupo = isset($_GET['id_grupo']) ? intval($_GET['id_grupo']) : null;

if (!$id_grupo) {
    header('HTTP/1.1 400 Bad Request');
    exit();
}

$mensajes = getMensajes($conn, $id_grupo);

function getMensajes($conn, $id_grupo)
{
    $stmt = $conn->prepare("SELECT m.*, u.username, u.instituto FROM mensajes m JOIN usuarios u ON m.id_user = u.id_user WHERE m.id_grupo = ? ORDER BY m.fecha DESC");
    $stmt->bind_param("i", $id_grupo);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

foreach ($mensajes as $mensaje) {
    echo '<div class="bg-indigo-700 p-6 rounded-lg shadow-lg mb-4 fade-in">';
    echo '<div class="flex items-center mb-2">';
    echo '<img src="' . ($mensaje['instituto'] > 0 ? '../icons/corona.png' : '../icons/alumno.png') . '" alt="icono" class="icon">';
    echo '<h3 class="text-xl font-bold text-blue-300">' . htmlspecialchars($mensaje['username']) . '</h3>';
    echo '</div>';
    echo '<div class="mensaje-content text-gray-300">' . htmlspecialchars($mensaje['mensaje']) . '</div>';
    echo '<p class="text-gray-400 mt-2 text-sm">' . $mensaje['fecha'] . '</p>';
    echo '</div>';
}
