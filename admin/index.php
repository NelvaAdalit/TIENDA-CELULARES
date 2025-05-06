<?php
// Habilitar registro de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Iniciar sesión
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Verificar si el usuario está autenticado y es administrador
if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] != 1) {
    header("Location: ../accesodenegado.php");
    exit;
}

include("../Pagina.php");
include("../BaseDatos.php");

$p = new Pagina();
$bd = new BaseDatos();

// Obtener estadísticas para el dashboard
$totalCelulares = $bd->getDatosParametrizados("SELECT COUNT(*) as total FROM celulares", []);
$totalCelulares = $totalCelulares[0]['total'];

$totalUsuarios = $bd->getDatosParametrizados("SELECT COUNT(*) as total FROM usuarios", []);
$totalUsuarios = $totalUsuarios[0]['total'];

$totalCompras = $bd->getDatosParametrizados("SELECT COUNT(*) as total FROM compras", []);
$totalCompras = $totalCompras[0]['total'];

$ventasRecientes = $bd->getDatosParametrizados("SELECT * FROM compras ORDER BY fecha_compra DESC LIMIT 5", []);

$p->cabeza("Panel de Administración");
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="index.php">Panel de Administración</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarAdmin" aria-controls="navbarAdmin" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarAdmin">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link active" href="index.php">Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="productos.php">Productos</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="usuarios.php">Usuarios</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="ventas.php">Ventas</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="configuracion.php">Configuración</a>
                </li>
            </ul>
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" href="../index.php">Ver Tienda</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../fin.php">Cerrar Sesión</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <h1 class="mb-4">Dashboard</h1>
    
    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title">Total de Productos</h5>
                    <p class="card-text display-4"><?php echo $totalCelulares; ?></p>
                    <a href="productos.php" class="btn btn-light">Ver Productos</a>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">Total de Usuarios</h5>
                    <p class="card-text display-4"><?php echo $totalUsuarios; ?></p>
                    <a href="usuarios.php" class="btn btn-light">Ver Usuarios</a>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5 class="card-title">Total de Ventas</h5>
                    <p class="card-text display-4"><?php echo $totalCompras; ?></p>
                    <a href="ventas.php" class="btn btn-light">Ver Ventas</a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5>Ventas Recientes</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($ventasRecientes)): ?>
                        <p class="text-muted">No hay ventas recientes.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Producto</th>
                                        <th>Cliente</th>
                                        <th>Cantidad</th>
                                        <th>Método de Pago</th>
                                        <th>Fecha</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($ventasRecientes as $venta): ?>
                                        <tr>
                                            <td><?php echo $venta['id']; ?></td>
                                            <td><?php echo htmlspecialchars($venta['producto']); ?></td>
                                            <td><?php echo htmlspecialchars($venta['nombre_cliente']); ?></td>
                                            <td><?php echo $venta['cantidad']; ?></td>
                                            <td><?php echo htmlspecialchars($venta['metodo_pago']); ?></td>
                                            <td><?php echo $venta['fecha_compra']; ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <a href="ventas.php" class="btn btn-primary">Ver todas las ventas</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$p->pie();
?>
