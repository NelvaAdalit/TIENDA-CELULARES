<?php
// Habilitar registro de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include("verificar_acceso.php");

// Verificar que el usuario tenga permiso para esta página
verificarPermiso('gestionar_productos');

include("Pagina.php");
include("BaseDatos.php");

$p = new Pagina();
$bd = new BaseDatos();

$p->cabeza("Administración de Productos");
$p->menu();

// Registrar datos para depuración
$debug_info = [];
$debug_info['session'] = $_SESSION;
$debug_info['permisos'] = isset($_SESSION['permisos']) ? $_SESSION['permisos'] : 'No hay permisos';

?>

<div class="container mt-4">
    <h1>Administración de Productos</h1>
    
    <div class="alert alert-info">
        <p>Esta es una página protegida. Solo usuarios con el permiso 'gestionar_productos' pueden acceder.</p>
    </div>
    
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Información del Usuario</h5>
            <p><strong>ID:</strong> <?php echo $_SESSION['usuario_id']; ?></p>
            <p><strong>Nombre:</strong> <?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?></p>
            <p><strong>Rol:</strong> <?php echo $_SESSION['usuario_rol']; ?></p>
        </div>
    </div>
    
    <div class="mt-4">
        <a href="index.php" class="btn btn-secondary">Volver al inicio</a>
    </div>
</div>

<?php
// Mostrar información de depuración en modo desarrollo
if (isset($_GET['debug']) && $_GET['debug'] == 1) {
    $p->mostrarDebug($debug_info);
}

$p->pie();
?>
