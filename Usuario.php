<?php
class Usuario {
    private $id;
    private $nombre;
    private $correo;
    private $rolId;
    private $nombreCompleto;
    private $permisos = [];
    private $bd;
    
    public function __construct($bd) {
        $this->bd = $bd;
        
        // Si hay una sesión activa, cargar datos del usuario
        if (isset($_SESSION['usuario_id'])) {
            $this->cargarUsuario($_SESSION['usuario_id']);
        }
    }
    
    // Método para iniciar sesión
    public function login($nombre, $clave) {
        // Registrar intento de login
        $ip = $_SERVER['REMOTE_ADDR'];
        
        // Prevenir SQL Injection usando parámetros
        $sql = "SELECT * FROM usuarios WHERE (nombre = ? OR correo = ?) AND activo = TRUE";
        $params = [$nombre, $nombre];
        
        $resultado = $this->bd->getDatosParametrizados($sql, $params);
        
        if (!empty($resultado)) {
            $usuario = $resultado[0];
            
            // Verificar contraseña - primero intentar con password_verify para hashes modernos
            $claveCorrecta = false;
            
            // Verificar si es un hash moderno (comienza con $)
            if (strpos($usuario['clave'], '$') === 0) {
                $claveCorrecta = password_verify($clave, $usuario['clave']);
            } else {
                // Es un hash SHA-1 antiguo
                $claveCorrecta = (sha1($clave) === $usuario['clave']);
                
                // Si la contraseña es correcta, actualizar al nuevo formato de hash
                if ($claveCorrecta) {
                    $nuevoHash = password_hash($clave, PASSWORD_DEFAULT);
                    $this->bd->ejecutarConsulta(
                        "UPDATE usuarios SET clave = ? WHERE id = ?",
                        [$nuevoHash, $usuario['id']]
                    );
                }
            }
            
            if ($claveCorrecta) {
                // Login exitoso
                $this->id = $usuario['id'];
                $this->nombre = $usuario['nombre'];
                $this->correo = $usuario['correo'];
                $this->rolId = $usuario['rol_id'];
                $this->nombreCompleto = $usuario['nombre_completo'];
                
                // Regenerar ID de sesión para prevenir ataques de fijación
                session_regenerate_id(true);
                
                // Guardar en sesión
                $_SESSION['usuario_id'] = $this->id;
                $_SESSION['usuario_nombre'] = $this->nombre;
                $_SESSION['usuario_rol'] = $this->rolId;
                $_SESSION['ultimo_acceso'] = time();
                
                // Cargar permisos
                $this->cargarPermisos();
                
                // Actualizar último acceso
                $this->bd->ejecutarConsulta(
                    "UPDATE usuarios SET ultimo_acceso = NOW() WHERE id = ?", 
                    [$this->id]
                );
                
                // Registrar login exitoso
                $this->bd->ejecutarConsulta(
                    "INSERT INTO intentos_login (usuario_id, ip, exitoso) VALUES (?, ?, TRUE)",
                    [$this->id, $ip]
                );
                
                return true;
            }
        }
        
        // Registrar intento fallido
        $usuarioId = null;
        if (!empty($resultado)) {
            $usuarioId = $resultado[0]['id'];
        }
        
        $this->bd->ejecutarConsulta(
            "INSERT INTO intentos_login (usuario_id, ip, exitoso) VALUES (?, ?, FALSE)",
            [$usuarioId, $ip]
        );
        
        return false;
    }
    
    // Método para cerrar sesión
    public function logout() {
        // Destruir variables de sesión
        unset($_SESSION['usuario_id']);
        unset($_SESSION['usuario_nombre']);
        unset($_SESSION['usuario_rol']);
        unset($_SESSION['ultimo_acceso']);
        unset($_SESSION['csrf_token']);
        
        // Limpiar propiedades
        $this->id = null;
        $this->nombre = null;
        $this->correo = null;
        $this->rolId = null;
        $this->nombreCompleto = null;
        $this->permisos = [];
        
        return true;
    }
    
    // Método para verificar si el usuario está autenticado
    public function estaAutenticado() {
        return isset($_SESSION['usuario_id']) && isset($this->id) && $this->id !== null;
    }
    
    // Método para verificar si el usuario tiene un permiso específico
    public function tienePermiso($permiso) {
        return in_array($permiso, $this->permisos);
    }
    
    // Método para verificar si el usuario tiene un rol específico
    public function tieneRol($rolId) {
        return $this->rolId == $rolId;
    }
    
