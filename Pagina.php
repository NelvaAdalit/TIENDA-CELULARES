<?php
class Pagina {
    public function cabeza($titulo = "Sin titulo") {
        echo "<!doctype html>
                <html lang='es'>
                <head>
                    <meta charset='utf-8'>
                    <meta name='viewport' content='width=device-width, initial-scale=1'>
                    <title>" . htmlspecialchars($titulo) . "</title>
                    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css' rel='stylesheet' integrity='sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7' crossorigin='anonymous'>
                    <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css'>
                    <style>
                        .debug-info {
                            background-color: #f8f9fa;
                            border: 1px solid #dee2e6;
                            border-radius: 0.25rem;
                            padding: 1rem;
                            margin-bottom: 1rem;
                            font-family: monospace;
                            white-space: pre-wrap;
                        }
                    </style>
                </head>
                <body>";
    }
    
    public function h1($mensaje) {
        echo "<h1>" . htmlspecialchars($mensaje) . "</h1>";
    }
    
    public function br() {
        echo "<br/>";
    }
    
    public function menu($vistaActual = 'inicio') {
        // Obtener información del usuario actual
        $esAdmin = isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] == 1;
        $esVendedor = isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] == 2;
        $estaAutenticado = isset($_SESSION['usuario_id']);
        
        echo "<nav class='navbar navbar-expand-lg bg-body-tertiary'>
                <div class='container-fluid'>
                    <a class='navbar-brand' href='index.php'>TiendaCelulares</a>
                    <button class='navbar-toggler' type='button' data-bs-toggle='collapse' data-bs-target='#navbarSupportedContent' aria-controls='navbarSupportedContent' aria-expanded='false' aria-label='Toggle navigation'>
                        <span class='navbar-toggler-icon'></span>
                    </button>
                    <div class='collapse navbar-collapse' id='navbarSupportedContent'>
                        <ul class='navbar-nav me-auto mb-2 mb-lg-0'>
                            <li class='nav-item'>
                                <a class='nav-link " . ($vistaActual == 'inicio' ? 'active' : '') . "' aria-current='page' href='index.php'>Inicio</a>
                            </li>
                            <li class='nav-item'>
                                <a class='nav-link " . ($vistaActual == 'celulares' ? 'active' : '') . "' href='index.php?vista=celulares'>Celulares</a>
                            </li>";
                            
        // Opciones para administradores
        if ($esAdmin) {
            echo "<li class='nav-item dropdown'>
                    <a class='nav-link dropdown-toggle' href='#' role='button' data-bs-toggle='dropdown' aria-expanded='false'>
                        Administración
                    </a>
                    <ul class='dropdown-menu'>
                        <li><a class='dropdown-item' href='admin/productos.php'>Gestionar Productos</a></li>
                        <li><a class='dropdown-item' href='admin/usuarios.php'>Gestionar Usuarios</a></li>
                        <li><a class='dropdown-item' href='admin/ventas.php'>Reporte de Ventas</a></li>
                        <li><hr class='dropdown-divider'></li>
                        <li><a class='dropdown-item' href='admin/configuracion.php'>Configuración</a></li>
                    </ul>
                </li>";
        }
        
        // Opciones para vendedores
        if ($esVendedor) {
            echo "<li class='nav-item dropdown'>
                    <a class='nav-link dropdown-toggle' href='#' role='button' data-bs-toggle='dropdown' aria-expanded='false'>
                        Ventas
                    </a>
                    <ul class='dropdown-menu'>
                        <li><a class='dropdown-item' href='vendedor/productos.php'>Gestionar Productos</a></li>
                        <li><a class='dropdown-item' href='vendedor/ventas.php'>Historial de Ventas</a></li>
                    </ul>
                </li>";
        }
        
        echo "</ul>";
        
        if ($estaAutenticado) {
            echo "<div class='navbar-nav'>
                    <li class='nav-item dropdown'>
                        <a class='nav-link dropdown-toggle' href='#' role='button' data-bs-toggle='dropdown' aria-expanded='false'>
                            " . htmlspecialchars($_SESSION['usuario_nombre']) . "
                        </a>
                        <ul class='dropdown-menu dropdown-menu-end'>";
            
            // Verificar si el usuario tiene permiso de administración
            if (isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] == 1) {
                echo "<li><a class='dropdown-item' href='admin/index.php'>Panel de Administración</a></li>";
            }
            
            echo "<li><a class='dropdown-item' href='perfil.php'>Mi Perfil</a></li>
                  <li><hr class='dropdown-divider'></li>
                  <li><a class='dropdown-item' href='fin.php'>Cerrar Sesión</a></li>
                </ul>
            </li>
            </div>";
        } else {
            echo "<div class='navbar-nav'>
                    <a class='nav-link' href='login.php'>Iniciar Sesión</a>
                    <a class='nav-link' href='registro.php'>Registrarse</a>
                  </div>";
        }
        
        echo "</div>
            </div>
        </nav>";
    }
    
    public function tarjeta($nombre, $descripcion, $imagen, $precio, $enlace="Ver detalles", $url="#") {
        echo "<div class='card mb-4' style='width: 18rem;'>
                  <img src='imagenes/" . htmlspecialchars($imagen) . "' class='card-img-top' alt='" . htmlspecialchars($nombre) . "' onerror=\"this.src='imagenes/placeholder.png'\">
                  <div class='card-body'>
                    <h5 class='card-title'>" . htmlspecialchars($nombre) . "</h5>
                    <p class='card-text'>" . (isset($descripcion) ? htmlspecialchars($descripcion) : 'Sin descripción disponible') . "</p>
                    <p class='card-text'><strong>Precio: Bs. " . htmlspecialchars($precio) . "</strong></p>
                    <a href='" . htmlspecialchars($url) . "' class='btn btn-primary'>" . htmlspecialchars($enlace) . "</a>
                  </div>
                </div>";
    }
    
    public function mostrarProductos($productos) {
        echo "<div class='row'>";
        foreach ($productos as $producto) {
            echo "<div class='col-md-4 mb-4'>";
            $this->tarjeta(
                $producto['marca'] . ' ' . $producto['modelo'],
                isset($producto['descripcion']) ? $producto['descripcion'] : $producto['marca'] . ' ' . $producto['modelo'],
                $producto['imagen'],
                $producto['precio'],
                "Ver detalles",
                "index.php?vista=detalle&id=" . $producto['id']
            );
            echo "</div>";
        }
        echo "</div>";
    }

    public function mostrarCatalogo($celulares, $marcas, $marcaSeleccionada, $precioMin, $precioMax) {
        echo "<div class='row'>
                <div class='col-md-3'>
                    <div class='card mb-4'>
                        <div class='card-header'>
                            <h5>Filtros</h5>
                        </div>
                        <div class='card-body'>
                            <form action='index.php' method='GET'>
                                <input type='hidden' name='vista' value='celulares'>
                                
                                <div class='mb-3'>
                                    <label for='marca' class='form-label'>Marca:</label>
                                    <select class='form-select' id='marca' name='marca'>
                                        <option value=''>Todas las marcas</option>";
                                        
        foreach ($marcas as $marca) {
            $selected = ($marca['marca'] == $marcaSeleccionada) ? 'selected' : '';
            echo "<option value='" . htmlspecialchars($marca['marca']) . "' $selected>" . htmlspecialchars($marca['marca']) . "</option>";
        }
                                        
        echo "              </select>
                                </div>
                                
                                <div class='mb-3'>
                                    <label for='precio_min' class='form-label'>Precio mínimo:</label>
                                    <input type='number' class='form-control' id='precio_min' name='precio_min' value='$precioMin' min='0'>
                                </div>
                                
                                <div class='mb-3'>
                                    <label for='precio_max' class='form-label'>Precio máximo:</label>
                                    <input type='number' class='form-control' id='precio_max' name='precio_max' value='$precioMax' min='0'>
                                </div>
                                
                                <button type='submit' class='btn btn-primary'>Filtrar</button>
                                <a href='index.php?vista=celulares' class='btn btn-secondary'>Limpiar</a>
                            </form>
                        </div>
                    </div>
                </div>
                
                <div class='col-md-9'>
                    <h2>Catálogo de Celulares</h2>";
                    
        if (empty($celulares)) {
            echo "<div class='alert alert-info'>No se encontraron productos con los filtros seleccionados.</div>";
        } else {
            echo "<div class='row'>";
            foreach ($celulares as $celular) {
                echo "<div class='col-md-4 mb-4'>";
                $this->tarjeta(
                    $celular['marca'] . ' ' . $celular['modelo'],
                    isset($celular['descripcion']) ? $celular['descripcion'] : $celular['marca'] . ' ' . $celular['modelo'],
                    $celular['imagen'],
                    $celular['precio'],
                    "Ver detalles",
                    "index.php?vista=detalle&id=" . $celular['id']
                );
                echo "</div>";
            }
            echo "</div>";
        }
                    
        echo "  </div>
            </div>";
    }

    public function mostrarDetalle($celular) {
        echo "<div class='row'>
                <div class='col-md-6'>
                    <img src='imagenes/" . htmlspecialchars($celular['imagen']) . "' class='img-fluid rounded' alt='" . htmlspecialchars($celular['marca'] . ' ' . $celular['modelo']) . "' onerror=\"this.src='imagenes/placeholder.png'\">
                </div>
                <div class='col-md-6'>
                    <h2>" . htmlspecialchars($celular['marca'] . ' ' . $celular['modelo']) . "</h2>
                    <p class='lead'>" . (isset($celular['descripcion']) ? htmlspecialchars($celular['descripcion']) : 'Sin descripción disponible') . "</p>
                    
                    <div class='mb-3'>
                        <h4>Especificaciones:</h4>
                        <ul class='list-group'>
                            <li class='list-group-item'><strong>Procesador:</strong> " . htmlspecialchars($celular['procesador']) . "</li>
                            <li class='list-group-item'><strong>RAM:</strong> " . htmlspecialchars($celular['ram']) . "</li>
                            <li class='list-group-item'><strong>Almacenamiento:</strong> " . htmlspecialchars($celular['almacenamiento']) . "</li>
                            <li class='list-group-item'><strong>Pantalla:</strong> " . htmlspecialchars($celular['pantalla']) . "</li>
                            <li class='list-group-item'><strong>Cámara:</strong> " . htmlspecialchars($celular['camara']) . "</li>
                            <li class='list-group-item'><strong>Batería:</strong> " . htmlspecialchars($celular['bateria']) . "</li>
                        </ul>
                    </div>
                    
                    <div class='mb-3'>
                        <h3>Precio: Bs. " . htmlspecialchars($celular['precio']) . "</h3>
                        <p>Stock disponible: " . htmlspecialchars($celular['stock']) . " unidades</p>
                    </div>";
                    
        if ($celular['stock'] > 0) {
            echo "<div class='d-grid gap-2'>
                    <a href='index.php?vista=comprar&id=" . $celular['id'] . "' class='btn btn-primary btn-lg'>Comprar ahora</a>
                    <a href='index.php?vista=celulares' class='btn btn-secondary'>Volver al catálogo</a>
                  </div>";
        } else {
            echo "<div class='alert alert-warning'>Producto agotado</div>
                  <a href='index.php?vista=celulares' class='btn btn-secondary'>Volver al catálogo</a>";
        }
                    
        echo "</div>
            </div>";
    }

    public function mostrarError($mensaje) {
        echo "<div class='alert alert-danger'>
                <h4>Error</h4>
                <p>" . htmlspecialchars($mensaje) . "</p>
                <a href='index.php' class='btn btn-primary'>Volver al inicio</a>
              </div>";
    }

    public function mostrarFormularioCompra($celular, $cantidad, $mensaje = "") {
        // Mostrar mensaje de éxito o error
        if (!empty($mensaje)) {
            $tipo = (strpos($mensaje, 'success:') === 0) ? 'success' : 'danger';
            $texto = (strpos($mensaje, 'success:') === 0) ? substr($mensaje, 8) : $mensaje;
            echo "<div class='alert alert-$tipo'>" . htmlspecialchars($texto) . "</div>";
        }
        
        echo "<div class='row'>
                <div class='col-md-6'>
                    <img src='imagenes/" . htmlspecialchars($celular['imagen']) . "' class='img-fluid rounded' alt='" . htmlspecialchars($celular['marca'] . ' ' . $celular['modelo']) . "' onerror=\"this.src='imagenes/placeholder.png'\">
                    
                    <div class='mt-3'>
                        <h4>" . htmlspecialchars($celular['marca'] . ' ' . $celular['modelo']) . "</h4>
                        <p>" . (isset($celular['descripcion']) ? htmlspecialchars($celular['descripcion']) : 'Sin descripción disponible') . "</p>
                        <h5>Precio: Bs. " . htmlspecialchars($celular['precio']) . "</h5>
                        <p>Stock disponible: " . htmlspecialchars($celular['stock']) . " unidades</p>
                    </div>
                </div>
                
                <div class='col-md-6'>
                    <div class='card'>
                        <div class='card-header'>
                            <h3>Formulario de Compra</h3>
                        </div>
                        <div class='card-body'>
                            <form method='POST'>
                                <div class='mb-3'>
                                    <label for='nombre' class='form-label'>Nombre completo:</label>
                                    <input type='text' class='form-control' id='nombre' name='nombre' required>
                                </div>
                                
                                <div class='mb-3'>
                                    <label for='cantidad' class='form-label'>Cantidad:</label>
                                    <input type='number' class='form-control' id='cantidad' name='cantidad' value='" . $cantidad . "' min='1' max='" . $celular['stock'] . "' required>
                                </div>
                                
                                <div class='mb-3'>
                                    <label for='metodo_pago' class='form-label'>Método de pago:</label>
                                    <select class='form-select' id='metodo_pago' name='metodo_pago' required>
                                        <option value=''>Seleccione un método de pago</option>
                                        <option value='Efectivo'>Efectivo</option>
                                        <option value='Tarjeta de crédito'>Tarjeta de crédito</option>
                                        <option value='Transferencia bancaria'>Transferencia bancaria</option>
                                    </select>
                                </div>
                                
                                <div class='d-grid gap-2'>
                                    <button type='submit' class='btn btn-success btn-lg'>Confirmar Compra</button>
                                    <a href='index.php?vista=detalle&id=" . $celular['id'] . "' class='btn btn-secondary'>Volver a detalles</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>";
    }

    public function mostrarCompras($compras) {
        echo "<h2>Historial de Compras</h2>";
        
        if (empty($compras)) {
            echo "<div class='alert alert-info'>No hay compras registradas.</div>";
        } else {
            echo "<div class='table-responsive'>
                    <table class='table table-striped'>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Producto</th>
                                <th>Cliente</th>
                                <th>Cantidad</th>
                                <th>Método de Pago</th>
                                <th>Fecha</th>
                            </tr>
                        </thead>
                        <tbody>";
                        
            foreach ($compras as $compra) {
                echo "<tr>
                        <td>" . $compra['id'] . "</td>
                        <td>" . htmlspecialchars($compra['producto']) . "</td>
                        <td>" . htmlspecialchars($compra['nombre_cliente']) . "</td>
                        <td>" . $compra['cantidad'] . "</td>
                        <td>" . htmlspecialchars($compra['metodo_pago']) . "</td>
                        <td>" . $compra['fecha_compra'] . "</td>
                      </tr>";
            }
                        
            echo "  </tbody>
                  </table>
                </div>";
        }
    }

    public function mostrarResultadosBusqueda($q, $resultados) {
        echo "<h2>Resultados de búsqueda para: \"" . htmlspecialchars($q) . "\"</h2>";
        
        if (empty($resultados)) {
            echo "<div class='alert alert-info'>No se encontraron resultados para su búsqueda.</div>";
        } else {
            echo "<p>Se encontraron " . count($resultados) . " resultados.</p>";
            echo "<div class='row'>";
            foreach ($resultados as $celular) {
                echo "<div class='col-md-4 mb-4'>";
                $this->tarjeta(
                    $celular['marca'] . ' ' . $celular['modelo'],
                    $celular['descripcion'],
                    $celular['imagen'],
                    $celular['precio'],
                    "Ver detalles",
                    "index.php?vista=detalle&id=" . $celular['id']
                );
                echo "</div>";
            }
            echo "</div>";
        }
    }
    
    // Resto de métodos de la clase Pagina...
    
    public function inicioContenedor() {
        echo "<div class='container mt-4'>";
    }
    
    public function finContenedor() {
        echo "</div>"; // Cierre del container
    }
    
    public function pie() {
        echo "<footer class='bg-dark text-white text-center py-3 mt-5'>
                <div class='container'>
                    <p>© " . date('Y') . " TiendaCelulares. Todos los derechos reservados.</p>
                </div>
              </footer>
              <script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js' integrity='sha384-k6d4wzSIapyDyv1kpU366/PK5hCdSbCRGRCMv+eplOQJWyd1fbcAu9OCUj5zNLiq' crossorigin='anonymous'></script>
                  </body>
                </html>";
    }
    
    // Método para mostrar información de depuración
    public function mostrarDebug($info) {
        echo "<div class='debug-info'>";
        echo htmlspecialchars(print_r($info, true));
        echo "</div>";
    }
}
?>
