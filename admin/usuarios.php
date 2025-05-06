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
include("../Usuario.php");

$p = new Pagina();
$bd = new BaseDatos();
$usuario = new Usuario($bd);

// Procesar cambio de estado de usuario
$mensaje = "";
$tipo_mensaje = "";

if (isset($_GET['activar']) && is_numeric($_GET['activar'])) {
    $id = $_GET['activar'];
    if ($usuario->cambiarEstado($id, 1)) {
        $mensaje = "Usuario activado correctamente.";
        $tipo_mensaje = "success";
    } else {
        $mensaje = "Error al activar el usuario.";
        $tipo_mensaje = "danger";
    }
}

if (isset($_GET['desactivar']) && is_numeric($_GET['desactivar'])) {
    $id = $_GET['desactivar'];
    // No permitir desactivar al propio administrador
    if ($id == $_SESSION['usuario_id']) {
        $mensaje = "No puedes desactivar tu propio usuario.";
        $tipo_mensaje = "danger";
    } else if ($usuario->cambiarEstado($id, 0)) {
        $mensaje = "Usuario desactivado correctamente.";
        $tipo_mensaje = "success";
    } else {
        $mensaje = "Error al desactivar el usuario.";
        $tipo_mensaje = "danger";
    }
}

// Obtener lista de usuarios
$usuarios = $usuario->listarUsuarios();

$p->cabeza("Administración de Usuarios");
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
                    <a class="nav-link active" href="usuarios.php">Usuarios</a>
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
    <h1 class="mb-4">Administración de Usuarios</h1>
    
    <?php if (!empty($mensaje)): ?>
        <div class="alert alert-<?php echo $tipo_mensaje; ?>"><?php echo htmlspecialchars($mensaje); ?></div>
    <?php endif; ?>
    
    <div class="mb-3">
        <a href="usuario_nuevo.php" class="btn btn-primary">Crear Nuevo Usuario</a>
    </div>
    
    <div class="card">
        <div class="card-body">
            <?php if (empty($usuarios)): ?>
                <p class="text-muted">No hay usuarios registrados.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Correo</th>
                                <th>Nombre Completo</th>
                                <th>Rol</th>
                                <th>Estado</th>
                                <th>Fecha Registro</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($usuarios as $u): ?>
                                <tr>
                                    <td><?php echo $u['id']; ?></td>
                                    <td><?php echo htmlspecialchars($u['nombre']); ?></td>
                                    <td><?php echo htmlspecialchars($u['correo']); ?></td>
                                    <td><?php echo htmlspecialchars($u['nombre_completo']); ?></td>
                                    <td><?php echo htmlspecialchars($u['rol_nombre']); ?></td>
                                    <td>
                                        <?php if ($u['activo']): ?>
                                            <span class="badge bg-success">Activo</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Inactivo</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo $u['fecha_registro']; ?></td>
                                    <td>
                                        <a href="usuario_editar.php?id=<?php echo $u['id']; ?>" class="btn btn-sm btn-warning">Editar</a>
                                        
                                        <?php if ($u['id'] != $_SESSION['usuario_id']): ?>
                                            <?php if ($u['activo']): ?>
                                                <a href="usuarios.php?desactivar=<?php echo $u['id']; ?>" class="btn btn-sm btn-danger" 
                                                   onclick="return confirm('¿Está seguro de desactivar este usuario?')">Desactivar</a>
                                            <?php else: ?>
                                                <a href="usuarios.php?activar=<?php echo $u['id']; ?>" class="btn btn-sm btn-success">Activar</a>
                                            <?php endif; ?>
                                        <?php endif; ?>
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
