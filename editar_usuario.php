<?php
// Habilitar registro de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// admin/editar_usuario.php
include("../verificar_acceso.php");

// Verificar que el usuario tenga permiso para esta página
verificarPermiso('gestionar_usuarios');

include("../Pagina.php");
include("../BaseDatos.php");
include("../Usuario.php");

$p = new Pagina();
$bd = new BaseDatos();
$usuario = new Usuario($bd);

// Generar token CSRF
$csrf_token = bin2hex(random_bytes(32));
$_SESSION['csrf_token'] = $csrf_token;

// Obtener ID del usuario a editar
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    header("Location: usuarios.php");
    exit;
}

// Obtener datos del usuario
$usuario_data = $usuario->obtenerUsuario($id);

if (empty($usuario_data)) {
    header("Location: usuarios.php");
    exit;
}

// Obtener roles para el select
$roles = $bd->getDatosParametrizados("SELECT * FROM roles ORDER BY id", []);

// Procesar formulario
$mensaje = "";
$tipo_mensaje = "";

// Registrar datos para depuración
$debug_info = [];
$debug_info['session'] = $_SESSION;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Registrar datos POST para depuración
    $debug_info['post'] = $_POST;
    
    $nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
    $correo = isset($_POST['correo']) ? trim($_POST['correo']) : '';
    $nombre_completo = isset($_POST['nombre_completo']) ? trim($_POST['nombre_completo']) : '';
    $rol_id = isset($_POST['rol_id']) ? intval($_POST['rol_id']) : 3;
    $activo = isset($_POST['activo']) ? 1 : 0;
    
    // Validaciones
    if (empty($nombre) || empty($correo) || empty($nombre_completo)) {
        $mensaje = "Todos los campos son obligatorios";
        $tipo_mensaje = "danger";
    } else if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $mensaje = "El correo electrónico no es válido";
        $tipo_mensaje = "danger";
    } else {
        // Verificar si el nombre de usuario o correo ya existe (excluyendo el usuario actual)
        $check = $bd->getDatosParametrizados(
            "SELECT id FROM usuarios WHERE (nombre = ? OR correo = ?) AND id != ?", 
            [$nombre, $correo, $id]
        );
        
        if (!empty($check)) {
            $mensaje = "El nombre de usuario o correo ya está en uso por otro usuario";
            $tipo_mensaje = "danger";
        } else {
            // Actualizar usuario
            $sql = "UPDATE usuarios SET nombre = ?, correo = ?, nombre_completo = ?, rol_id = ?, activo = ? WHERE id = ?";
            $params = [$nombre, $correo, $nombre_completo, $rol_id, $activo, $id];
            
            if ($bd->ejecutarConsulta($sql, $params)) {
                $mensaje = "Usuario actualizado correctamente.";
                $tipo_mensaje = "success";
                
                // Actualizar datos para mostrar en el formulario
                $usuario_data['nombre'] = $nombre;
                $usuario_data['correo'] = $correo;
                $usuario_data['nombre_completo'] = $nombre_completo;
                $usuario_data['rol_id'] = $rol_id;
                $usuario_data['activo'] = $activo;
            } else {
                $mensaje = "Error al actualizar el usuario.";
                $tipo_mensaje = "danger";
            }
        }
    }
    
    // Procesar cambio de contraseña si se proporcionó
    $clave = isset($_POST['clave']) ? $_POST['clave'] : '';
    $confirmar_clave = isset($_POST['confirmar_clave']) ? $_POST['confirmar_clave'] : '';
    
    if (!empty($clave) || !empty($confirmar_clave)) {
        if ($clave !== $confirmar_clave) {
            $mensaje = "Las contraseñas no coinciden";
            $tipo_mensaje = "danger";
        } else if (strlen($clave) < 6) {
            $mensaje = "La contraseña debe tener al menos 6 caracteres";
            $tipo_mensaje = "danger";
        } else {
            // Actualizar contraseña
            $hash = password_hash($clave, PASSWORD_DEFAULT);
            $sql = "UPDATE usuarios SET clave = ? WHERE id = ?";
            $params = [$hash, $id];
            
            if ($bd->ejecutarConsulta($sql, $params)) {
                $mensaje = "Usuario y contraseña actualizados correctamente.";
                $tipo_mensaje = "success";
            } else {
                $mensaje = "Error al actualizar la contraseña.";
                $tipo_mensaje = "danger";
            }
        }
    }
}

$p->cabeza("Editar Usuario");
?>

<div class="container mt-4">
    <h1>Editar Usuario</h1>
    
    <?php if (!empty($mensaje)): ?>
        <div class="alert alert-<?php echo $tipo_mensaje; ?>"><?php echo htmlspecialchars($mensaje); ?></div>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-body">
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                <div class="mb-3">
                    <label for="nombre_completo" class="form-label">Nombre Completo:</label>
                    <input type="text" class="form-control" id="nombre_completo" name="nombre_completo" value="<?php echo htmlspecialchars($usuario_data['nombre_completo']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="nombre" class="form-label">Nombre de Usuario:</label>
                    <input type="text" class="form-control" id="nombre" name="nombre" value="<?php echo htmlspecialchars($usuario_data['nombre']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="correo" class="form-label">Correo Electrónico:</label>
                    <input type="email" class="form-control" id="correo" name="correo" value="<?php echo htmlspecialchars($usuario_data['correo']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="rol_id" class="form-label">Rol:</label>
                    <select class="form-select" id="rol_id" name="rol_id" required>
                        <?php foreach ($roles as $rol): ?>
                            <option value="<?php echo $rol['id']; ?>" <?php echo ($usuario_data['rol_id'] == $rol['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($rol['nombre']); ?> - <?php echo htmlspecialchars($rol['descripcion']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="activo" name="activo" <?php echo $usuario_data['activo'] ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="activo">Usuario Activo</label>
                </div>
                
                <hr>
                <h5>Cambiar Contraseña (dejar en blanco para mantener la actual)</h5>
                
                <div class="mb-3">
                    <label for="clave" class="form-label">Nueva Contraseña:</label>
                    <input type="password" class="form-control" id="clave" name="clave">
                </div>
                <div class="mb-3">
                    <label for="confirmar_clave" class="form-label">Confirmar Nueva Contraseña:</label>
                    <input type="password" class="form-control" id="confirmar_clave" name="confirmar_clave">
                </div>
                
                <div class="d-flex justify-content-between">
                    <a href="usuarios.php" class="btn btn-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
// Mostrar información de depuración en modo desarrollo
if (isset($_GET['debug']) && $_GET['debug'] == 1) {
    $p->mostrarDebug($debug_info);
}

$p->pie();
?>
