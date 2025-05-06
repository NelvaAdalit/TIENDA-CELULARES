<?php
// Este script añade la columna 'descripcion' a la tabla 'celulares' si no existe
// y actualiza los registros existentes con descripciones predeterminadas

// Incluir archivo de conexión
include("BaseDatos.php");

// Crear instancia de la base de datos
$bd = new BaseDatos();

// Verificar si la columna 'descripcion' existe en la tabla 'celulares'
$columnas = $bd->getDatosParametrizados("SHOW COLUMNS FROM celulares LIKE 'descripcion'", []);

if (empty($columnas)) {
    // La columna no existe, añadirla
    echo "<p>Añadiendo columna 'descripcion' a la tabla 'celulares'...</p>";
    
    $resultado = $bd->ejecutarConsulta("ALTER TABLE celulares ADD COLUMN descripcion TEXT AFTER modelo", []);
    
    if ($resultado) {
        echo "<p>Columna añadida correctamente.</p>";
        
        // Actualizar los registros existentes con descripciones predeterminadas
        $celulares = $bd->getDatosParametrizados("SELECT id, marca, modelo FROM celulares", []);
        
        foreach ($celulares as $celular) {
            $descripcion = "Smartphone " . $celular['marca'] . " " . $celular['modelo'] . " con excelentes características y rendimiento.";
            $bd->ejecutarConsulta(
                "UPDATE celulares SET descripcion = ? WHERE id = ?",
                [$descripcion, $celular['id']]
            );
        }
        
        echo "<p>Registros actualizados con descripciones predeterminadas.</p>";
    } else {
        echo "<p>Error al añadir la columna.</p>";
    }
} else {
    echo "<p>La columna 'descripcion' ya existe en la tabla 'celulares'.</p>";
    
    // Verificar si hay registros sin descripción
    $sinDescripcion = $bd->getDatosParametrizados(
        "SELECT COUNT(*) as total FROM celulares WHERE descripcion IS NULL OR descripcion = ''", 
        []
    );
    
    if ($sinDescripcion[0]['total'] > 0) {
        echo "<p>Actualizando " . $sinDescripcion[0]['total'] . " registros sin descripción...</p>";
        
        $celularesSinDesc = $bd->getDatosParametrizados(
            "SELECT id, marca, modelo FROM celulares WHERE descripcion IS NULL OR descripcion = ''", 
            []
        );
        
        foreach ($celularesSinDesc as $celular) {
            $descripcion = "Smartphone " . $celular['marca'] . " " . $celular['modelo'] . " con excelentes características y rendimiento.";
            $bd->ejecutarConsulta(
                "UPDATE celulares SET descripcion = ? WHERE id = ?",
                [$descripcion, $celular['id']]
            );
        }
        
        echo "<p>Registros actualizados correctamente.</p>";
    } else {
        echo "<p>Todos los registros ya tienen descripción.</p>";
    }
}

echo "<p>Proceso completado. <a href='index.php'>Volver al inicio</a></p>";
?>
