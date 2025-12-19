<!DOCTYPE HTML>

<html lang="es">
<head>
    <!-- Datos que describen el documento -->
    <meta charset="UTF-8" />
    <meta name = "author" content = "Sergio Blanco García"/>
    <meta name = "descripción"  content = "Información acerca de las clasificaciones de MotoGP-Desktop"/>
    <meta name = "keywords" content = "clasificación, clasificaciones"/>
    <meta name = "viewport" content = "width = device-width, initial-scale = 1.0"/>
    <title>MotoGP-Clasificaciones</title>
	<link rel="stylesheet" type="text/css" href = "estilo/estilo.css"/>
    <link rel="stylesheet" type="text/css" href = "estilo/layout.css"/>
    <link rel="icon" href="multimedia/icon.ico"/>
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
        <a href = "clasificaciones.php" class = "active">Clasificaciones</a>
        <a href = "juegos.html">Juegos</a>
        <a href = "ayuda.html">Ayuda</a>
    </nav>
</header>
<p>Estás en: <a href="index.html">MotoGP Desktop</a> >> <strong>Clasificaciones</strong></p>
<main>

    <h2>Clasificaciones de MotoGP Desktop</h2>
    <?php
        $clasificacion = new Clasificacion();
        $clasificacion->consultar();

    ?>
</main>
</body>
</html>

<?php 
class Clasificacion{
    private $documento;

    public function __construct() {
        $this->documento = "xml/circuitoEsquema.xml";
    }

    public function consultar() {
        $datos = file_get_contents($this->documento);
        $datos = preg_replace("/>\s*</", ">\n<", $datos);

        $xml = new SimpleXMLElement($datos);

        echo "<h3>Ganador de la carrera celebrada este año en el Autodromo Internazionale del Mugello:</h3>";
        
        $duracion = $xml->winnerinfo->time;
        $intervalo = new DateInterval($duracion);
        $minutos = $intervalo->i;
        $segundos = $intervalo->s;
        $formato = sprintf("%02d:%02d", $minutos, $segundos);
        echo "<p> {$xml->winnerinfo->winner} con un tiempo de: $formato</p>";

        echo "<h3>Clasificación del mundial:</h3>";
        echo "<ol>";
        foreach ($xml->qualified->driver as $piloto) {
            echo "<li> $piloto </li>";
        }
        echo "</ol>";
    }
}

?>