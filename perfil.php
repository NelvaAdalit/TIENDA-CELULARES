<?php
// Habilitar registro de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Iniciar sesión
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

include("Pagina.php");
include("BaseDatos.php");
include("Usuario.php");

$p = new Pagina();
$bd = new BaseDatos();
$usuario = new Usuario($bd);

// Generar token CSRF
$csrf_token = bin2hex(random_bytes(32));
$_SESSION['csrf_token'] = $csrf_token;

// Obtener datos del usuario actual
$id = $_SESSION['usuario_id'];
$usuario_data = $bd->getDatosParametrizados("SELECT * FROM usuarios WHERE id = ?", [$id]);

if (empty($usuario_data)) {
    header("Location: index.php");
    exit;
}

$usuario_data = $usuario_data[0];

// Procesar formulario
$mensaje = "";
$tipo_mensaje = "";

// Registrar datos para depuración
$debug_info = [];
$debug_info['session'] = $_SESSION;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Registrar datos POST para depuración
    $debug_info['post'] = $_POST;
    
    // Determinar qué formulario se envió
    if (isset($_POST['actualizar_info'])) {
        // Formulario de información personal
        $correo = isset($_POST['correo']) ? trim($_POST['correo']) : '';
        $nombre_completo = isset($_POST['nombre_completo']) ? trim($_POST['nombre_completo']) : '';
        
        // Validaciones
        if (empty($correo) || empty($nombre_completo)) {
            $mensaje = "Todos los campos son obligatorios";
            $tipo_mensaje = "danger";
        } else if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            $mensaje = "El correo electrónico no es válido";
            $tipo_mensaje = "danger";
        } else {
            // Verificar si el correo ya existe (excluyendo el usuario actual)
            $check = $bd->getDatosParametrizados(
                "SELECT id FROM usuarios WHERE correo = ? AND id != ?", 
                [$correo, $id]
            );
            
            if (!empty($check)) {
                $mensaje = "El correo electrónico ya está en uso por otro usuario";
                $tipo_mensaje = "danger";
            } else {
                // Actualizar usuario
                $sql = "UPDATE usuarios SET correo = ?, nombre_completo = ? WHERE id = ?";
                $params = [$correo, $nombre_completo, $id];
                
                if ($bd->ejecutarConsulta($sql, $params)) {
                    $mensaje = "Perfil actualizado correctamente.";
                    $tipo_mensaje = "success";
                    
                    // Actualizar datos para mostrar en el formulario
                    $usuario_data['correo'] = $correo;
                    $usuario_data['nombre_completo'] = $nombre_completo;
                } else {
                    $mensaje = "Error al actualizar el perfil.";
                    $tipo_mensaje = "danger";
                }
            }
        }
    } else if (isset($_POST['cambiar_clave'])) {
        // Formulario de cambio de contraseña
        $clave_actual = isset($_POST['clave_actual']) ? $_POST['clave_actual'] : '';
        $clave_nueva = isset($_POST['clave_nueva']) ? $_POST['clave_nueva'] : '';
        $confirmar_clave = isset($_POST['confirmar_clave']) ? $_POST['confirmar_clave'] : '';
        
        if (empty($clave_actual) || empty($clave_nueva) || empty($confirmar_clave)) {
            $mensaje = "Todos los campos son obligatorios para cambiar la contraseña";
            $tipo_mensaje = "danger";
        } else if ($clave_nueva !== $confirmar_clave) {
            $mensaje = "Las nuevas contraseñas no coinciden";
            $tipo_mensaje = "danger";
        } else if (strlen($clave_nueva) < 6) {
            $mensaje = "La nueva contraseña debe tener al menos 6 caracteres";
            $tipo_mensaje = "danger";
        } else {
            // Verificar contraseña actual
            if ($usuario->cambiarClave($id, $clave_actual, $clave_nueva)) {
                $mensaje = "Contraseña actualizada correctamente.";
                $tipo_mensaje = "success";
            } else {
                $mensaje = "La contraseña actual es incorrecta.";
                $tipo_mensaje = "danger";
            }
        }
    }
}

$p->cabeza("Mi Perfil");
$p->menu();
?>

<div class="container mt-4">
    <h1>Mi Perfil</h1>
    
    <?php if (!empty($mensaje)): ?>
        <div class="alert alert-<?php echo $tipo_mensaje; ?>"><?php echo htmlspecialchars($mensaje); ?></div>
    <?php endif; ?>
    
    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5>Información Personal</h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                        <input type="hidden" name="actualizar_info" value="1">
                        <div class="mb-3">
                            <label for="nombre" class="form-label">Nombre de Usuario:</label>
                            <input type="text" class="form-control" id="nombre" value="<?php echo htmlspecialchars($usuario_data['nombre']); ?>" readonly>
                            <small class="text-muted">El nombre de usuario no se puede cambiar.</small>
                        </div>
                        <div class="mb-3">
                            <label for="nombre_completo" class="form-label">Nombre Completo:</label>
                            <input type="text" class="form-control" id="nombre_completo" name="nombre_completo" value="<?php echo htmlspecialchars($usuario_data['nombre_completo']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="correo" class="form-label">Correo Electrónico:</label>
                            <input type="email" class="form-control" id="correo" name="correo" value="<?php echo htmlspecialchars($usuario_data['correo']); ?>" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Actualizar Información</button>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>Cambiar Contraseña</h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                        <input type="hidden" name="cambiar_clave" value="1">
                        <div class="mb-3">
                            <label for="clave_actual" class="form-label">Contraseña Actual:</label>
                            <input type="password" class="form-control" id="clave_actual" name="clave_actual" required>
                        </div>
                        <div class="mb-3">
                            <label for="clave_nueva" class="form-label">Nueva Contraseña:</label>
                            <input type="password" class="form-control" id="clave_nueva" name="clave_nueva" required>
                        </div>
                        <div class="mb-3">
                            <label for="confirmar_clave" class="form-label">Confirmar Nueva Contraseña:</label>
                            <input type="password" class="form-control" id="confirmar_clave" name="confirmar_clave" required>
                        </div>
                        <button type="submit" class="btn btn-warning">Cambiar Contraseña</button>
                    </form>
                </div>
            </div>
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
