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

// Obtener todas las ventas
$ventas = $bd->getDatosParametrizados("SELECT * FROM compras ORDER BY fecha_compra DESC", []);

// Calcular estadísticas
$totalVentas = count($ventas);
$totalIngresos = 0;

if (!empty($ventas)) {
    // Obtener precios de los productos vendidos
    foreach ($ventas as &$venta) {
        // Extraer marca y modelo del nombre del producto
        $partes = explode(' ', $venta['producto'], 2);
        if (count($partes) >= 2) {
            $marca = $partes[0];
            $modelo = $partes[1];
            
            // Buscar el precio del producto
            $producto = $bd->getDatosParametrizados(
                "SELECT precio FROM celulares WHERE marca LIKE ? AND modelo LIKE ? LIMIT 1",
                [$marca . '%', $modelo . '%']
            );
            
            if (!empty($producto)) {
                $venta['precio_unitario'] = $producto[0]['precio'];
                $venta['total'] = $producto[0]['precio'] * $venta['cantidad'];
                $totalIngresos += $venta['total'];
            } else {
                $venta['precio_unitario'] = 'N/A';
                $venta['total'] = 'N/A';
            }
        } else {
            $venta['precio_unitario'] = 'N/A';
            $venta['total'] = 'N/A';
        }
    }
}

$p->cabeza("Reporte de Ventas");
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
                    <a class="nav-link" href="index.php">Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="productos.php">Productos</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="usuarios.php">Usuarios</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="ventas.php">Ventas</a>
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
    <h1 class="mb-4">Reporte de Ventas</h1>
    
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title">Total de Ventas</h5>
                    <p class="card-text display-4"><?php echo $totalVentas; ?></p>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">Total de Ingresos</h5>
                    <p class="card-text display-4">Bs. <?php echo number_format($totalIngresos, 2); ?></p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h5>Historial de Ventas</h5>
        </div>
        <div class="card-body">
            <?php if (empty($ventas)): ?>
                <p class="text-muted">No hay ventas registradas.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Producto</th>
                                <th>Cliente</th>
                                <th>Cantidad</th>
                                <th>Precio Unitario</th>
                                <th>Total</th>
                                <th>Método de Pago</th>
                                <th>Fecha</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($ventas as $venta): ?>
                                <tr>
                                    <td><?php echo $venta['id']; ?></td>
                                    <td><?php echo htmlspecialchars($venta['producto']); ?></td>
                                    <td><?php echo htmlspecialchars($venta['nombre_cliente']); ?></td>
                                    <td><?php echo $venta['cantidad']; ?></td>
                                    <td>
                                        <?php 
                                        if ($venta['precio_unitario'] !== 'N/A') {
                                            echo 'Bs. ' . number_format($venta['precio_unitario'], 2);
                                        } else {
                                            echo 'N/A';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?php 
                                        if ($venta['total'] !== 'N/A') {
                                            echo 'Bs. ' . number_format($venta['total'], 2);
                                        } else {
                                            echo 'N/A';
                                        }
                                        ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($venta['metodo_pago']); ?></td>
                                    <td><?php echo $venta['fecha_compra']; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
$p->pie();
?>
