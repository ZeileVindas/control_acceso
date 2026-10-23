<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    // Si no ha iniciado sesión, redirigir al login
    header("Location: login.php");
    exit();
}

require_once 'conexion.php';
$db = $pdo ?? $conn ?? $conexion ?? null;

// Variables para los filtros
$busqueda = trim($_GET['busqueda'] ?? '');
$fecha_desde = $_GET['fecha_desde'] ?? '';
$fecha_hasta = $_GET['fecha_hasta'] ?? '';

// Construir la consulta SQL base con JOIN para obtener los datos del empleado
$sql = "SELECT a.*, e.cedula, e.nombre 
        FROM asistencias a 
        JOIN empleados e ON a.empleado_id = e.id 
        WHERE 1=1";
$params = [];

// Aplicar filtros si existen
if (!empty($busqueda)) {
    $sql .= " AND (e.nombre LIKE :busqueda OR e.cedula LIKE :busqueda)";
    $params['busqueda'] = "%$busqueda%";
}

if (!empty($fecha_desde)) {
    $sql .= " AND a.fecha >= :fecha_desde";
    $params['fecha_desde'] = $fecha_desde;
}

if (!empty($fecha_hasta)) {
    $sql .= " AND a.fecha <= :fecha_hasta";
    $params['fecha_hasta'] = $fecha_hasta;
}

$sql .= " ORDER BY a.fecha DESC, a.hora_entrada DESC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$registros = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Asistencia - Control de Acceso</title>
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
                    <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="empleados.php">Empleados</a></li>
                    <li class="nav-item"><a class="nav-link active fw-bold" href="historial.php">Historial</a></li>
                    <li class="nav-item"><a class="nav-link text-warning" href="logout.php">Cerrar Sesión</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container py-5">
        <h2 class="fw-bold mb-4">Historial de Asistencia</h2>

        <!-- Formulario de Filtros -->
        <div class="card shadow-sm border-0 rounded-3 mb-4">
            <div class="card-body">
                <form method="GET" action="historial.php" class="row g-3">
                    <div class="col-md-4">
                        <label for="busqueda" class="form-label small fw-bold">Empleado o Cédula</label>
                        <input type="text" name="busqueda" id="busqueda" class="form-control" placeholder="Buscar por nombre o cédula" value="<?= htmlspecialchars($busqueda) ?>">
                    </div>
                    <div class="col-md-3">
                        <label for="fecha_desde" class="form-label small fw-bold">Fecha Desde</label>
                        <input type="date" name="fecha_desde" id="fecha_desde" class="form-control" value="<?= htmlspecialchars($fecha_desde) ?>">
                    </div>
                    <div class="col-md-3">
                        <label for="fecha_hasta" class="form-label small fw-bold">Fecha Hasta</label>
                        <input type="date" name="fecha_hasta" id="fecha_hasta" class="form-control" value="<?= htmlspecialchars($fecha_hasta) ?>">
                    </div>
                    <div class="col-md-2 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-primary w-100">Filtrar</button>
                        <a href="historial.php" class="btn btn-outline-secondary w-100">Limpiar</a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabla de Registros -->
        <div class="card shadow-sm border-0 rounded-3">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Cédula</th>
                                <th>Empleado</th>
                                <th>Fecha</th>
                                <th>Entrada</th>
                                <th>Salida</th>
                                <th class="pe-4">Horas Trabajadas</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($registros) > 0): ?>
                                <?php foreach ($registros as $row): ?>
                                    <tr>
                                        <td class="ps-4"><?= htmlspecialchars($row['cedula']) ?></td>
                                        <td><strong><?= htmlspecialchars($row['nombre']) ?></strong></td>
                                        <td><?= htmlspecialchars($row['fecha']) ?></td>
                                        <td>
                                            <?= htmlspecialchars($row['hora_entrada']) ?>
                                            <?php 
                                                // Identificación de llegadas tardías (límite 08:00 AM)
                                                $hora_limite = '08:00:00';
                                                if ($row['hora_entrada'] > $hora_limite) {
                                                    echo ' <span class="badge bg-danger text-white ms-1" style="font-size: 0.7rem;">Tardanza</span>';
                                                } else {
                                                    echo ' <span class="badge bg-success text-white ms-1" style="font-size: 0.7rem;">A tiempo</span>';
                                                }
                                            ?>
                                        </td>
                                        <td>
                                            <?php if ($row['hora_salida']): ?>
                                                <?= htmlspecialchars($row['hora_salida']) ?>
                                            <?php else: ?>
                                                <span class="badge bg-warning text-dark">En turno</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="pe-4">
                                            <?php 
                                                if ($row['hora_salida']) {
                                                    $entrada = new DateTime($row['hora_entrada']);
                                                    $salida = new DateTime($row['hora_salida']);
                                                    $diff = $entrada->diff($salida);
                                                    echo $diff->format('%h h %i m');
                                                } else {
                                                    echo '-';
                                                }
                                            ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">No se encontraron registros de asistencia.</td>
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