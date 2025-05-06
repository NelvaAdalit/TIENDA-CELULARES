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

// Obtener la vista solicitada (por defecto 'inicio')
$vista = isset($_GET['vista']) ? filter_var($_GET['vista'], FILTER_SANITIZE_FULL_SPECIAL_CHARS) : 'inicio';

// Crear instancia de la página
$p = new Pagina();
$bd = new BaseDatos();

// Estructura común para todas las páginas
$p->cabeza("Tienda de Celulares - " . ucfirst($vista));
$p->menu($vista); // Pasamos la vista actual para marcar el ítem activo en el menú
$p->inicioContenedor();

// Cargar la vista solicitada
switch ($vista) {
    case 'inicio':
        // Obtener productos para la página de inicio
        $productos = $bd->getDatosParametrizados(
            "SELECT * FROM celulares ORDER BY fecha_agregado DESC LIMIT 6", 
            []
        );
        
        // Mostrar contenido de la página de inicio
        echo "<h1 class='mb-4'>Bienvenido a nuestra Tienda de Celulares</h1>";
        
        echo "<div class='alert alert-info mb-4'>
                <h4>Ofertas Especiales</h4>
                <p>Descubre nuestras últimas novedades en tecnología móvil. Tenemos los mejores precios del mercado.</p>
              </div>";
        
        echo "<h2 class='mb-3'>Productos Destacados</h2>";
        $p->mostrarProductos($productos);
        break;
        
    case 'celulares':
        // Filtros
        $marca = isset($_GET['marca']) ? filter_var($_GET['marca'], FILTER_SANITIZE_FULL_SPECIAL_CHARS) : '';
        $precio_min = isset($_GET['precio_min']) ? filter_var($_GET['precio_min'], FILTER_VALIDATE_FLOAT) : 0;
        $precio_max = isset($_GET['precio_max']) ? filter_var($_GET['precio_max'], FILTER_VALIDATE_FLOAT) : 10000;
        
        // Consulta con filtros parametrizada
        $params = [$precio_min, $precio_max];
        $sql = "SELECT * FROM celulares WHERE precio >= ? AND precio <= ?";
        
        if (!empty($marca)) {
            $sql .= " AND marca = ?";
            $params[] = $marca;
        }
        
        $sql .= " ORDER BY marca, precio";
        
        $celulares = $bd->getDatosParametrizados($sql, $params);
        $marcas = $bd->getDatosParametrizados("SELECT DISTINCT marca FROM celulares ORDER BY marca", []);
        
        $p->mostrarCatalogo($celulares, $marcas, $marca, $precio_min, $precio_max);
        break;
        
    case 'detalle':
        $id = isset($_GET['id']) ? filter_var($_GET['id'], FILTER_VALIDATE_INT) : 0;
        
        if ($id <= 0) {
            $p->mostrarError("ID de producto no válido");
            break;
        }
        
        $celular = $bd->getDatosParametrizados("SELECT * FROM celulares WHERE id = ?", [$id]);
        
        if (empty($celular)) {
            $p->mostrarError("Producto no encontrado");
            break;
        }
        
        $p->mostrarDetalle($celular[0]);
        break;
        
    case 'comprar':
        $id = isset($_GET['id']) ? filter_var($_GET['id'], FILTER_VALIDATE_INT) : 0;
        $cantidad = isset($_GET['cantidad']) ? filter_var($_GET['cantidad'], FILTER_VALIDATE_INT) : 1;
        
        if ($id <= 0) {
            $p->mostrarError("ID de producto no válido");
            break;
        }
        
        $celular = $bd->getDatosParametrizados("SELECT * FROM celulares WHERE id = ?", [$id]);
        
        if (empty($celular)) {
            $p->mostrarError("Producto no encontrado");
            break;
        }
        
        // Procesar el formulario de compra si se envió
        $mensaje = "";
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nombre = isset($_POST['nombre']) ? filter_var($_POST['nombre'], FILTER_SANITIZE_FULL_SPECIAL_CHARS) : '';
            $metodo = isset($_POST['metodo_pago']) ? filter_var($_POST['metodo_pago'], FILTER_SANITIZE_FULL_SPECIAL_CHARS) : '';
            $cantidad = isset($_POST['cantidad']) ? filter_var($_POST['cantidad'], FILTER_VALIDATE_INT) : 1;
            
            if (empty($nombre) || empty($metodo) || $cantidad <= 0) {
                $mensaje = "Por favor complete todos los campos correctamente.";
            } else if ($cantidad > $celular[0]['stock']) {
                $mensaje = "No hay suficiente stock disponible.";
            } else {
                // Registrar la compra
                $producto = $celular[0]['marca'] . ' ' . $celular[0]['modelo'];
                $sql = "INSERT INTO compras (producto, nombre_cliente, cantidad, metodo_pago) VALUES (?, ?, ?, ?)";
                $params = [$producto, $nombre, $cantidad, $metodo];
                
                if ($bd->ejecutarConsulta($sql, $params)) {
                    // Actualizar el stock
                    $nuevoStock = $celular[0]['stock'] - $cantidad;
                    $bd->ejecutarConsulta("UPDATE celulares SET stock = ? WHERE id = ?", [$nuevoStock, $id]);
                    
                    $mensaje = "success:¡Compra realizada con éxito! Gracias por su compra.";
                    
                    // Redireccionar después de 3 segundos
                    header("refresh:3;url=index.php");
                } else {
                    $mensaje = "Error al procesar la compra. Intente nuevamente.";
                }
            }
        }
        
        $p->mostrarFormularioCompra($celular[0], $cantidad, $mensaje);
        break;
        
    case 'compras':
        // Verificar si el usuario tiene permiso para ver compras
        if (!isset($_SESSION['usuario_rol']) || ($_SESSION['usuario_rol'] != 1 && $_SESSION['usuario_rol'] != 2)) {
            header("Location: accesodenegado.php");
            exit;
        }
        
        $compras = $bd->getDatosParametrizados("SELECT * FROM compras ORDER BY fecha_compra DESC", []);
        $p->mostrarCompras($compras);
        break;
        
    case 'buscar':
        $q = isset($_GET['q']) ? filter_var($_GET['q'], FILTER_SANITIZE_FULL_SPECIAL_CHARS) : '';
        
        $resultados = [];
        if (!empty($q)) {
            $busqueda = "%$q%";
            $resultados = $bd->getDatosParametrizados(
                "SELECT * FROM celulares 
                WHERE marca LIKE ? 
                OR modelo LIKE ? 
                OR procesador LIKE ? 
                OR ram LIKE ? 
                OR almacenamiento LIKE ?
                ORDER BY marca, modelo",
                [$busqueda, $busqueda, $busqueda, $busqueda, $busqueda]
            );
        }
        
        $p->mostrarResultadosBusqueda($q, $resultados);
        break;
        
    default:
        $p->mostrarError("Página no encontrada");
        break;
}

$p->finContenedor();
$p->pie();
?>
