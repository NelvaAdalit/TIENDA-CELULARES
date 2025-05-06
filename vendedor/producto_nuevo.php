<?php
// Habilitar registro de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Iniciar sesión
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Verificar si el usuario está autenticado y es vendedor
if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] != 2) {
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
    // Obtener datos del formulario
    $marca = isset($_POST['marca']) ? trim($_POST['marca']) : '';
    $modelo = isset($_POST['modelo']) ? trim($_POST['modelo']) : '';
    $descripcion = isset($_POST['descripcion']) ? trim($_POST['descripcion']) : '';
    $procesador = isset($_POST['procesador']) ? trim($_POST['procesador']) : '';
    $ram = isset($_POST['ram']) ? trim($_POST['ram']) : '';
    $almacenamiento = isset($_POST['almacenamiento']) ? trim($_POST['almacenamiento']) : '';
    $pantalla = isset($_POST['pantalla']) ? trim($_POST['pantalla']) : '';
    $camara = isset($_POST['camara']) ? trim($_POST['camara']) : '';
    $bateria = isset($_POST['bateria']) ? trim($_POST['bateria']) : '';
    $precio = isset($_POST['precio']) ? filter_var($_POST['precio'], FILTER_VALIDATE_FLOAT) : 0;
    $stock = isset($_POST['stock']) ? filter_var($_POST['stock'], FILTER_VALIDATE_INT) : 0;
    
    // Validaciones
    if (empty($marca) || empty($modelo) || empty($procesador) || empty($ram) || 
        empty($almacenamiento) || empty($camara) || empty($bateria) || 
        $precio <= 0 || $stock < 0) {
        $mensaje = "Por favor complete todos los campos obligatorios correctamente.";
        $tipo_mensaje = "danger";
    } else {
        // Procesar imagen
        $imagen = 'placeholder.png'; // Imagen por defecto
        
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $filename = $_FILES['imagen']['name'];
            $ext = pathinfo($filename, PATHINFO_EXTENSION);
            
            if (in_array(strtolower($ext), $allowed)) {
                // Generar nombre único para la imagen
                $newname = $marca . '_' . $modelo . '_' . uniqid() . '.' . $ext;
                $destination = '../imagenes/' . $newname;
                
                if (move_uploaded_file($_FILES['imagen']['tmp_name'], $destination)) {
                    $imagen = $newname;
                }
            } else {
                $mensaje = "Formato de imagen no válido. Se permiten: jpg, jpeg, png, gif.";
                $tipo_mensaje = "danger";
            }
        }
        
        if (empty($tipo_mensaje)) {
            // Insertar producto en la base de datos
            $sql = "INSERT INTO celulares (marca, modelo, descripcion, procesador, ram, almacenamiento, pantalla, camara, bateria, precio, stock, imagen) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $params = [
                $marca, $modelo, $descripcion, $procesador, $ram, $almacenamiento, 
                $pantalla, $camara, $bateria, $precio, $stock, $imagen
            ];
            
            if ($bd->ejecutarConsulta($sql, $params)) {
                $mensaje = "Producto agregado correctamente.";
                $tipo_mensaje = "success";
                
                // Redireccionar después de 2 segundos
                header("refresh:2;url=productos.php");
            } else {
                $mensaje = "Error al agregar el producto.";
                $tipo_mensaje = "danger";
            }
        }
    }
}

$p->cabeza("Agregar Nuevo Producto");
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-success">
    <div class="container-fluid">
        <a class="navbar-brand" href="index.php">Panel de Vendedor</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarVendedor" aria-controls="navbarVendedor" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarVendedor">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link" href="index.php">Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="productos.php">Productos</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="ventas.php">Ventas</a>
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
    <h1 class="mb-4">Agregar Nuevo Producto</h1>
    
    <?php if (!empty($mensaje)): ?>
        <div class="alert alert-<?php echo $tipo_mensaje; ?>"><?php echo htmlspecialchars($mensaje); ?></div>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-body">
            <form method="POST" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="marca" class="form-label">Marca:</label>
                            <input type="text" class="form-control" id="marca" name="marca" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="modelo" class="form-label">Modelo:</label>
                            <input type="text" class="form-control" id="modelo" name="modelo" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="descripcion" class="form-label">Descripción:</label>
                            <textarea class="form-control" id="descripcion" name="descripcion" rows="3"></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="procesador" class="form-label">Procesador:</label>
                            <input type="text" class="form-control" id="procesador" name="procesador" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="ram" class="form-label">RAM:</label>
                            <input type="text" class="form-control" id="ram" name="ram" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="almacenamiento" class="form-label">Almacenamiento:</label>
                            <input type="text" class="form-control" id="almacenamiento" name="almacenamiento" required>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="pantalla" class="form-label">Pantalla:</label>
                            <input type="text" class="form-control" id="pantalla" name="pantalla" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="camara" class="form-label">Cámara:</label>
                            <input type="text" class="form-control" id="camara" name="camara" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="bateria" class="form-label">Batería:</label>
                            <input type="text" class="form-control" id="bateria" name="bateria" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="precio" class="form-label">Precio (Bs.):</label>
                            <input type="number" class="form-control" id="precio" name="precio" step="0.01" min="0" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="stock" class="form-label">Stock:</label>
                            <input type="number" class="form-control" id="stock" name="stock" min="0" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="imagen" class="form-label">Imagen:</label>
                            <input type="file" class="form-control" id="imagen" name="imagen">
                            <small class="form-text text-muted">Formatos permitidos: JPG, JPEG, PNG, GIF</small>
                        </div>
                    </div>
                </div>
                
                <div class="d-flex justify-content-between">
                    <a href="productos.php" class="btn btn-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-primary">Guardar Producto</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$p->pie();
?>
