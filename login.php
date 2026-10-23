<?php
session_start();
require_once 'conexion.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = trim($_POST['usuario'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (!empty($usuario) && !empty($password)) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE usuario = :usuario");
            $stmt->execute(['usuario' => $usuario]);
            $user = $stmt->fetch();

            if ($user) {
                if (password_verify($password, $user['password']) || $password === $user['password']) {
                    $_SESSION['usuario'] = $user['usuario'];
                    header("Location: dashboard.php");
                    exit();
                } else {
                    $error = "Usuario o contraseña incorrectos.";
                }
            } else {
                $error = "Usuario o contraseña incorrectos.";
            }
        } catch (Exception $e) {
            $error = "Error en la base de datos: " . $e->getMessage();
        }
    } else {
        $error = "Por favor, complete todos los campos.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso Admin - Control de Acceso</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Quicksand:wght@400;600&display=swap" rel="stylesheet">
    <!-- Tus Estilos Personalizados -->
    <link rel="stylesheet" href="estilos.css">
</head>
<body class="bg-light d-flex align-items-center min-vh-100">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card shadow-sm border-0 rounded-3">
                    <div class="card-body p-4 text-center">
                        <h2 class="fw-bold mb-2">Acceso Admin</h2>
                        <p class="text-muted mb-4">Ingrese sus credenciales de administración</p>

                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger text-start py-2 mb-3" role="alert">
                                <?= htmlspecialchars($error) ?>
                            </div>
                        <?php endif; ?>

                        <form action="login.php" method="POST" class="text-start">
                            <div class="mb-3">
                                <label for="usuario" class="form-label small fw-bold">Usuario</label>
                                <input type="text" name="usuario" id="usuario" class="form-control" placeholder="Usuario administrador" required autofocus>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label small fw-bold">Contraseña</label>
                                <input type="password" name="password" id="password" class="form-control" placeholder="Contraseña" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100 py-2 fw-bold">Iniciar Sesión</button>
                        </form>

                        <div class="mt-4">
                            <a href="index.php" class="text-decoration-none text-muted small">&larr; Volver a Pantalla de Marcación</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>