<?php
// Habilitar registro de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// admin/activar_usuario.php
include("../verificar_acceso.php");

// Verificar que el usuario tenga permiso para esta página
verificarPermiso('gestionar_usuarios');

include("../BaseDatos.php");
include("../Usuario.php");

$bd = new BaseDatos();
$usuario = new Usuario($bd);

// Obtener ID del usuario a activar
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    header("Location: usuarios.php");
    exit;
}

// Activar usuario
$usuario->cambiarEstado($id, 1);

// Redirigir a la lista de usuarios
header("Location: usuarios.php?mensaje=Usuario activado correctamente");
exit;
?>
