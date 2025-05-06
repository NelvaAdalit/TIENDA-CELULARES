<?php
class BaseDatos {
    private $conexion;
    
    public function __construct() {
        try {
            // Idealmente, estas credenciales deberían estar en un archivo de configuración
            $host = "localhost";
            $dbname = "tienda";
            $port = "3307";
            $user = "root";
            $password = "";
            
            $this->conexion = new PDO("mysql:host=$host;dbname=$dbname;port=$port", $user, $password);
            $this->conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conexion->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            $this->conexion->exec("SET NAMES utf8mb4");
        } catch(PDOException $e) {
            error_log("Error de conexión: " . $e->getMessage());
            die("Error de conexión: " . $e->getMessage());
        }
    }

    // Método para consultas parametrizadas (SELECT)
    public function getDatosParametrizados($sql, $params = []) {
        try {
            $query = $this->conexion->prepare($sql);
            $query->execute($params);
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Error en la consulta parametrizada: " . $e->getMessage());
            return [];
        }
    }
    
    // Método para ejecutar consultas de inserción, actualización o eliminación
    public function ejecutarConsulta($sql, $params = []) {
        try {
            $query = $this->conexion->prepare($sql);
            return $query->execute($params);
        } catch(PDOException $e) {
            error_log("Error al ejecutar consulta: " . $e->getMessage());
            return false;
        }
    }
    
    // Método para obtener el último ID insertado
    public function ultimoIdInsertado() {
        return $this->conexion->lastInsertId();
    }
    
    // Método para obtener datos sin parametrizar (mantener para compatibilidad)
    public function getDatos($sql) {
        try {
            $query = $this->conexion->prepare($sql);
            $query->execute();
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Error en la consulta: " . $e->getMessage());
            return [];
        }
    }

    // Método para cerrar la conexión
    public function cerrarConexion() {
        $this->conexion = null;
    }
    
    // Método para depuración
    public function debugQuery($sql, $params = []) {
        $keys = array();
        $values = array();
        
        // Construir arrays para reemplazo
        foreach ($params as $key => $value) {
            // Verificar si es un índice numérico o un nombre
            if (is_numeric($key)) {
                $keys[] = '/\?/';
            } else {
                $keys[] = '/:'.$key.'/';
            }
            
            // Añadir comillas si es string
            if (is_string($value)) {
                $values[] = "'" . $value . "'";
            } else if (is_null($value)) {
                $values[] = 'NULL';
            } else {
                $values[] = $value;
            }
        }
        
        // Reemplazar los placeholders con los valores reales
        $query = preg_replace($keys, $values, $sql, 1);
        
        return $query;
    }
}
?>
