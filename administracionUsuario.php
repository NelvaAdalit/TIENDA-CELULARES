<?php
// Habilitar registro de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// admin/usuarios.php
include("../verificar_acceso.php");

// Verificar que el usuario tenga permiso para esta página
verificarPermiso('gestionar_usuarios');

include("../Pagina.php");
include("../BaseDatos.php");
include("../Usuario.php");

$p = new Pagina();
$bd = new BaseDatos();
$usuario = new Usuario($bd);

// Procesar eliminación de usuario
if (isset($_GET['eliminar']) && is_numeric($_GET['eliminar'])) {
    $id = $_GET['eliminar'];
    // No permitir eliminar al propio administrador
    if ($id != $_SESSION['usuario_id']) {
        $usuario->cambiarEstado($id, 0);
        $mensaje = "Usuario desactivado correctamente.";
    } else {
        $mensaje = "No puedes desactivar tu propio usuario.";
    }
}

// Procesar activación de usuario
if (isset($_GET['activar']) && is_numeric($_GET['activar'])) {
    $id = $_GET['activar'];
    $usuario->cambiarEstado($id, 1);
    $mensaje = "Usuario activado correctamente.";
}

// Obtener lista de usuarios
$usuarios = $usuario->listarUsuarios();

$p->cabeza("Administración de Usuarios");
?>

<div class="container mt-4">
    <h1>Administración de Usuarios</h1>
    
    <?php if (isset($mensaje)): ?>
        <div class="alert alert-info"><?php echo htmlspecialchars($mensaje); ?></div>
    <?php endif; ?>
    
    <div class="mb-3">
        <a href="nuevo_usuario.php" class="btn btn-primary">Crear Nuevo Usuario</a>
        <a href="index.php" class="btn btn-secondary">Volver al Panel</a>
    </div>
    
    <div class="card">
        <div class="card-body">
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
                            <th>Último Acceso</th>
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
                                <td><?php echo $u['ultimo_acceso'] ? $u['ultimo_acceso'] : 'Nunca'; ?></td>
                                <td>
                                    <a href="editar_usuario.php?id=<?php echo $u['id']; ?>" class="btn btn-sm btn-warning">Editar</a>
                                    <?php if ($u['id'] != $_SESSION['usuario_id']): ?>
                                        <?php if ($u['activo']): ?>
                                            <a href="usuarios.php?eliminar=<?php echo $u['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Estás seguro de desactivar este usuario?')">Desactivar</a>
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
        </div>
    </div>
</div>

<?php
$p->pie();
?>
