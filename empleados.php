<?php
ob_start();
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

require_once 'conexion.php';
$db = $pdo ?? $conn ?? $conexion ?? null;

$mensaje = '';
$error = '';

// Cambiar Estado (Activar / Desactivar) de forma directa
if (isset($_GET['accion']) && isset($_GET['id'])) {
    $accion = $_GET['accion'];
    $id_emp = $_GET['id'];
    $nuevo_estado = ($accion == 'activar') ? 1 : 0;

    if ($db !== null) {
        $stmt = $db->prepare("UPDATE empleados SET estado = :estado WHERE id = :id");
        $stmt->execute(['estado' => $nuevo_estado, 'id' => $id_emp]);
        header("Location: empleados.php");
        exit();
    }
}

// Registrar o Editar Empleado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? '';
    $cedula = trim($_POST['cedula'] ?? '');
    $nombre = trim($_POST['nombre'] ?? '');
    $correo = trim($_POST['correo'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');

    if (!empty($cedula) && !empty($nombre) && $db !== null) {
        if (!empty($id)) {
            $stmt = $db->prepare("UPDATE empleados SET cedula = :cedula, nombre = :nombre, correo = :correo, telefono = :telefono WHERE id = :id");
            $stmt->execute(['cedula' => $cedula, 'nombre' => $nombre, 'correo' => $correo, 'telefono' => $telefono, 'id' => $id]);
            $mensaje = "Empleado actualizado correctamente.";
        } else {
            $codigo = strtoupper(substr(md5(uniqid(rand(), true)), 0, 6));
            $stmt = $db->prepare("INSERT INTO empleados (cedula, nombre, correo, telefono, codigo_acceso, estado, fecha_registro) VALUES (:cedula, :nombre, :correo, :telefono, :codigo, 1, CURDATE())");
            $stmt->execute(['cedula' => $cedula, 'nombre' => $nombre, 'correo' => $correo, 'telefono' => $telefono, 'codigo' => $codigo]);
            $mensaje = "Empleado registrado con éxito. Código: $codigo";
        }
    } else {
        $error = "Por favor, complete los campos obligatorios.";
    }
}

// Obtener lista de empleados
$stmt_empleados = $db->query("SELECT * FROM empleados ORDER BY id DESC");
$empleados = $stmt_empleados->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mantenimiento de Empleados - Control de Acceso</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Quicksand:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="estilos.css">
</head>
<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="dashboard.php">Control de Acceso</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link active fw-bold" href="empleados.php">Empleados</a></li>
                    <li class="nav-item"><a class="nav-link" href="historial.php">Historial</a></li>
                    <li class="nav-item"><a class="nav-link text-warning" href="logout.php">Cerrar Sesión</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container py-5">
        <h2 class="fw-bold mb-4">Mantenimiento de Empleados</h2>

        <?php if (!empty($mensaje)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($mensaje) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Formulario de Registro -->
        <div class="card shadow-sm border-0 rounded-3 mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold text-primary">Registrar Nuevo Empleado</h5>
            </div>
            <div class="card-body">
                <form action="empleados.php" method="POST" class="row g-3">
                    <div class="col-md-3">
                        <label for="cedula" class="form-label small fw-bold">Cédula</label>
                        <input type="text" name="cedula" id="cedula" class="form-control" placeholder="1-1111-1111" required>
                    </div>
                    <div class="col-md-4">
                        <label for="nombre" class="form-label small fw-bold">Nombre Completo</label>
                        <input type="text" name="nombre" id="nombre" class="form-control" placeholder="Nombre completo" required>
                    </div>
                    <div class="col-md-3">
                        <label for="correo" class="form-label small fw-bold">Correo Electrónico</label>
                        <input type="email" name="correo" id="correo" class="form-control" placeholder="correo@ejemplo.com" required>
                    </div>
                    <div class="col-md-2">
                        <label for="telefono" class="form-label small fw-bold">Teléfono</label>
                        <input type="text" name="telefono" id="telefono" class="form-control" placeholder="8888-8888" required>
                    </div>
                    <div class="col-12 text-end">
                        <button type="submit" class="btn btn-primary px-4 fw-bold">Guardar Empleado</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabla de Empleados -->
        <div class="card shadow-sm border-0 rounded-3">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold text-primary">Lista de Empleados</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Cédula</th>
                                <th>Nombre</th>
                                <th>Correo</th>
                                <th>Teléfono</th>
                                <th>Código Acceso</th>
                                <th>Estado</th>
                                <th class="pe-4 text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($empleados) > 0): ?>
                                <?php foreach ($empleados as $emp): ?>
                                    <tr>
                                        <td class="ps-4"><?= htmlspecialchars($emp['cedula']) ?></td>
                                        <td><strong><?= htmlspecialchars($emp['nombre']) ?></strong></td>
                                        <td><?= htmlspecialchars($emp['correo']) ?></td>
                                        <td><?= htmlspecialchars($emp['telefono']) ?></td>
                                        <td><span class="badge bg-dark font-monospace"><?= htmlspecialchars($emp['codigo_acceso']) ?></span></td>
                                        <td>
                                            <?php if ($emp['estado'] == 1): ?>
                                                <span class="badge bg-success">Activo</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Inactivo</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="pe-4 text-center">
                                            <?php if ($emp['estado'] == 1): ?>
                                                <a href="empleados.php?accion=desactivar&id=<?= $emp['id'] ?>" class="btn btn-sm btn-outline-danger">Desactivar</a>
                                            <?php else: ?>
                                                <a href="empleados.php?accion=activar&id=<?= $emp['id'] ?>" class="btn btn-sm btn-outline-success">Activar</a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">No hay empleados registrados.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>