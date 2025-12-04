CREATE DATABASE UO293686_DB;
USE UO293686_DB;

CREATE TABLE Usuarios (
    usuario_id INT PRIMARY KEY AUTO_INCREMENT,
    profesion VARCHAR(100) NOT NULL,
    edad INT NOT NULL,
    genero ENUM('Masculino','Femenino','Otro') NOT NULL,
    pericia_informatica ENUM('Baja','Media','Alta') NOT NULL
);

CREATE TABLE Resultados_Test (
    resultado_id INT PRIMARY KEY AUTO_INCREMENT,
    usuario_id INT NOT NULL,
    dispositivo ENUM('Ordenador','Tableta','Teléfono') NOT NULL,
    tiempo_segundos INT NOT NULL,
    tarea_completada BOOLEAN NOT NULL,
    comentarios TEXT,
    propuestas_mejora TEXT,
    valoracion INT, CHECK (valoracion BETWEEN 0 AND 10),
    FOREIGN KEY (usuario_id) REFERENCES Usuarios(usuario_id)
);

CREATE TABLE Observaciones_Facilitador (
    observacion_id INT PRIMARY KEY AUTO_INCREMENT,
    usuario_id INT NOT NULL,
    comentarios TEXT NOT NULL,
    FOREIGN KEY (usuario_id) REFERENCES Usuarios(usuario_id)
);
