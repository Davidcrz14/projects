<?php
session_start();
include 'conection.php';

if (!isset($_SESSION['id_user'])) {
    header('Location: login.php');
    exit();
}

$id_user = $_SESSION['id_user'];
$id_grupo = isset($_GET['id_grupo']) ? $_GET['id_grupo'] : null;

if (!$id_grupo) {
    die('No se especificó un grupo.');
}

$user = getUser($conn, $id_user);
$grupo = getGrupo($conn, $id_grupo);

if (!$user || !$grupo) {
    die('Usuario o grupo no encontrado.');
}

$role = $grupo['correocreador'] === $user['correo'] ? 'profesor' : 'alumno';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['publicar_mensaje'])) {
        $mensaje = $_POST['mensaje'];
        publicarMensaje($conn, $id_grupo, $id_user, $mensaje);
    } elseif (isset($_POST['editar_descripcion']) && $role === 'profesor') {
        $descripcion = $_POST['descripcion'];
        editarDescripcion($conn, $id_grupo, $descripcion);
    }
    exit();
}

$mensajes = getMensajes($conn, $id_grupo);
$alumnos = $role === 'profesor' ? obtenerAlumnos($conn, $id_grupo) : null;

function getUser($conn, $id_user)
{
    $stmt = $conn->prepare("SELECT * FROM usuarios WHERE id_user = ?");
    $stmt->bind_param("i", $id_user);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function getGrupo($conn, $id_grupo)
{
    $stmt = $conn->prepare("SELECT * FROM grupos WHERE id_grupo = ?");
    $stmt->bind_param("i", $id_grupo);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function publicarMensaje($conn, $id_grupo, $id_user, $mensaje)
{
    $stmt = $conn->prepare("INSERT INTO mensajes (id_grupo, id_user, mensaje, fecha) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("iis", $id_grupo, $id_user, $mensaje);
    return $stmt->execute();
}

function editarDescripcion($conn, $id_grupo, $descripcion)
{
    $stmt = $conn->prepare("UPDATE grupos SET descripcion = ? WHERE id_grupo = ?");
    $stmt->bind_param("si", $descripcion, $id_grupo);
    return $stmt->execute();
}

function getMensajes($conn, $id_grupo)
{
    $stmt = $conn->prepare("SELECT m.*, u.username, u.instituto FROM mensajes m JOIN usuarios u ON m.id_user = u.id_user WHERE m.id_grupo = ? ORDER BY m.fecha DESC");
    $stmt->bind_param("i", $id_grupo);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function obtenerAlumnos($conn, $id_grupo)
{
    $stmt = $conn->prepare("SELECT u.username, u.correo FROM usuarios u JOIN grupos g ON FIND_IN_SET(u.id_user, g.id_alumno) WHERE g.id_grupo = ?");
    $stmt->bind_param("i", $id_grupo);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asesoría - <?php echo htmlspecialchars($grupo['descripcion']); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        .fade-in {
            animation: fadeIn 0.5s ease-out;
        }

        .icon {
            width: 20px;
            height: 20px;
            vertical-align: middle;
            margin-right: 5px;
        }
    </style>
</head>

<body class="bg-gradient-to-br from-indigo-900 to-blue-800 text-gray-200 min-h-screen">
    <nav class="bg-indigo-800 p-4 shadow-lg">
        <div class="container mx-auto flex justify-between items-center">
            <a href="index.php" class="text-2xl font-bold text-blue-300 hover:text-blue-200 transition duration-300">
                <i class="fas fa-graduation-cap mr-2"></i>Asesoramientos UTM
            </a>
            <a href="logout.php" class="text-blue-300 hover:text-blue-200 transition duration-300">
                <i class="fas fa-sign-out-alt mr-2"></i>Cerrar sesión
            </a>
        </div>
    </nav>

    <div class="container mx-auto p-8">
        <h1 class="text-4xl font-bold mb-6 text-center text-blue-300"><?php echo htmlspecialchars($grupo['descripcion']); ?></h1>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="md:col-span-2">
                <div class="bg-indigo-700 p-6 rounded-lg shadow-lg mb-8">
                    <h2 class="text-2xl font-bold mb-4 text-blue-300">Publicar un nuevo mensaje</h2>
                    <form id="mensajeForm" class="space-y-4">
                        <div>
                            <textarea name="mensaje" required class="w-full p-2 rounded bg-indigo-600 text-white" placeholder="Escribe tu mensaje aquí..."></textarea>
                        </div>
                        <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded transition duration-300">
                            <i class="fas fa-paper-plane mr-2"></i>Publicar
                        </button>
                    </form>
                </div>

                <h2 class="text-2xl font-bold mb-4 text-blue-300">Mensajes del Grupo</h2>
                <div id="mensajes" class="space-y-4">
                    <!-- Los mensajes se cargarán aquí dinámicamente -->
                </div>
            </div>

            <div class="md:col-span-1">
                <?php if ($role === 'profesor'): ?>
                    <div class="bg-indigo-700 p-6 rounded-lg shadow-lg mb-8">
                        <h2 class="text-2xl font-bold mb-4 text-blue-300">Editar Nombre del Grupo</h2>
                        <form id="descripcionForm" class="space-y-4">
                            <div>
                                <textarea name="descripcion" required class="w-full p-2 rounded bg-indigo-600 text-white"><?php echo htmlspecialchars($grupo['descripcion']); ?></textarea>
                            </div>
                            <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded transition duration-300">
                                <i class="fas fa-edit mr-2"></i>Actualizar Nombre
                            </button>
                        </form>
                    </div>

                    <div class="bg-indigo-700 p-6 rounded-lg shadow-lg mb-8">
                        <h2 class="text-2xl font-bold mb-4 text-blue-300">Lista de Alumnos</h2>
                        <ul class="space-y-4">
                            <?php foreach ($alumnos as $alumno): ?>
                                <li class="text-gray-300">
                                    <i class="fas fa-user mr-2"></i>
                                    <?php echo htmlspecialchars($alumno['username']) . ' (' . htmlspecialchars($alumno['correo']) . ')'; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <footer class="bg-indigo-800 text-center p-4 mt-8">
        <p class="text-blue-300">&copy; 2024 Asesoramientos UTM. DavC-Sin nada que hacer</p>
    </footer>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            function cargarMensajes() {
                $.ajax({
                    url: 'get_mensajes.php',
                    method: 'GET',
                    data: {
                        id_grupo: <?php echo $id_grupo; ?>
                    },
                    success: function(data) {
                        $('#mensajes').html(data);
                    }
                });
            }

            cargarMensajes();
            setInterval(cargarMensajes, 5000);

            $('#mensajeForm').submit(function(e) {
                e.preventDefault();
                $.ajax({
                    url: '<?php echo $_SERVER['PHP_SELF'] . '?id_grupo=' . $id_grupo; ?>',
                    method: 'POST',
                    data: $(this).serialize() + '&publicar_mensaje=1',
                    success: function() {
                        $('#mensajeForm')[0].reset();
                        cargarMensajes();
                    }
                });
            });

            $('#descripcionForm').submit(function(e) {
                e.preventDefault();
                $.ajax({
                    url: '<?php echo $_SERVER['PHP_SELF'] . '?id_grupo=' . $id_grupo; ?>',
                    method: 'POST',
                    data: $(this).serialize() + '&editar_descripcion=1',
                    success: function() {
                        alert('Nombre actualizado con éxito');
                    }
                });
            });
        });
    </script>
</body>

</html>
