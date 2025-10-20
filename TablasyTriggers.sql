-- Tablas

CREATE TABLE Usuarios(
    rut_usuario VARCHAR(10) PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    contrasena VARCHAR(200) NOT NULL
);

CREATE TABLE Ingenieros(
    rut_ingeniero VARCHAR(10) PRIMARY KEY,
    
    FOREIGN KEY (rut_ingeniero) REFERENCES Usuarios(rut_usuario) ON DELETE CASCADE
);

CREATE TABLE Topicos(
    id_topico INT PRIMARY KEY AUTO_INCREMENT,
    categoria VARCHAR(100) NOT NULL UNIQUE
);

CREATE TABLE Especialidades(
    rut_ingeniero VARCHAR(10) NOT NULL,
    id_topico INT NOT NULL,
    
    PRIMARY KEY (rut_ingeniero, id_topico),
    FOREIGN KEY (rut_ingeniero) REFERENCES Usuarios(rut_usuario) ON DELETE CASCADE,
    FOREIGN KEY (id_topico) REFERENCES Topicos(id_topico) ON DELETE CASCADE
);

CREATE TABLE Solicitudes(
    id_solicitud INT PRIMARY KEY AUTO_INCREMENT,
    tipo ENUM('Funcionalidad', 'Error') NOT NULL,
    titulo VARCHAR(200) NOT NULL,
    id_topico INT NOT NULL,
    rut_usuario VARCHAR(10) NOT NULL,
    estado ENUM('Abierto', 'En Progreso', 'Resuelto', 'Cerrado') NOT NULL DEFAULT 'Abierto',
    fecha DATE DEFAULT (CURRENT_DATE),
    
    FOREIGN KEY (id_topico) REFERENCES Topicos(id_topico) ON DELETE CASCADE,
    FOREIGN KEY (rut_usuario) REFERENCES Usuarios(rut_usuario) ON DELETE CASCADE,
    
    UNIQUE KEY unico_titulo (Titulo, Tipo)
);

CREATE TABLE Solicitudes_Funcionalidades(
    id_funcion INT PRIMARY KEY,
    ambiente ENUM('Web','Movil'),
    resumen VARCHAR(150) NOT NULL,
    
    FOREIGN KEY (id_funcion) REFERENCES Solicitudes(id_solicitud) ON DELETE CASCADE
);

CREATE TABLE Solicitudes_Errores(
    id_error INT PRIMARY KEY,
    descripcion VARCHAR(200) NOT NULL,
    
    FOREIGN KEY (id_error) REFERENCES Solicitudes(id_solicitud) ON DELETE CASCADE
);

CREATE TABLE Funcionalidad_Aceptada(
    id_criterio INT PRIMARY KEY AUTO_INCREMENT,
    id_funcion INT NOT NULL,
    descripcion TEXT NOT NULL,
    
    FOREIGN KEY (id_funcion) REFERENCES Solicitudes(id_solicitud) ON DELETE CASCADE
);

CREATE TABLE Asignaciones(
    id_asignacion INT NOT NULL,
    rut_ingeniero VARCHAR(10) NOT NULL,
    
    PRIMARY KEY (id_asignacion, rut_ingeniero),
    FOREIGN KEY (id_asignacion) REFERENCES Solicitudes(id_solicitud) ON DELETE CASCADE,
    FOREIGN KEY (rut_ingeniero) REFERENCES Ingenieros(rut_ingeniero) ON DELETE CASCADE
);

-- Triggers:

Nombre: Max20
Tabla: Asignaciones
BEFORE
INSERT

BEGIN
    DECLARE asignaciones_actuales INT;
    
    SELECT COUNT(*) INTO asignaciones_actuales
    FROM Asignaciones
    WHERE rut_ingeniero = NEW.rut_ingeniero;
    
    IF asignaciones_actuales >= 20 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Error, el ingeniero ya tiene el limite maximo de 20 asignaciones.';
    END IF;
END

Nombre: Max2
Tabla: Especialidades
BEFORE
INSERT

BEGIN
    DECLARE especialidades_actuales INT;
    
    SELECT COUNT(*) INTO especialidades_actuales
    FROM Especialidades
    WHERE rut_ingeniero = NEW.rut_ingeniero;
    
    IF especialidades_actuales >= 2 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Error, los ingenieros no pueden tener mas de 2 especialidades.';
    END IF;
