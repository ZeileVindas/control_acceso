<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

require_once 'conexion.php';
$db = $pdo ?? $conn ?? $conexion ?? null;

$total_empleados = 0;
$empleados_activos = 0;
$ingresaron_hoy = 0;
$actualmente_dentro = 0;
$empleados_dentro = [];

if ($db !== null) {
    try {
        // Total de empleados
        $stmt = $db->query("SELECT COUNT(*) FROM empleados");
        $total_empleados = $stmt->fetchColumn();

        // Empleados activos
        $stmt = $db->query("SELECT COUNT(*) FROM empleados WHERE estado = 1");
        $empleados_activos = $stmt->fetchColumn();

        // Fecha actual
        $fecha_hoy = date('Y-m-d');

        // Empleados que ingresaron hoy
        $stmt = $db->prepare("SELECT COUNT(DISTINCT empleado_id) FROM asistencias WHERE fecha = ?");
        $stmt->execute([$fecha_hoy]);
        $ingresaron_hoy = $stmt->fetchColumn();

        // Empleados actualmente dentro (sin hora de salida registrada)
        $stmt = $db->prepare("SELECT COUNT(*) FROM asistencias WHERE fecha = ? AND hora_salida IS NULL");
        $stmt->execute([$fecha_hoy]);
        $actualmente_dentro = $stmt->fetchColumn();

        // Lista de empleados dentro de la empresa
        $sql_dentro = "SELECT a.*, e.cedula, e.nombre 
                       FROM asistencias a 
                       JOIN empleados e ON a.empleado_id = e.id 
                       WHERE a.fecha = ? AND a.hora_salida IS NULL";
        $stmt_dentro = $db->prepare($sql_dentro);
        $stmt_dentro->execute([$fecha_hoy]);
        $empleados_dentro = $stmt_dentro->fetchAll();

    } catch (PDOException $e) {
        $error = "Error al cargar los datos: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Control de Acceso</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Quicksand:wght@400;600&display=swap" rel="stylesheet">
    <!-- Tus Estilos Personalizados -->
    <link rel="stylesheet" href="estilos.css">
</head>
<body class="bg-light">

    <!-- Barra de Navegación -->
    <nav class="navbar navbar-expand-lg navbar-dark shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="dashboard.php">Control de Acceso</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item"><a class="nav-link active fw-bold" href="dashboard.php">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="empleados.php">Empleados</a></li>
                    <li class="nav-item"><a class="nav-link" href="historial.php">Historial</a></li>
                    <li class="nav-item"><a class="nav-link text-warning" href="logout.php">Cerrar Sesión</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container py-5">
        <h2 class="fw-bold mb-4">Bienvenido, <?= htmlspecialchars($_SESSION['usuario']) ?></h2>

        <!-- Tarjetas de Estadísticas -->
        <div class="row g-4 mb-5">
            <div class="col-md-3">
                <div class="card shadow-sm border-0 rounded-3 p-3">
                    <p class="text-muted small mb-1">Total Empleados</p>
                    <h3 class="fw-bold text-primary"><?= $total_empleados ?></h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm border-0 rounded-3 p-3">
                    <p class="text-muted small mb-1">Empleados Activos</p>
                    <h3 class="fw-bold text-success"><?= $empleados_activos ?></h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm border-0 rounded-3 p-3">
                    <p class="text-muted small mb-1">Ingresaron Hoy</p>
                    <h3 class="fw-bold text-info"><?= $ingresaron_hoy ?></h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm border-0 rounded-3 p-3">
                    <p class="text-muted small mb-1">Actualmente Dentro</p>
                    <h3 class="fw-bold text-warning"><?= $actualmente_dentro ?></h3>
                </div>
            </div>
        </div>

        <!-- Acceso Rápido -->
        <div class="card shadow-sm border-0 rounded-3 mb-5">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold">Acceso Rápido</h5>
            </div>
            <div class="card-body d-flex gap-3 flex-wrap">
                <a href="empleados.php" class="btn btn-primary px-4 py-2 fw-bold">Administrar Empleados</a>
                <a href="historial.php" class="btn btn-secondary px-4 py-2 fw-bold">Ver Historial Completo</a>
                <a href="index.php" target="_blank" class="btn btn-outline-dark px-4 py-2 fw-bold">Abrir Pantalla de Marcación</a>
            </div>
        </div>

        <!-- Tabla: Empleados Actualmente Dentro -->
        <div class="card shadow-sm border-0 rounded-3">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold text-primary">Empleados Actualmente Dentro de la Empresa</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Cédula</th>
                                <th>Nombre</th>
                                <th>Fecha</th>
                                <th class="pe-4">Hora de Entrada</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($empleados_dentro) > 0): ?>
                                <?php foreach ($empleados_dentro as $row): ?>
                                    <tr>
                                        <td class="ps-4"><?= htmlspecialchars($row['cedula']) ?></td>
                                        <td><strong><?= htmlspecialchars($row['nombre']) ?></strong></td>
                                        <td><?= htmlspecialchars($row['fecha']) ?></td>
                                        <td class="pe-4"><?= htmlspecialchars($row['hora_entrada']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">No hay empleados dentro de la empresa en este momento.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>