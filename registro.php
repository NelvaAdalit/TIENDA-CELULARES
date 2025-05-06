<?php
// Habilitar registro de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Iniciar sesión
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include("Pagina.php");
include("BaseDatos.php");
include("Usuario.php");

$p = new Pagina();
$bd = new BaseDatos();
$usuario = new Usuario($bd);

// Verificar si ya está autenticado
if ($usuario->estaAutenticado()) {
    header("Location: index.php");
    exit;
}

// Generar token CSRF
$csrf_token = bin2hex(random_bytes(32));
$_SESSION['csrf_token'] = $csrf_token;

// Mensaje de error o éxito
$mensaje = "";
$tipo_mensaje = "";

// Registrar datos para depuración
$debug_info = [];
$debug_info['session'] = $_SESSION;

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Registrar datos POST para depuración
    $debug_info['post'] = $_POST;
    
    // Verificar token CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $mensaje = "Error de validación del formulario. Por favor, intente nuevamente.";
        $tipo_mensaje = "danger";
        $debug_info['error'] = "CSRF token inválido";
        $debug_info['post_token'] = isset($_POST['csrf_token']) ? $_POST['csrf_token'] : 'No enviado';
        $debug_info['session_token'] = $_SESSION['csrf_token'];
    } else {
        // Obtener datos del formulario
        $nombre = isset($_POST['usuario']) ? trim($_POST['usuario']) : '';
        $correo = isset($_POST['correo']) ? trim($_POST['correo']) : '';
        $clave = isset($_POST['clave']) ? $_POST['clave'] : '';
        $confirmar_clave = isset($_POST['confirmar_clave']) ? $_POST['confirmar_clave'] : '';
        $nombre_completo = isset($_POST['nombre_completo']) ? trim($_POST['nombre_completo']) : '';
        
        // Validaciones
        if (empty($nombre) || empty($correo) || empty($clave) || empty($confirmar_clave) || empty($nombre_completo)) {
            $mensaje = "Todos los campos son obligatorios";
            $tipo_mensaje = "danger";
            $debug_info['error'] = "Campos vacíos";
        } else if ($clave !== $confirmar_clave) {
            $mensaje = "Las contraseñas no coinciden";
            $tipo_mensaje = "danger";
            $debug_info['error'] = "Contraseñas no coinciden";
        } else if (strlen($clave) < 6) {
            $mensaje = "La contraseña debe tener al menos 6 caracteres";
            $tipo_mensaje = "danger";
            $debug_info['error'] = "Contraseña muy corta";
        } else if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            $mensaje = "El correo electrónico no es válido";
            $tipo_mensaje = "danger";
            $debug_info['error'] = "Email inválido";
        } else {
            // Intentar registrar al usuario
            $debug_info['pre_registro'] = "Intentando registrar usuario";
            if ($usuario->registrar($nombre, $correo, $clave, $nombre_completo)) {
                $mensaje = "Registro exitoso. Ahora puedes iniciar sesión.";
                $tipo_mensaje = "success";
                $debug_info['registro'] = "Exitoso";
                
                // Limpiar los campos del formulario después de un registro exitoso
                $nombre = $correo = $nombre_completo = '';
                
                // Redireccionar después de 3 segundos
                header("refresh:3;url=login.php");
            } else {
                $mensaje = "El nombre de usuario o correo ya está en uso";
                $tipo_mensaje = "danger";
                $debug_info['error'] = "Usuario o correo ya existe";
            }
        }
    }
}

$p->cabeza("Registro de Usuario");
$p->menu();
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="text-center">Registro de Usuario</h3>
                </div>
                <div class="card-body">
                    <?php if (!empty($mensaje)): ?>
                        <div class="alert alert-<?php echo $tipo_mensaje; ?>"><?php echo htmlspecialchars($mensaje); ?></div>
                    <?php endif; ?>
                    
                    <form action="registro.php" method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                        <div class="mb-3">
                            <label for="nombre_completo" class="form-label">Nombre Completo:</label>
                            <input type="text" class="form-control" id="nombre_completo" name="nombre_completo" value="<?php echo isset($nombre_completo) ? htmlspecialchars($nombre_completo) : ''; ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="usuario" class="form-label">Nombre de Usuario:</label>
                            <input type="text" class="form-control" id="usuario" name="usuario" value="<?php echo isset($nombre) ? htmlspecialchars($nombre) : ''; ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="correo" class="form-label">Correo Electrónico:</label>
                            <input type="email" class="form-control" id="correo" name="correo" value="<?php echo isset($correo) ? htmlspecialchars($correo) : ''; ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="clave" class="form-label">Contraseña:</label>
                            <input type="password" class="form-control" id="clave" name="clave" required>
                            <small class="form-text text-muted">La contraseña debe tener al menos 6 caracteres.</small>
                        </div>
                        <div class="mb-3">
                            <label for="confirmar_clave" class="form-label">Confirmar Contraseña:</label>
                            <input type="password" class="form-control" id="confirmar_clave" name="confirmar_clave" required>
                        </div>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Registrarse</button>
                        </div>
                    </form>
                    
                    <div class="mt-3 text-center">
                        <p>¿Ya tienes una cuenta? <a href="login.php">Inicia sesión aquí</a></p>
                    </div>
                </div>
            </div>
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
