<?php
// Habilitar registro de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Iniciar sesión
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Verificar si el usuario está autenticado y es vendedor
if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] != 2) {
    header("Location: ../accesodenegado.php");
    exit;
}

include("../Pagina.php");
include("../BaseDatos.php");

$p = new Pagina();
$bd = new BaseDatos();

// Procesar eliminación de producto
$mensaje = "";
$tipo_mensaje = "";

if (isset($_GET['eliminar']) && is_numeric($_GET['eliminar'])) {
    $id = $_GET['eliminar'];
    if ($bd->ejecutarConsulta("DELETE FROM celulares WHERE id = ?", [$id])) {
        $mensaje = "Producto eliminado correctamente.";
        $tipo_mensaje = "success";
    } else {
        $mensaje = "Error al eliminar el producto.";
        $tipo_mensaje = "danger";
    }
}

// Obtener lista de productos
$productos = $bd->getDatosParametrizados("SELECT * FROM celulares ORDER BY marca, modelo", []);

$p->cabeza("Gestión de Productos");
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-success">
    <div class="container-fluid">
        <a class="navbar-brand" href="index.php">Panel de Vendedor</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarVendedor" aria-controls="navbarVendedor" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarVendedor">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link" href="index.php">Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="productos.php">Productos</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="ventas.php">Ventas</a>
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
    <h1 class="mb-4">Gestión de Productos</h1>
    
    <?php if (!empty($mensaje)): ?>
        <div class="alert alert-<?php echo $tipo_mensaje; ?>"><?php echo htmlspecialchars($mensaje); ?></div>
    <?php endif; ?>
    
    <div class="mb-3">
        <a href="producto_nuevo.php" class="btn btn-primary">Agregar Nuevo Producto</a>
    </div>
    
    <div class="card">
        <div class="card-body">
            <?php if (empty($productos)): ?>
                <p class="text-muted">No hay productos registrados.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Imagen</th>
                                <th>Marca</th>
                                <th>Modelo</th>
                                <th>Precio</th>
                                <th>Stock</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($productos as $producto): ?>
                                <tr>
                                    <td><?php echo $producto['id']; ?></td>
                                    <td>
                                        <img src="../imagenes/<?php echo htmlspecialchars($producto['imagen']); ?>" 
                                             alt="<?php echo htmlspecialchars($producto['marca'] . ' ' . $producto['modelo']); ?>" 
                                             class="img-thumbnail" style="max-width: 50px;"
                                             onerror="this.src='../imagenes/placeholder.png'">
                                    </td>
                                    <td><?php echo htmlspecialchars($producto['marca']); ?></td>
                                    <td><?php echo htmlspecialchars($producto['modelo']); ?></td>
                                    <td>Bs. <?php echo htmlspecialchars($producto['precio']); ?></td>
                                    <td><?php echo $producto['stock']; ?></td>
                                    <td>
                                        <a href="producto_editar.php?id=<?php echo $producto['id']; ?>" class="btn btn-sm btn-warning">Editar</a>
                                        <a href="productos.php?eliminar=<?php echo $producto['id']; ?>" class="btn btn-sm btn-danger" 
                                           onclick="return confirm('¿Está seguro de eliminar este producto?')">Eliminar</a>
                                    </td>
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