    // Método para cargar datos del usuario desde la BD
    private function cargarUsuario($id) {
        $sql = "SELECT * FROM usuarios WHERE id = ? AND activo = TRUE";
        $resultado = $this->bd->getDatosParametrizados($sql, [$id]);
        
        if (!empty($resultado)) {
            $usuario = $resultado[0];
            $this->id = $usuario['id'];
            $this->nombre = $usuario['nombre'];
            $this->correo = $usuario['correo'];
            $this->rolId = $usuario['rol_id'];
            $this->nombreCompleto = $usuario['nombre_completo'];
            
            // Cargar permisos
            $this->cargarPermisos();
            
            return true;
        }
        
        return false;
    }
    
    // Método para cargar los permisos del usuario
    private function cargarPermisos() {
        if (!isset($this->rolId)) {
            return;
        }
        
        $sql = "SELECT p.nombre FROM permisos p 
                JOIN rol_permiso rp ON p.id = rp.permiso_id 
                WHERE rp.rol_id = ?";
        
        $resultado = $this->bd->getDatosParametrizados($sql, [$this->rolId]);
        
        $this->permisos = [];
        foreach ($resultado as $permiso) {
            $this->permisos[] = $permiso['nombre'];
        }
    }
    
    // Método para registrar un nuevo usuario
    public function registrar($nombre, $correo, $clave, $nombreCompleto, $rolId = 3) {
        // Verificar si el usuario ya existe
        $sql = "SELECT id FROM usuarios WHERE nombre = ? OR correo = ?";
        $resultado = $this->bd->getDatosParametrizados($sql, [$nombre, $correo]);
        
        if (!empty($resultado)) {
            return false; // Usuario o correo ya existe
        }
        
        // Crear hash seguro de la contraseña
        $hash = password_hash($clave, PASSWORD_DEFAULT);
        
        // Insertar nuevo usuario
        $sql = "INSERT INTO usuarios (nombre, correo, clave, nombre_completo, rol_id, activo) 
                VALUES (?, ?, ?, ?, ?, 1)";
        
        $exito = $this->bd->ejecutarConsulta($sql, [
            $nombre, 
            $correo, 
            $hash, 
            $nombreCompleto, 
            $rolId
        ]);
        
        if ($exito) {
            error_log("Usuario registrado correctamente: $nombre, $correo");
        } else {
            error_log("Error al registrar usuario: $nombre, $correo");
        }
        
        return $exito;
    }
    
    // Método para cambiar la contraseña
    public function cambiarClave($id, $claveActual, $claveNueva) {
        // Verificar la clave actual
        $sql = "SELECT clave FROM usuarios WHERE id = ?";
        $resultado = $this->bd->getDatosParametrizados($sql, [$id]);
        
        if (empty($resultado)) {
            return false;
        }
        
        $claveAlmacenada = $resultado[0]['clave'];
        
        // Verificar si es un hash SHA-1 antiguo o un hash moderno
        $esValida = false;
        
        if (preg_match('/^[0-9a-f]{40}$/i', $claveAlmacenada)) {
            // Es un hash SHA-1
            $esValida = (sha1($claveActual) === $claveAlmacenada);
        } else {
            // Es un hash moderno
            $esValida = password_verify($claveActual, $claveAlmacenada);
        }
        
        if (!$esValida) {
            return false;
        }
        
        // Crear nuevo hash y actualizar
        $nuevoHash = password_hash($claveNueva, PASSWORD_DEFAULT);
        $sql = "UPDATE usuarios SET clave = ? WHERE id = ?";
        return $this->bd->ejecutarConsulta($sql, [$nuevoHash, $id]);
    }
    
    // Método para activar/desactivar un usuario
    public function cambiarEstado($id, $activo) {
        $sql = "UPDATE usuarios SET activo = ? WHERE id = ?";
        return $this->bd->ejecutarConsulta($sql, [$activo, $id]);
    }
    
    // Método para obtener información detallada de un usuario
    public function obtenerUsuario($id) {
        $sql = "SELECT u.*, r.nombre as rol_nombre 
                FROM usuarios u 
                JOIN roles r ON u.rol_id = r.id 
                WHERE u.id = ?";
        $resultado = $this->bd->getDatosParametrizados($sql, [$id]);
        
        return !empty($resultado) ? $resultado[0] : null;
    }
    
    // Método para listar todos los usuarios
    public function listarUsuarios() {
        $sql = "SELECT u.*, r.nombre as rol_nombre 
                FROM usuarios u 
                JOIN roles r ON u.rol_id = r.id 
                ORDER BY u.id";
        return $this->bd->getDatosParametrizados($sql, []);
    }
    
    // Getters
    public function getId() {
        return $this->id;
    }
    
    public function getNombre() {
        return $this->nombre;
    }
    
    public function getCorreo() {
        return $this->correo;
    }
    
    public function getRolId() {
        return $this->rolId;
    }
    
    public function getNombreCompleto() {
        return $this->nombreCompleto;
    }
    
    public function getPermisos() {
        return $this->permisos;
    }
}
?>
