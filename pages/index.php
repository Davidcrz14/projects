<?php
session_start();
include 'conection.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['id_user'])) {
    header('Location: login.php');
    exit();
}

// Obtener datos del usuario
$id_user = $_SESSION['id_user'];
$sql = "SELECT * FROM usuarios WHERE id_user='$id_user'";
$result = $conn->query($sql);

if (!$result) {
    die('Error en la consulta SQL: ' . $conn->error);
}

$user = $result->fetch_assoc();

if (!$user) {
    die('No se encontró el usuario en la base de datos.');
}

// Determinar el rol del usuario
$role = $user['instituto'] > 0 ? 'profesor' : 'alumno';

// Función para crear un nuevo grupo (para profesores)
function crearGrupo($conn, $id_user, $namecreador, $correocreador, $descripcion)
{
    $sql = "INSERT INTO grupos (namecreador, correocreador, descripcion) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $namecreador, $correocreador, $descripcion);
    return $stmt->execute();
}

// Función para registrar un alumno en un grupo y redirigir a la página de asesoría
function registrarYRedirigir($conn, $id_grupo, $id_alumno)
{
    // Verificar si el alumno ya está registrado en el grupo
    $sql_check = "SELECT id_alumno FROM grupos WHERE id_grupo = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("i", $id_grupo);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result()->fetch_assoc();

    $alumnos_registrados = $result_check['id_alumno'] ? explode(',', trim($result_check['id_alumno'], ',')) : [];

    if (!in_array($id_alumno, $alumnos_registrados)) {
        // Agregar el ID del alumno al grupo
        $alumnos_registrados[] = $id_alumno;
        $nuevo_lista_alumnos = implode(',', $alumnos_registrados);

        $sql_update = "UPDATE grupos SET id_alumno = ? WHERE id_grupo = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("si", $nuevo_lista_alumnos, $id_grupo);
        $stmt_update->execute();
    }

    // Redirigir a la página de asesoría
    header("Location: asesoria.php?id_grupo=" . $id_grupo);
    exit();
}

// Procesar la creación de un nuevo grupo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear_grupo'])) {
    $namecreador = $user['username'];
    $correocreador = $user['correo'];
    $descripcion = $_POST['descripcion'];

    if (crearGrupo($conn, $id_user, $namecreador, $correocreador, $descripcion)) {
        $mensaje = "Grupo creado exitosamente.";
    } else {
        $error = "Error al crear el grupo.";
    }
}

// Procesar el registro y redirección a un grupo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['entrar_grupo'])) {
    $id_grupo = $_POST['id_grupo'];
    registrarYRedirigir($conn, $id_grupo, $id_user);
}

// Obtener los grupos disponibles
$sql_grupos = "SELECT * FROM grupos";
$result_grupos = $conn->query($sql_grupos);
$grupos = $result_grupos->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Asesoramientos UTM</title>
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
        <h1 class="text-4xl font-bold mb-6 text-center text-blue-300 fade-in">
            Bienvenido, <?php echo htmlspecialchars($user['username']); ?>
        </h1>

        <?php if (isset($mensaje)): ?>
            <div class="bg-green-500 text-white p-4 rounded mb-4 fade-in"><?php echo $mensaje; ?></div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="bg-red-500 text-white p-4 rounded mb-4 fade-in"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if ($role === 'profesor'): ?>
            <div class="bg-indigo-700 p-6 rounded-lg shadow-lg mb-8 fade-in">
                <h2 class="text-2xl font-bold mb-4 text-blue-300">Crear Nuevo Grupo de Asesoría</h2>
                <form action="" method="POST" class="space-y-4">
                    <div>
                        <label for="descripcion" class="block text-blue-300 mb-2">Descripción del Grupo:</label>
                        <textarea name="descripcion" id="descripcion" required class="w-full p-2 rounded bg-indigo-600 text-white"></textarea>
                    </div>
                    <button type="submit" name="crear_grupo" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded transition duration-300">
                        <i class="fas fa-plus-circle mr-2"></i>Crear Grupo
                    </button>
                </form>
            </div>

            <h2 class="text-2xl font-bold mb-4 text-center text-blue-300 fade-in">Mis Grupos de Asesoría</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 fade-in">
                <?php
                $sql_grupos = "SELECT * FROM grupos WHERE correocreador = ?";
                $stmt = $conn->prepare($sql_grupos);
                $stmt->bind_param("s", $user['correo']);
                $stmt->execute();
                $result_grupos = $stmt->get_result();

                while ($grupo = $result_grupos->fetch_assoc()): ?>
                    <div class="bg-indigo-700 p-6 rounded-lg shadow-lg hover:shadow-xl transition duration-300">
                        <h3 class="text-xl font-bold text-blue-300 mb-2"><?php echo htmlspecialchars($grupo['namecreador']); ?></h3>
                        <p class="text-gray-300"><i class="fas fa-info-circle mr-2"></i><?php echo htmlspecialchars($grupo['descripcion']); ?></p>
                        <p class="text-gray-300 mt-2"><i class="fas fa-users mr-2"></i>Alumnos registrados:
                            <?php echo $grupo['id_alumno'] ? count(explode(',', trim($grupo['id_alumno'], ','))) : 0; ?>
                        </p>
                        <a href="asesoria.php?id_grupo=<?php echo $grupo['id_grupo']; ?>" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded transition duration-300 mt-4 inline-block">
                            <i class="fas fa-door-open mr-2"></i>Entrar al Grupo
                        </a>
                    </div>
                <?php endwhile; ?>
            </div>

        <?php elseif ($role === 'alumno'): ?>
            <h2 class="text-2xl font-bold mb-4 text-center text-blue-300 fade-in">Grupos de Asesoría Disponibles</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 fade-in">
                <?php
                $sql_grupos = "SELECT * FROM grupos";
                $result_grupos = $conn->query($sql_grupos);

                while ($grupo = $result_grupos->fetch_assoc()):
                    $alumnos_registrados = $grupo['id_alumno'] ? explode(',', trim($grupo['id_alumno'], ',')) : [];
                    $ya_registrado = in_array($id_user, $alumnos_registrados);
                ?>
                    <div class="bg-indigo-700 p-6 rounded-lg shadow-lg hover:shadow-xl transition duration-300">
                        <h3 class="text-xl font-bold text-blue-300 mb-2"><?php echo htmlspecialchars($grupo['namecreador']); ?></h3>
                        <p class="text-gray-300"><i class="fas fa-envelope mr-2"></i><?php echo htmlspecialchars($grupo['correocreador']); ?></p>
                        <p class="text-gray-300"><i class="fas fa-info-circle mr-2"></i><?php echo htmlspecialchars($grupo['descripcion']); ?></p>
                        <form action="" method="POST" class="mt-4">
                            <input type="hidden" name="id_grupo" value="<?php echo $grupo['id_grupo']; ?>">
                            <button type="submit" name="entrar_grupo" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded transition duration-300">
                                <i class="fas fa-door-open mr-2"></i>Entrar al Grupo
                            </button>
                        </form>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>
    </div>

    <footer class="bg-indigo-800 text-center p-4 mt-8">
        <p class="text-blue-300">&copy; 2024 Asesoramientos UTM. DavC-Sin nada que hacer</p>
    </footer>
</body>

</html>
