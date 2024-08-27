<?php
include 'conection.php';
session_start();
$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = mysqli_real_escape_string($conn, $_POST["email"]);
    $password = mysqli_real_escape_string($conn, $_POST["password"]);

    // Consultar si el correo existe en la base de datos
    $sql = "SELECT * FROM usuarios WHERE correo='$email' AND password='$password'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Iniciar sesión y redirigir a index.php
        $_SESSION['id_user'] = $user['id_user']; // Cambiado de 'id' a 'id_user'
        header('Location: index.php');
        exit();
    } else {
        $message = "Correo electrónico o contraseña incorrectos.";
    }

    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Asesoramientos UTM</title>
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
</head>

<body class="bg-gradient-to-br from-gray-900 to-gray-800 text-gray-200 min-h-screen flex items-center justify-center">
    <div class="w-full max-w-md">
        <div class="bg-gray-800 p-8 rounded-lg shadow-lg animate-slideIn">
            <h1 class="text-3xl font-bold mb-6 text-center text-blue-400">Inicia Sesión</h1>
            <form method="POST" class="space-y-6">
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
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded cursor-pointer transition duration-300 transform hover:scale-105">
                    <i class="fas fa-sign-in-alt mr-2"></i>Login
                </button>
            </form>
            <?php if ($message): ?>
                <div class="mt-4 text-center text-lg font-semibold text-red-500">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>
