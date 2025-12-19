<?php
class Configuracion {
    private $host = "localhost";
    private $usuario = "DBUSER2025";
    private $password = "DBPSWD2025";
    private $db = "UO293686_DB";
    private $conn;

    
    public function __construct() {
        $this->conn = new mysqli($this->host, $this->usuario, $this->password);
        if ($this->conn->connect_error) {
            die("Error de conexión: " . $this->conn->connect_error);
        }
    }

    private function baseDatosExiste() {
        $result = $this->conn->query("SHOW DATABASES LIKE '" . $this->db . "'");
        return $result && $result->num_rows > 0;
    }

    public function reiniciarBD() {
        if (!$this->conn->select_db($this->db)) {
            die("Error: La base de datos no existe. Primero debes inicializarla.");
        }
        
        $tablas = ["Resultados_Test", "Observaciones_Facilitador", "Usuarios"];
        foreach ($tablas as $tabla) {
            $this->conn->query("DELETE FROM $tabla");
        }
    }

    public function eliminarBD() {
        $sql = "DROP DATABASE IF EXISTS " . $this->db;
        if (!$this->conn->query($sql)) {
            die("Error al eliminar la BD: " . $this->conn->error);
        }
    }

    public function inicializarBD() {
        if ($this->baseDatosExiste()) {
            return false;
        }
        
        $sql = "CREATE DATABASE " . $this->db . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
        if (!$this->conn->query($sql)) {
            die("Error al crear la BD: " . $this->conn->error);
        }
        
        $this->conn->select_db($this->db);
        
        $rutaSQL = __DIR__ . "/UO293686_DB.sql";
        $sql = file_get_contents($rutaSQL);
        if ($sql === false) {
            die("No se pudo leer el archivo SQL.");
        }
        
        $sql = preg_replace('/CREATE DATABASE UO293686_DB;?\s*/i', '', $sql);
        $sql = preg_replace('/USE UO293686_DB;?\s*/i', '', $sql);
        
        if ($this->conn->multi_query($sql)) {
            while ($this->conn->more_results()) {
                $this->conn->next_result();
            }
        } else {
            die("Error al ejecutar el script SQL: " . $this->conn->error);
        }
        
        return true;
    }

    public function exportarCSV($rutaArchivo) {
        if (!$this->conn->select_db($this->db)) {
            die("Error: La base de datos no existe. Primero debes inicializarla.");
        }
        
        $sql = "SELECT 
                    u.usuario_id,
                    u.profesion,
                    u.edad,
                    u.genero,
                    u.pericia_informatica,
                    rt.dispositivo,
                    rt.tiempo_segundos,
                    rt.valoracion,
                    rt.propuestas_mejora,
                    of.comentarios as comentario
                FROM Usuarios u
                LEFT JOIN Resultados_Test rt ON u.usuario_id = rt.usuario_id
                LEFT JOIN Observaciones_Facilitador of ON u.usuario_id = of.usuario_id
                ORDER BY u.usuario_id";
        
        $resultado = $this->conn->query($sql);
        if ($resultado) {
            if($resultado->num_rows > 0){
                $fp = fopen($rutaArchivo, 'w');
                
                $cabeceras = [
                    'usuario_id', 
                    'profesion', 
                    'edad', 
                    'genero', 
                    'pericia_informatica', 
                    'dispositivo', 
                    'tiempo_segundos', 
                    'valoracion', 
                    'propuestas_mejora', 
                    'comentario'
                ];
                fputcsv($fp, $cabeceras);
                
                while ($fila = $resultado->fetch_assoc()) {
                    $filaOrdenada = [
                        'usuario_id' => $fila['usuario_id'] ?? '',
                        'profesion' => $fila['profesion'] ?? '',
                        'edad' => $fila['edad'] ?? '',
                        'genero' => $fila['genero'] ?? '',
                        'pericia_informatica' => $fila['pericia_informatica'] ?? '',
                        'dispositivo' => $fila['dispositivo'] ?? '',
                        'tiempo_segundos' => $fila['tiempo_segundos'] ?? '',
                        'valoracion' => $fila['valoracion'] ?? '',
                        'propuestas_mejora' => $fila['propuestas_mejora'] ?? '',
                        'comentario' => $fila['comentario'] ?? ''
                    ];
                    fputcsv($fp, $filaOrdenada);
                }
                fclose($fp);
                
                return [
                    'exito' => true,
                    'registros' => $resultado->num_rows,
                    'archivo' => $rutaArchivo
                ];
            }
            else{
                $fp = fopen($rutaArchivo, 'w');
                $cabeceras = [
                    'usuario_id', 'profesion', 'edad', 'genero', 'pericia_informatica', 
                    'dispositivo', 'tiempo_segundos', 'valoracion', 'propuestas_mejora', 'comentario'
                ];
                fputcsv($fp, $cabeceras);
                fclose($fp);
                
                return [
                    'exito' => true,
                    'registros' => 0,
                    'archivo' => $rutaArchivo,
                    'mensaje' => 'Archivo creado solo con cabeceras (sin datos)'
                ];
            }
        } else {
            die("Error al exportar datos: " . $this->conn->error);
        }
    }
}

$config = new Configuracion();
$mensaje = "";
$exportResultado = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {
    switch ($_POST['accion']) {
        case 'Reiniciar':
            $config->reiniciarBD();
            $mensaje = "Base de datos reiniciada.";
            break;
        case 'Eliminar':
            $config->eliminarBD();
            $mensaje = "Base de datos eliminada.";
            break;
        case 'Inicializar':
            $resultadoInicializar = $config->inicializarBD();
            if ($resultadoInicializar === false) {
                $mensaje = "La base de datos ya existe. No se realizó ninguna acción.";
            } else {
                $mensaje = "Base de datos inicializada desde script.";
            }
            break;
        case 'Exportar':
            $rutaArchivo = __DIR__ . "usuarios" . ".csv";
            $exportResultado = $config->exportarCSV($rutaArchivo);
            if ($exportResultado['exito']) {
                $nombreArchivo = basename($rutaArchivo);
                $mensaje = "Datos exportados a <a href='$nombreArchivo' download>$nombreArchivo</a> (" . $exportResultado['registros'] . " registros).";
            }
            break;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name = "author" content = "Sergio Blanco García"/>
    <title>Configuración BD Usabilidad</title>
    <link rel="stylesheet" type="text/css" href = "estilo/estilo.css"/>
    <link rel="stylesheet" type="text/css" href = "estilo/layout.css"/>
</head>
<body>
    <h2>Configuración de la Base de Datos</h2>
    <form method="post">
        <button type="submit" name="accion" value="Reiniciar">Reiniciar BD</button>
        <button type="submit" name="accion" value="Eliminar">Eliminar BD</button>
        <button type="submit" name="accion" value="Inicializar">Inicializar BD desde script</button>
        <button type="submit" name="accion" value="Exportar">Exportar Datos Completos a CSV</button>
    </form>
    <p><?php echo $mensaje; ?></p>
    
    <?php if ($exportResultado !== null && $exportResultado['exito']): ?>
        <p><strong>Archivo generado:</strong> <?php echo basename($exportResultado['archivo']); ?></p>
        <p><strong>Registros exportados:</strong> <?php echo $exportResultado['registros']; ?></p>
        <?php if (isset($exportResultado['mensaje'])): ?>
            <p><em><?php echo $exportResultado['mensaje']; ?></em></p>
        <?php endif; ?>
    <?php endif; ?>
</body>
</html>