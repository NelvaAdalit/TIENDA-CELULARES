<?php
// verificar_acceso.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Verificar tiempo de inactividad (30 minutos)
$tiempo_inactividad = 1800; // 30 minutos en segundos
if (isset($_SESSION['ultimo_acceso'])) {
    $tiempo_transcurrido = time() - $_SESSION['ultimo_acceso'];
    if ($tiempo_transcurrido > $tiempo_inactividad) {
        // La sesión ha expirado
        session_unset();
        session_destroy();
        header("Location: login.php?error=Su sesión ha expirado por inactividad");
        exit;
    }
    // Actualizar tiempo de último acceso
    $_SESSION['ultimo_acceso'] = time();
}

// Función para verificar si el usuario tiene un permiso específico
function verificarPermiso($permiso) {
    // Si no hay sesión activa, redirigir al login
    if (!isset($_SESSION['usuario_id'])) {
        header("Location: login.php?error=Debes iniciar sesión para acceder a esta página");
        exit;
    }
    
    // Incluir archivos necesarios si no están ya incluidos
    if (!class_exists('BaseDatos')) {
        include_once(__DIR__ . "/BaseDatos.php");
    }
    
    if (!class_exists('Usuario')) {
        include_once(__DIR__ . "/Usuario.php");
    }
    
    // Crear instancias
    $bd = new BaseDatos();
    $usuario = new Usuario($bd);
    
    // Verificar si el usuario tiene el permiso requerido
    if (!$usuario->tienePermiso($permiso)) {
        header("Location: accesodenegado.php");
        exit;
    }
    
    // Si llegamos aquí, el usuario tiene permiso
    return true;
}

// Función para generar token CSRF
function generarTokenCSRF() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Función para verificar token CSRF
function verificarTokenCSRF($token) {
    if (!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
        return false;
    }
    return true;
}

// Función para depuración
function debug($data) {
    echo "<pre>";
    print_r($data);
    echo "</pre>";
}
?>
