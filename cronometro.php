<!DOCTYPE HTML>

<html lang="es">
    <head>
        <!-- Datos que describen el documento -->
        <meta charset="UTF-8" />
        <meta name = "author" content = "Sergio Blanco García"/>
        <meta name = "descripción"  content = "Página de juegos de MotoGP-Desktop"/>
        <meta name = "keywords" content = "cronometro"/>
        <meta name = "viewport" content = "width = device-width, initial-scale = 1.0"/>
        <title>MotoGP-Juegos Cronometro PHP</title>
        <link rel="stylesheet" type="text/css" href = "estilo/estilo.css"/>
        <link rel="stylesheet" type="text/css" href = "estilo/layout.css"/>
    </head>
    <body>
        <header>
            <!-- Datos con el contenidos que aparece en el navegador -->
            <h1><a href="index.html">MotoGP Desktop</a></h1>
            <nav>
                <a href = "index.html">Inicio</a>
                <a href = "piloto.html">Piloto</a>
                <a href = "circuito.html">Circuito</a>
                <a href = "meteorologia.html">Meteorología</a>
                <a href = "clasificaciones.php">Clasificaciones</a>
                <a href = "juegos.html" class = "active">Juegos</a>
                <a href = "ayuda.html">Ayuda</a>
            </nav>
        </header>
    <p>Estás en: <a href="index.html">MotoGP Desktop</a> >> <a href="juegos.html">Juegos</a> >> <strong>Cronómetro PHP</strong></p>

        <main>
            <h2>Pruebas de la clase Cronómetro</h2>
            <form method="post">
                <input type="submit" name="accion" value="Arrancar" />
                <input type="submit" name="accion" value="Parar" />
                <input type="submit" name="accion" value="Mostrar" />
            </form>

            <section>
                <?php
                require_once 'Cronometro.php';
                session_start();
                if (!isset($_SESSION['cronometro'])) {
                    $_SESSION['cronometro'] = new Cronometro();
                }
                $cronometro = $_SESSION['cronometro'];

                if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {
                    switch ($_POST['accion']) {
                        case 'Arrancar':
                            $cronometro->arrancar();
                            echo "<p>Cronómetro arrancado.</p>";
                            break;
                        case 'Parar':
                            $cronometro->parar();
                            echo "<p>Cronómetro parado.</p>";
                            break;
                        case 'Mostrar':
                            echo "<p>Tiempo transcurrido: " . $cronometro->mostrar() . " segundos</p>";
                            break;
                    }
                }
                ?>
            </section>
        </main>
    </body>
</html>

<?php
class Cronometro {
    private $tiempo;
    private $inicio;

    public function __construct() {
        $this->tiempo = 0;
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
?>