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
        $this->conn->select_db($this->db);
    }

    public function reiniciarBD() {
        $tablas = ["Resultados_Test", "Observaciones_Facilitador", "Usuarios", "Dispositivos"];
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
        $this->conn->query("DROP DATABASE IF EXISTS " . $this->db);
        $rutaSQL = __DIR__ . "/UO293686_DB.sql";
        $sql = file_get_contents($rutaSQL);
        if ($sql === false) {
            die("No se pudo leer el archivo SQL.");
        }
        if ($this->conn->multi_query($sql)) {
            while ($this->conn->more_results()) {
                $this->conn->next_result();
            }
        } else {
            die("Error al ejecutar el script SQL: " . $this->conn->error);
        }
    }


    public function exportarCSV($tabla, $rutaArchivo) {
        $resultado = $this->conn->query("SELECT * FROM $tabla");
        if ($resultado) {
            if($resultado->num_rows > 0){
                $fp = fopen($rutaArchivo, 'w');
                $cabeceras = array_keys($resultado->fetch_assoc());
                fputcsv($fp, $cabeceras);
                $resultado->data_seek(0);
                while ($fila = $resultado->fetch_assoc()) {
                    fputcsv($fp, $fila);
                }
                fclose($fp);
            }
            else{
                die("La tabla '$tabla' no tiene datos que exportar.");
            }
        } else {
            die("Error al exportar datos: " . $this->conn->error);
        }
    }
}

$config = new Configuracion();
$mensaje = "";

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
            $config->inicializarBD();
            $mensaje = "Base de datos inicializada desde script.";
            break;
        case 'Exportar':
            $config->exportarCSV("Usuarios", __DIR__ . "/usuarios.csv");
            $mensaje = "Datos exportados a usuarios.csv.";
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
        <button type="submit" name="accion" value="Exportar">Exportar Usuarios a CSV</button>
    </form>
    <p><?php echo $mensaje; ?></p>
</body>
</html>
