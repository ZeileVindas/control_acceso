<?php
require_once 'conexion.php';
$db = $pdo ?? $conn ?? $conexion ?? null;

$mensaje = '';
$tipo_alerta = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cedula = trim($_POST['cedula'] ?? '');
    $codigo = trim($_POST['codigo'] ?? '');

    if (!empty($cedula) && !empty($codigo) && $db !== null) {
        // 1. Buscar si el empleado existe y está activo
        $stmt = $db->prepare("SELECT * FROM empleados WHERE cedula = :cedula AND estado = 1");
        $stmt->execute(['cedula' => $cedula]);
        $empleado = $stmt->fetch();

        if ($empleado) {
            // 2. Verificar que el código de acceso coincida
            if ($empleado['codigo_acceso'] === $codigo) {
                $empleado_id = $empleado['id'];
                $fecha_hoy = date('Y-m-d');
                $hora_actual = date('H:i:s');

                // 3. Verificar si tiene una entrada abierta hoy (sin hora de salida)
                $stmt_asistencia = $db->prepare("SELECT * FROM asistencias WHERE empleado_id = :empleado_id AND fecha = :fecha AND hora_salida IS NULL");
                $stmt_asistencia->execute(['empleado_id' => $empleado_id, 'fecha' => $fecha_hoy]);
                $asistencia_abierta = $stmt_asistencia->fetch();

                if ($asistencia_abierta) {
                    // Registrar Salida
                    $stmt_salida = $db->prepare("UPDATE asistencias SET hora_salida = :hora WHERE id = :id");
                    $stmt_salida->execute(['hora' => $hora_actual, 'id' => $asistencia_abierta['id']]);
                    $mensaje = "Salida registrada correctamente para " . $empleado['nombre'] . " a las " . $hora_actual;
                    $tipo_alerta = "success";
                } else {
                    // Registrar Entrada
                    $stmt_entrada = $db->prepare("INSERT INTO asistencias (empleado_id, fecha, hora_entrada) VALUES (:empleado_id, :fecha, :hora)");
                    $stmt_entrada->execute(['empleado_id' => $empleado_id, 'fecha' => $fecha_hoy, 'hora' => $hora_actual]);
                    $mensaje = "Entrada registrada correctamente para " . $empleado['nombre'] . " a las " . $hora_actual;
                    $tipo_alerta = "success";
                }
            } else {
                $mensaje = "Cédula o código de acceso incorrecto.";
                $tipo_alerta = "danger";
            }
        } else {
            $mensaje = "El empleado no existe o se encuentra inactivo.";
            $tipo_alerta = "danger";
        }
    } else {
        $mensaje = "Por favor, complete todos los campos.";
        $tipo_alerta = "danger";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Control de Asistencia - Pantalla Pública</title>
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
                        <h2 class="fw-bold mb-2">Control de Asistencia</h2>
                        <p class="text-muted mb-4">Ingrese sus credenciales para registrar Entrada / Salida</p>

                        <?php if (!empty($mensaje)): ?>
                            <div class="alert alert-<?= $tipo_alerta ?> text-start py-2 mb-3" role="alert">
                                <?= htmlspecialchars($mensaje) ?>
                            </div>
                        <?php endif; ?>

                        <form action="index.php" method="POST" class="text-start">
                            <div class="mb-3">
                                <label for="cedula" class="form-label small fw-bold">Cédula</label>
                                <input type="text" name="cedula" id="cedula" class="form-control" placeholder="Ej: 1-1111-1111" required autofocus>
                            </div>
                            <div class="mb-3">
                                <label for="codigo" class="form-label small fw-bold">Código de Acceso</label>
                                <input type="password" name="codigo" id="codigo" class="form-control" placeholder="Código de 6 dígitos" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100 py-2 fw-bold">Registrar Marcación</button>
                        </form>

                        <div class="mt-4">
                            <a href="login.php" class="text-decoration-none text-muted small">Acceso Administrativo</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>