-- Tablas
--prueba de commit
CREATE TABLE Usuarios(
    rut_usuario VARCHAR(10) PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE
);

CREATE TABLE Ingenieros(
	rut_ingeniero VARCHAR(10) PRIMARY KEY,
    
    FOREIGN KEY (rut_ingeniero) REFERENCES Usuarios(rut_usuario)
);

CREATE TABLE Topicos(
    id_topico INT PRIMARY KEY AUTO_INCREMENT,
    categoria VARCHAR(100) NOT NULL UNIQUE
);

CREATE TABLE Especialidades(
	rut_ingeniero VARCHAR(10) NOT NULL,
    id_topico INT NOT NULL,
    
    PRIMARY KEY (rut_ingeniero, id_topico),
    FOREIGN KEY (rut_ingeniero) REFERENCES Usuarios(rut_usuario),
    FOREIGN KEY (id_topico) REFERENCES Topicos(id_topico)
);
CREATE TABLE Solicitudes(
	id_solicitud INT PRIMARY KEY AUTO_INCREMENT,
    tipo ENUM('Funcionalidad', 'Error') NOT NULL,
    titulo VARCHAR(200) NOT NULL,
    id_topico INT NOT NULL,
    rut_usuario VARCHAR(10) NOT NULL,
    estado ENUM('Abierto', 'En Progreso', 'Resuelto', 'Cerrado') NOT NULL DEFAULT 'Abierto',
    fecha DATE DEFAULT (CURRENT_DATE),
    
    FOREIGN KEY (id_topico) REFERENCES Topicos(id_topico),
  	FOREIGN KEY (rut_usuario) REFERENCES Usuarios(rut_usuario),
    
    UNIQUE KEY unico_titulo (Titulo, Tipo)
);

CREATE TABLE Solicitudes_Funcionalidades(
	id_funcion INT PRIMARY KEY,
    ambiente ENUM('Web','Movil'),
    resumen VARCHAR(150) NOT NULL,
    
    FOREIGN KEY (id_funcion) REFERENCES Solicitudes(id_solicitud)
);

CREATE TABLE Solicitudes_Errores(
	id_error INT PRIMARY KEY,
    descripcion VARCHAR(200) NOT NULL,
    
    FOREIGN KEY (id_error) REFERENCES Solicitudes(id_solicitud)
);

CREATE TABLE Funcionalidad_Aceptada(
	id_criterio INT PRIMARY KEY AUTO_INCREMENT,
    id_funcion INT NOT NULL,
    descripcion TEXT NOT NULL,
    
    FOREIGN KEY (id_funcion) REFERENCES Solicitudes(id_solicitud)
);

CREATE TABLE Asignaciones(
	id_asignacion INT NOT NULL,
    rut_ingeniero VARCHAR(10) NOT NULL,
    
    PRIMARY KEY (id_asignacion, rut_ingeniero),
    FOREIGN KEY (id_asignacion) REFERENCES Solicitudes(id_solicitud),
    FOREIGN KEY (rut_ingeniero) REFERENCES Ingenieros(rut_ingeniero)
);
