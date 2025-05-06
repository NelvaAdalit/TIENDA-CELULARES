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

// Procesar formulario
$mensaje = "";
$tipo_mensaje = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Aquí puedes implementar la lógica para guardar la configuración
    // Por ejemplo, actualizar información de la tienda, configuración de correo, etc.
    
    $mensaje = "Configuración guardada correctamente.";
    $tipo_mensaje = "success";
}

$p->cabeza("Configuración del Sistema");
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
                    <a class="nav-link" href="ventas.php">Ventas</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="configuracion.php">Configuración</a>
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
    <h1 class="mb-4">Configuración del Sistema</h1>
    
    <?php if (!empty($mensaje)): ?>
        <div class="alert alert-<?php echo $tipo_mensaje; ?>"><?php echo htmlspecialchars($mensaje); ?></div>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-body">
            <form method="POST">
                <div class="mb-3">
                    <h5>Información de la Tienda</h5>
                    <hr>
                    <div class="mb-3">
                        <label for="nombre_tienda" class="form-label">Nombre de la Tienda:</label>
                        <input type="text" class="form-control" id="nombre_tienda" name="nombre_tienda" value="TiendaCelulares">
                    </div>
                    <div class="mb-3">
                        <label for="direccion" class="form-label">Dirección:</label>
                        <input type="text" class="form-control" id="direccion" name="direccion" value="Av. Principal #123">
                    </div>
                    <div class="mb-3">
                        <label for="telefono" class="form-label">Teléfono:</label>
                        <input type="text" class="form-control" id="telefono" name="telefono" value="+591 12345678">
                    </div>
                    <div class="mb-3">
                        <label for="correo" class="form-label">Correo Electrónico:</label>
                        <input type="email" class="form-control" id="correo" name="correo" value="info@tiendacelulares.com">
                    </div>
                </div>
                
                <div class="mb-3">
                    <h5>Configuración de Correo</h5>
                    <hr>
                    <div class="mb-3">
                        <label for="smtp_host" class="form-label">Servidor SMTP:</label>
                        <input type="text" class="form-control" id="smtp_host" name="smtp_host" value="smtp.example.com">
                    </div>
                    <div class="mb-3">
                        <label for="smtp_puerto" class="form-label">Puerto SMTP:</label>
                        <input type="text" class="form-control" id="smtp_puerto" name="smtp_puerto" value="587">
                    </div>
                    <div class="mb-3">
                        <label for="smtp_usuario" class="form-label">Usuario SMTP:</label>
                        <input type="text" class="form-control" id="smtp_usuario" name="smtp_usuario" value="usuario@example.com">
                    </div>
                    <div class="mb-3">
                        <label for="smtp_clave" class="form-label">Contraseña SMTP:</label>
                        <input type="password" class="form-control" id="smtp_clave" name="smtp_clave" value="********">
                    </div>
                </div>
                
                <div class="mb-3">
                    <h5>Opciones de Seguridad</h5>
                    <hr>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="registro_abierto" name="registro_abierto" checked>
                        <label class="form-check-label" for="registro_abierto">
                            Permitir registro de nuevos usuarios
                        </label>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="captcha" name="captcha">
                        <label class="form-check-label" for="captcha">
                            Habilitar CAPTCHA en formularios
                        </label>
                    </div>
                    <div class="mb-3">
                        <label for="intentos_login" class="form-label">Intentos de login antes de bloqueo:</label>
                        <input type="number" class="form-control" id="intentos_login" name="intentos_login" value="5" min="1" max="10">
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary">Guardar Configuración</button>
            </form>
        </div>
    </div>
</div>

<?php
$p->pie();
?>
