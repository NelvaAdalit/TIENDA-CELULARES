<?php
// Iniciar sesión
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include("BaseDatos.php");
include("Usuario.php");

$bd = new BaseDatos();
$usuario = new Usuario($bd);

// Cerrar sesión
$usuario->logout();

// Destruir la sesión
session_destroy();

// Redirigir al login
header("Location: login.php");
exit;
?>