END

Nombre: Min3
Tabla: Funcionalidad_Aceptada
BEFORE
DELETE

BEGIN
    DECLARE criterios_restantes INT;

    SELECT COUNT(*) INTO criterios_restantes
    FROM Funcionalidad_Aceptada
    WHERE id_funcion = OLD.id_funcion;

    IF criterios_restantes <= 3 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Error, una solicitud de funcionalidad debe tener un minimo de 3 criterios.';
    END IF;
END

Nombre: Titulo20min
Tabla: Solicitudes

BEFORE
INSERT

BEGIN
    IF CHAR_LENGTH(NEW.titulo) < 20 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Error, el titulo de la solicitud debe tener un minimo de 20 caracteres.';
    END IF;
END

-- Inserts:

INSERT INTO Topicos (Categoria) VALUES
('Desarrollo de API'),
('Bases de Datos'),
('Frontend Web'),
('Backend Web'),
('Desarrollo Móvil Android'),
('Desarrollo Móvil iOS'),
('DevOps'),
('Seguridad Informática'),
('Inteligencia Artificial'),
('Machine Learning');

--Join

CREATE VIEW Vista_Solicitudes AS
SELECT
    s.id_solicitud,
    s.tipo,
    s.titulo,
    s.id_topico,
    s.rut_usuario,
    s.estado,
    s.fecha,
    u.nombre AS solicitante,
    t.categoria AS topico,
    sf.ambiente,
    sf.resumen,
    se.descripcion
FROM Solicitudes s
JOIN Usuarios u ON s.rut_usuario = u.rut_usuario
JOIN Topicos t ON s.id_topico = t.id_topico
LEFT JOIN Solicitudes_Funcionalidades sf ON s.id_solicitud = sf.id_funcion
LEFT JOIN Solicitudes_Errores se ON s.id_solicitud = se.id_error;

-- Nico Sabe

DELIMITER //
CREATE PROCEDURE SP_Crear_Solicitud_Error(
    IN p_rut_usuario VARCHAR(10),
    IN p_titulo VARCHAR(200),
    IN p_id_topico INT,
    IN p_descripcion VARCHAR(200)
)
BEGIN
    DECLARE nueva_id_solicitud INT;

    -- 1. Insertar en la tabla 'padre' Solicitudes
    INSERT INTO Solicitudes (tipo, titulo, id_topico, rut_usuario)
    VALUES ('Error', p_titulo, p_id_topico, p_rut_usuario);

    -- 2. Obtener el ID auto-incremental que se acaba de crear
    SET nueva_id_solicitud = LAST_INSERT_ID();

    -- 3. Insertar en la tabla 'hija' Solicitudes_Errores
    INSERT INTO Solicitudes_Errores (id_error, descripcion)
    VALUES (nueva_id_solicitud, p_descripcion);
END //
DELIMITER ;

-- Funcion

DELIMITER $$
CREATE FUNCTION `ContarIngenieros`() 
RETURNS INT
DETERMINISTIC
BEGIN
    DECLARE total INT;
    SELECT COUNT(*) INTO total FROM Ingenieros;
    RETURN total;
END$$
DELIMITER ;

-- Nico Sabe 2: Ahora es personal

DELIMITER //
CREATE TRIGGER TR_Asignacion_Automatica
AFTER INSERT ON Solicitudes
FOR EACH ROW
BEGIN
    DECLARE v_ingeniero_disponible VARCHAR(10);

    -- 1. Buscamos un ingeniero que tenga la especialidad (id_topico) de la nueva solicitud.
    --    (Esta es una lógica simple, asigna al primero que encuentra)
    SELECT rut_ingeniero INTO v_ingeniero_disponible
    FROM Especialidades
    WHERE id_topico = NEW.id_topico
    LIMIT 1;

    -- 2. Si se encontró un ingeniero disponible, se crea la asignación.
    IF v_ingeniero_disponible IS NOT NULL THEN
        INSERT INTO Asignaciones (id_asignacion, rut_ingeniero)
        VALUES (NEW.id_solicitud, v_ingeniero_disponible);
    END IF;
END //
DELIMITER ;
