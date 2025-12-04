<?php
session_start();

class Cronometro {
    private $tiempo;
    private $inicio;

    public function __construct() {
        $this->tiempo = 0;
        $this->inicio = 0;
    }

    public function arrancar(){
        $this->inicio = time();
    }

    public function parar(){
        if ($this->inicio > 0) {
            $this->tiempo = time() - $this->inicio;
        }
    }

    public function mostrar(){
        return $this->tiempo;
    }
}

$host = "localhost";
$user = "DBUSER2025";
$pass = "DBPSWD2025";
$db   = "UO293686_DB";
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$mensaje = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'iniciar') {
    $_SESSION['cronometro'] = new Cronometro();
    $_SESSION['cronometro']->arrancar();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'terminar') {
    $valido = true;

    for ($i = 1; $i <= 10; $i++) {
        if (empty($_POST["pregunta$i"])) {
            $valido = false;
            break;
        }
    }

    if ($valido) {
        $_SESSION['cronometro']->parar();
        $tiempo = $_SESSION['cronometro']->mostrar();

        $respuestas = "";
        for ($i = 1; $i <= 10; $i++) {
            $respuestas .= "Pregunta $i: " . $_POST["pregunta$i"] . "\n";
        }

        $comentario = $_POST['comentario'] ?? "";
        $propuesta  = $_POST['propuesta'] ?? "";
        $valoracion = $_POST['valoracion'] ?? 0;

        $conn->query("INSERT INTO Usuarios (profesion, edad, genero, pericia_informatica)
                      VALUES ('Estudiante', 22, 'Femenino', 'Media')");
        $usuario_id = $conn->insert_id;

        $dispositivo = "Ordenador";
        $tarea_completada = 1;

        $stmt = $conn->prepare("INSERT INTO Resultados_Test 
            (usuario_id, dispositivo, tiempo_segundos, tarea_completada, comentarios, propuestas_mejora, valoracion) 
            VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isisssi", $usuario_id, $dispositivo, $tiempo, $tarea_completada, $comentario, $propuesta, $valoracion);
        $stmt->execute();

        $mensaje = "¡Prueba completada! Tiempo empleado: $tiempo segundos. Datos guardados correctamente.";
        unset($_SESSION['cronometro']);
    } else {
        $mensaje = "Por favor, responde todas las preguntas antes de terminar la prueba.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Prueba de Usabilidad</title>
</head>
<body>
    <h2>Prueba de Usabilidad</h2>

    <?php if (!isset($_POST['accion']) || $_POST['accion'] === 'iniciar'): ?>
        <?php if (!isset($_POST['accion'])): ?>
            <form method="post" action="">
                <button type="submit" name="accion" value="iniciar">Iniciar prueba</button>
            </form>
        <?php else: ?>
            <form method="post" action="">
                <label>¿De dónde es el piloto Fabio Quartararo</label><input type="text" name="pregunta1" required><br>
                <label>¿En que año nació?</label><input type="text" name="pregunta2" required><br>
                <label>¿Qué moto usó en 2022?</label><input type="text" name="pregunta3" required><br>
                <label>¿Qué tamaño tiene el tablero del juego de memoria con cartas?</label><input type="text" name="pregunta4" required><br>
                <label>¿Cual es el nombre del circuito del que se habla en esta página?</label><input type="text" name="pregunta5" required><br>
                <label>¿Cuales fueron los días en los que se llevó a cabo el entrenamiento en el circuito?</label><input type="text" name="pregunta6" required><br>
                <label>¿Quién fue el segundo en la clasificación tras la carrera en ese circuito?</label><input type="text" name="pregunta7" required><br>
                <label>¿Cual fue el tiempo delcorredor que quedó primero en la carera?</label><input type="text" name="pregunta8" required><br>
                <label>¿Qué es una chicane?</label><input type="text" name="pregunta9" required><br>
                <label>¿Cual es el dorsal de Fabio Quartararo?</label><input type="text" name="pregunta10" required><br>

                <h3>Comentarios del Observador</h3>
                <label>Comentario:</label><br>
                <textarea name="comentario" rows="3" cols="50"></textarea><br>

                <label>Propuesta de mejora:</label><br>
                <textarea name="propuesta" rows="3" cols="50"></textarea><br>

                <label>Valoración (0-10):</label>
                <input type="number" name="valoracion" min="0" max="10"><br>

                <button type="submit" name="accion" value="terminar">Terminar prueba</button>
            </form>
        <?php endif; ?>
    <?php endif; ?>

    <p><?php echo $mensaje; ?></p>
</body>
</html>
