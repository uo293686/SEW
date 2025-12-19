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
$error = "";
$usuario_id = null;
$estado = 0;

if (isset($_SESSION['usuario_id'])) {
    $usuario_id = $_SESSION['usuario_id'];
    if (isset($_SESSION['cronometro'])) {
        $estado = 1;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'registrar_usuario') {
    $valido = true;
    
    $campos_requeridos = ['profesion', 'edad', 'genero', 'pericia_informatica'];
    foreach ($campos_requeridos as $campo) {
        if (empty($_POST[$campo])) {
            $valido = false;
            $error = "Por favor, completa todos los campos requeridos.";
            break;
        }
    }
    
    if ($valido) {
        $profesion = trim($_POST['profesion']);
        $edad = intval($_POST['edad']);
        $genero = $_POST['genero'];
        $pericia = $_POST['pericia_informatica'];
        
        $generos_validos = ['Masculino', 'Femenino', 'Otro'];
        $pericias_validas = ['Baja', 'Media', 'Alta'];
        
        if (!in_array($genero, $generos_validos) || !in_array($pericia, $pericias_validas)) {
            $error = "Valores no válidos seleccionados.";
        } elseif ($edad <= 0 || $edad > 120) {
            $error = "Edad no válida.";
        } else {
            $stmt = $conn->prepare("INSERT INTO Usuarios (profesion, edad, genero, pericia_informatica) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("siss", $profesion, $edad, $genero, $pericia);
            
            if ($stmt->execute()) {
                $usuario_id = $conn->insert_id;
                $_SESSION['usuario_id'] = $usuario_id;
                
                $_SESSION['cronometro'] = new Cronometro();
                $_SESSION['cronometro']->arrancar();
                
                $estado = 1;
            } else {
                $error = "Error al registrar usuario.";
            }
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'responder_preguntas') {
    if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['cronometro'])) {
        $error = "Sesión inválida. Por favor, comienza de nuevo.";
    } else {
        $valido = true;
        $respuestas = [];

        for ($i = 1; $i <= 10; $i++) {
            $campo = "pregunta$i";
            if (empty($_POST[$campo])) {
                $valido = false;
                $error = "Por favor, responde todas las preguntas antes de continuar.";
                break;
            }
            $respuestas[$campo] = $_POST[$campo];
        }

        if ($valido) {
            $_SESSION['respuestas'] = $respuestas;
            $estado = 2;
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'finalizar_prueba') {
    if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['cronometro']) || !isset($_SESSION['respuestas'])) {
        $error = "Datos incompletos. Por favor, comienza de nuevo.";
    } else {
        $_SESSION['cronometro']->parar();
        $tiempo = $_SESSION['cronometro']->mostrar();
        
        $comentario = $_POST['comentario'] ?? "";
        $propuesta = $_POST['propuesta'] ?? "";
        $valoracion = $_POST['valoracion'] ?? 0;
        
        $valoracion = intval($valoracion);
        if ($valoracion < 0 || $valoracion > 10) {
            $valoracion = 0;
        }
        
        $dispositivo = "Ordenador";
        
        $conn->begin_transaction();
        
        try {
            $stmt = $conn->prepare("INSERT INTO Resultados_Test 
                (usuario_id, dispositivo, tiempo_segundos, tarea_completada, 
                 comentarios, propuestas_mejora, valoracion) 
                VALUES (?, ?, ?, 1, ?, ?, ?)");
            $stmt->bind_param("isissi", 
                $_SESSION['usuario_id'], 
                $dispositivo, 
                $tiempo,
                $comentario, 
                $propuesta, 
                $valoracion
            );
            $stmt->execute();
            
            if (!empty($comentario)) {
                $stmt2 = $conn->prepare("INSERT INTO Observaciones_Facilitador 
                    (usuario_id, comentarios) VALUES (?, ?)");
                $stmt2->bind_param("is", $_SESSION['usuario_id'], $comentario);
                $stmt2->execute();
            }
            
            $conn->commit();
            
            $mensaje = "¡Prueba completada! Datos guardados correctamente.";
            
            unset($_SESSION['cronometro']);
            unset($_SESSION['respuestas']);
            unset($_SESSION['usuario_id']);
            
            $estado = 3;
            
        } catch (Exception $e) {
            $conn->rollback();
            $error = "Error al guardar los resultados. Por favor, inténtalo de nuevo.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name = "author" content = "Sergio Blanco García"/>
    <title>Prueba de Usabilidad</title>
    <link rel="stylesheet" type="text/css" href = "estilo/estilo.css"/>
    <link rel="stylesheet" type="text/css" href = "estilo/layout.css"/>
</head>
<body>
    <h2>Prueba de Usabilidad</h2>
    
    <?php if ($estado === 0): ?>
        <h3>Introduzca sus datos:</h3>
        <form method="post" action="">
            <p>
                <label>Profesión:</label><br>
                <input type="text" name="profesion" required placeholder="Ej: Estudiante, Ingeniero, etc.">
            </p>
            
            <p>
                <label>Edad:</label><br>
                <input type="number" name="edad" min="1" max="120" required>
            </p>
            
            <p>
                <label>Género:</label><br>
                <input type="radio" name="genero" value="Masculino" required> Masculino<br>
                <input type="radio" name="genero" value="Femenino"> Femenino<br>
                <input type="radio" name="genero" value="Otro"> Otro
            </p>
            
            <p>
                <label>Pericia Informática (Cómo de bueno/a eres con ordenadores):</label><br>
                <select name="pericia_informatica" required>
                    <option value="">Selecciona...</option>
                    <option value="Baja">Baja</option>
                    <option value="Media">Media</option>
                    <option value="Alta">Alta</option>
                </select>
            </p>
            
            <p>
                <button type="submit" name="accion" value="registrar_usuario">Comenzar Prueba</button>
            </p>
        </form>
        
    <?php elseif ($estado === 1): ?>
        <h3>Preguntas de la Prueba</h3>
        <p>Cronómetro iniciado. Por favor, responde a todas las preguntas:</p>
        
        <form method="post" action="">
            <p>
                <label>1. ¿De dónde es el piloto Fabio Quartararo?</label><br>
                <input type="text" name="pregunta1" required>
            </p>
            
            <p>
                <label>2. ¿En qué año nació?</label><br>
                <input type="text" name="pregunta2" required>
            </p>
            
            <p>
                <label>3. ¿Qué moto usó en 2022?</label><br>
                <input type="text" name="pregunta3" required>
            </p>
            
            <p>
                <label>4. ¿Qué tamaño tiene el tablero del juego de memoria con cartas?</label><br>
                <input type="text" name="pregunta4" required>
            </p>
            
            <p>
                <label>5. ¿Cuál es el nombre del circuito del que se habla en la página?</label><br>
                <input type="text" name="pregunta5" required>
            </p>
            
            <p>
                <label>6. ¿Cuáles fueron los días en los que se llevó a cabo el entrenamiento de la carrera de este año en el circuito?</label><br>
                <input type="text" name="pregunta6" required>
            </p>
            
            <p>
                <label>7. ¿Quién fue el segundo en la clasificación global tras la carrera en ese circuito?</label><br>
                <input type="text" name="pregunta7" required>
            </p>
            
            <p>
                <label>8. ¿Cuál fue el tiempo del corredor que quedó primero este año en la carrera?</label><br>
                <input type="text" name="pregunta8" required>
            </p>
            
            <p>
                <label>9. ¿Qué es una chicane?</label><br>
                <input type="text" name="pregunta9" required>
            </p>
            
            <p>
                <label>10. ¿Cuál es el dorsal que este año usó Fabio Quartararo?</label><br>
                <input type="text" name="pregunta10" required>
            </p>
            
            <p>
                <button type="submit" name="accion" value="responder_preguntas">Continuar a Valoración</button>
            </p>
        </form>
        
    <?php elseif ($estado === 2): ?>
        <h3>Valoración y Comentarios</h3>
        <p>Por favor, valora tu experiencia y proporciona cualquier comentario o sugerencia:</p>
        
        <form method="post" action="">
            <p>
                <label>Valoración de la página web (0-10):</label><br>
                <input type="number" name="valoracion" min="0" max="10" required>
            </p>
            
            <p>
                <label>Propuestas de mejora:</label><br>
                <textarea name="propuesta" rows="4" cols="50"></textarea>
            </p>
            
            <p>
                <label>Comentarios del observador:</label><br>
                <textarea name="comentario" rows="4" cols="50"></textarea>
            </p>
            
            <p>
                <button type="submit" name="accion" value="finalizar_prueba">Terminar Prueba</button>
            </p>
        </form>
        
    <?php elseif ($estado === 3): ?>
        <p><?php echo htmlspecialchars($mensaje); ?></p>
        <p><a href="<?php echo $_SERVER['PHP_SELF']; ?>">Realizar otra prueba</a></p>
    <?php endif; ?>
</body>
</html>