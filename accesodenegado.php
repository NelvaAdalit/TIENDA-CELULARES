<?php
include("Pagina.php");

$p = new Pagina();
$p->cabeza("Acceso Denegado");
$p->menu();
?>

<div class="container mt-5">
    <div class="alert alert-danger">
        <h2>Acceso Denegado</h2>
        <p>No tienes permisos para acceder a esta sección.</p>
        <a href="index.php" class="btn btn-primary">Volver al inicio</a>
    </div>
</div>

<?php
$p->pie();
?>
