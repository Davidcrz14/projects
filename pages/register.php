<?php
include 'conection.php';

$message = '';
$instituto = 0; // Por defecto es 0 para alumnos

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = mysqli_real_escape_string($conn, $_POST["username"]);
    $email = mysqli_real_escape_string($conn, $_POST["email"]);
    $password = mysqli_real_escape_string($conn, $_POST["password"]);
    $role = $_POST["role"] ?? 'alumno'; // Default to 'alumno'
    $instituto = ($role === 'profesor') ? (int)$_POST["instituto"] : 0;

    // Verificar si el nombre de usuario ya existe
    $sql_check_username = "SELECT * FROM usuarios WHERE username='$username'";
    $result_username = $conn->query($sql_check_username);

    // Verificar si el correo ya existe
    $sql_check_email = "SELECT * FROM usuarios WHERE correo='$email'";
    $result_email = $conn->query($sql_check_email);

    if ($result_username->num_rows > 0) {
        $message = "El nombre de usuario ya está en uso.";
    } elseif ($result_email->num_rows > 0) {
        $message = "El correo electrónico ya está registrado.";
    } else {
        // Inserción de datos
        $sql = "INSERT INTO usuarios (username, correo, password, instituto) VALUES ('$username', '$email', '$password', $instituto)";

        if ($conn->query($sql) === TRUE) {
            $message = "Usuario registrado correctamente. Redirigiendo al login...";
            echo "<script>
                setTimeout(function() {
                    window.location.href = 'login.php';
                }, 5000);
            </script>";
        } else {
            $message = "Error: " . $conn->error;
        }
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asesoramientos UTM</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        @keyframes slideIn {
            from {
                transform: translateY(-20px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .animate-slideIn {
            animation: slideIn 0.5s ease-out;
        }
    </style>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const roleRadios = document.querySelectorAll('input[name="role"]');
            const institutoSelect = document.getElementById('institutoSelect');
            const profesorSection = document.getElementById('profesorSection');

            roleRadios.forEach(radio => {
                radio.addEventListener('change', function() {
                    if (this.value === 'profesor') {
                        profesorSection.style.display = 'block';
                    } else {
                        profesorSection.style.display = 'none';
                    }
                });
            });
        });
    </script>
</head>

<body class="bg-gradient-to-br from-gray-900 to-gray-800 text-gray-200 min-h-screen flex items-center justify-center">
    <div class="w-full max-w-md">
        <div class="bg-gray-800 p-8 rounded-lg shadow-lg animate-slideIn">
            <h1 class="text-3xl font-bold mb-6 text-center text-blue-400">Registrate</h1>
            <form method="POST" class="space-y-6">
                <div>
                    <label for="username" class="block text-gray-400 mb-2">
                        <i class="fas fa-user mr-2 text-blue-500"></i>Username:
                    </label>
                    <input type="text" name="username" id="username" required class="w-full bg-gray-700 text-gray-200 border border-gray-600 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-300">
                </div>

                <div>
                    <label for="email" class="block text-gray-400 mb-2">
                        <i class="fas fa-envelope mr-2 text-blue-500"></i>Email:
                    </label>
                    <input type="email" name="email" id="email" required class="w-full bg-gray-700 text-gray-200 border border-gray-600 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-300">
                </div>

                <div>
                    <label for="password" class="block text-gray-400 mb-2">
                        <i class="fas fa-lock mr-2 text-blue-500"></i>Password:
                    </label>
                    <input type="password" name="password" id="password" required class="w-full bg-gray-700 text-gray-200 border border-gray-600 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-300">
                </div>

                <div class="mb-4">
                    <label class="block text-gray-400 mb-2">Soy:</label>
                    <div class="flex items-center">
                        <input type="radio" id="profesor" name="role" value="profesor" class="mr-2">
                        <label for="profesor" class="text-gray-400">Profesor</label>
                        <input type="radio" id="alumno" name="role" value="alumno" class="ml-4 mr-2">
                        <label for="alumno" class="text-gray-400">Alumno</label>
                    </div>
                </div>

                <div id="profesorSection" style="display: none;">
                    <label for="instituto" class="block text-gray-400 mb-2">
                        <i class="fas fa-graduation-cap mr-2 text-blue-500"></i>Selecciona tu carrera:
                    </label>
                    <select name="instituto" id="institutoSelect" class="w-full bg-gray-700 text-gray-200 border border-gray-600 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-300">
                        <option value="02">Ingeniería en Computación</option>
                        <option value="04">Ingeniería en Electrónica</option>
                        <option value="03">Ingeniería en Diseño</option>
                        <option value="05">Licenciatura en Ciencias Empresariales</option>
                        <option value="07">Licenciatura en Matemáticas Aplicadas</option>
                        <option value="06">Ingeniería en Alimentos</option>
                        <option value="11">Ingeniería Industrial</option>
                        <option value="14">Ingeniería en Mecatrónica</option>
                        <option value="17">Ingeniería en Física Aplicada</option>
                        <option value="31">Ingeniería Mecánica Automotriz</option>
                    </select>
                </div>

                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded cursor-pointer transition duration-300 transform hover:scale-105">
                    <i class="fas fa-user-plus mr-2"></i>Register
                </button>
            </form>

            <?php if ($message): ?>
                <div class="mt-4 text-center text-lg font-semibold text-green-500">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>
