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

// Mensaje de error
$error = "";

// Registrar datos para depuración
$debug_info = [];
$debug_info['session'] = $_SESSION;

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Registrar datos POST para depuración
    $debug_info['post'] = $_POST;
    
    $nombre = isset($_POST['usuario']) ? trim($_POST['usuario']) : '';
    $clave = isset($_POST['miclave']) ? $_POST['miclave'] : '';
    
    if (empty($nombre) || empty($clave)) {
        $error = "Por favor ingrese usuario y contraseña";
    } else {
        if ($usuario->login($nombre, $clave)) {
            // Regenerar ID de sesión para prevenir ataques de fijación
            session_regenerate_id(true);
            
            // Redirigir según el rol
            if ($usuario->tieneRol(1)) { // Admin
                header("Location: admin/index.php");
            } else if ($usuario->tieneRol(2)) { // Vendedor
                header("Location: vendedor/index.php");
            } else { // Cliente
                header("Location: index.php");
            }
            exit;
        } else {
            $error = "Usuario o contraseña incorrectos";
        }
    }
}

$p->cabeza("Iniciar Sesión");
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="text-center">Iniciar Sesión</h3>
                </div>
                <div class="card-body">
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>
                    
                    <form action="login.php" method="POST">
                      <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                      <div class="mb-3">
                      <label for="usuario" class="form-label">Usuario:</label>
                    <input type="text" class="form-control" id="usuario" name="usuario" required>
                    </div>
                    <div class="mb-3">
                    <label for="miclave" class="form-label">Contraseña:</label>
                    <input type="password" class="form-control" id="miclave" name="miclave" required>
                     </div>
                    <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary">Iniciar Sesión</button>
                    </div>
                </form>
                    
                    <div class="mt-3 text-center">
                        <p>¿No tienes una cuenta? <a href="registro.php">Regístrate aquí</a></p>
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
