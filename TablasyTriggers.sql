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
    UNIQUE KEY unico_titulo (titulo, tipo) -- Changed Titulo to titulo for consistency
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

CREATE TABLE Resenas (
    id_resena INT PRIMARY KEY AUTO_INCREMENT,
    id_solicitud INT NOT NULL,
    rut_ingeniero VARCHAR(10) NOT NULL,
    observacion TEXT NOT NULL,
    fecha_resena DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_solicitud) REFERENCES Solicitudes(id_solicitud) ON DELETE CASCADE,
    FOREIGN KEY (rut_ingeniero) REFERENCES Ingenieros(rut_ingeniero) ON DELETE CASCADE
);

-- Triggers:

DELIMITER //
CREATE TRIGGER Max20
BEFORE INSERT ON Asignaciones
FOR EACH ROW
BEGIN
    DECLARE asignaciones_actuales INT;
    SELECT COUNT(*) INTO asignaciones_actuales
    FROM Asignaciones
    WHERE rut_ingeniero = NEW.rut_ingeniero;
    IF asignaciones_actuales >= 20 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Error, el ingeniero ya tiene el limite maximo de 20 asignaciones.';
    END IF;
END //
DELIMITER ;

DELIMITER //
CREATE TRIGGER Max2
BEFORE INSERT ON Especialidades
FOR EACH ROW
BEGIN
    DECLARE especialidades_actuales INT;
    SELECT COUNT(*) INTO especialidades_actuales
    FROM Especialidades
    WHERE rut_ingeniero = NEW.rut_ingeniero;
    IF especialidades_actuales >= 2 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Error, los ingenieros no pueden tener mas de 2 especialidades.';
    END IF;
END //
DELIMITER ;

DELIMITER //
CREATE TRIGGER Min3
BEFORE DELETE ON Funcionalidad_Aceptada
FOR EACH ROW
BEGIN
    DECLARE criterios_restantes INT;
    SELECT COUNT(*) INTO criterios_restantes
    FROM Funcionalidad_Aceptada
    WHERE id_funcion = OLD.id_funcion;

    IF criterios_restantes <= 3 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Error, una solicitud de funcionalidad debe tener un minimo de 3 criterios.';
    END IF;
END //
DELIMITER ;

DELIMITER //
CREATE TRIGGER Titulo20min
BEFORE INSERT ON Solicitudes
FOR EACH ROW
BEGIN
    IF CHAR_LENGTH(NEW.titulo) < 20 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Error, el titulo de la solicitud debe tener un minimo de 20 caracteres.';
    END IF;
END //
DELIMITER ;

DELIMITER //
CREATE TRIGGER TR_Asignacion_Automatica
AFTER INSERT ON Solicitudes
FOR EACH ROW
BEGIN
    DECLARE v_ingeniero_disponible VARCHAR(10);
    SELECT rut_ingeniero INTO v_ingeniero_disponible
    FROM Especialidades
    WHERE id_topico = NEW.id_topico
    LIMIT 1;

    IF v_ingeniero_disponible IS NOT NULL THEN
        INSERT INTO Asignaciones (id_asignacion, rut_ingeniero)
        VALUES (NEW.id_solicitud, v_ingeniero_disponible);
    END IF;
END //
DELIMITER ;

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

INSERT INTO Usuarios (rut_usuario, nombre, email, contrasena) VALUES
('1111111-1', 'Ana Rojas', 'ana.rojas@email.com', 'hash_contrasena_123'),
('2222222-2', 'Bruno Diaz', 'bruno.diaz@email.com', 'hash_contrasena_456'),
('3333333-3', 'Carlos Soto', 'carlos.soto@email.com', 'hash_contrasena_789'),
('4444444-4', 'Daniela Mora', 'daniela.mora@email.com', 'hash_contrasena_abc'),
('5555555-5', 'Ester Vera', 'ester.vera@email.com', 'hash_contrasena_def');

INSERT INTO Ingenieros (rut_ingeniero) VALUES
('3333333-3'),
('4444444-4'),
('5555555-5');

INSERT INTO Especialidades (rut_ingeniero, id_topico) VALUES
('3333333-3', 1),
('3333333-3', 4);

INSERT INTO Especialidades (rut_ingeniero, id_topico) VALUES
('4444444-4', 3);

INSERT INTO Especialidades (rut_ingeniero, id_topico) VALUES
('5555555-5', 5),
('5555555-5', 6);

INSERT INTO Solicitudes (tipo, titulo, id_topico, rut_usuario, estado, fecha) VALUES
('Funcionalidad', 'Implementar nuevo endpoint GET para perfiles de usuario', 1, '1111111-1', 'Abierto', '2025-10-01');

INSERT INTO Solicitudes (tipo, titulo, id_topico, rut_usuario, estado, fecha) VALUES
('Error', 'El botón de login desaparece en resolución 1024x768', 3, '2222222-2', 'En Progreso', '2025-10-05');

INSERT INTO Solicitudes (tipo, titulo, id_topico, rut_usuario, estado, fecha) VALUES
('Funcionalidad', 'Crear la nueva vista de bienvenida en la App Android', 5, '1111111-1', 'Resuelto', '2025-10-10');

INSERT INTO Solicitudes (tipo, titulo, id_topico, rut_usuario, estado, fecha) VALUES
('Error', 'Fallo en el pipeline de despliegue continuo de producción', 7, '2222222-2', 'Abierto', '2025-10-15');

INSERT INTO Solicitudes_Funcionalidades (id_funcion, ambiente, resumen) VALUES
(1, 'Web', 'Resumen: Endpoint debe retornar nombre, email y avatar del usuario.'),
(3, 'Movil', 'Resumen: Pantalla con logo y botón de "Comenzar".');

INSERT INTO Solicitudes_Errores (id_error, descripcion) VALUES
(2, 'Descripción: Al reducir la ventana del navegador, el botón se oculta.'),
(4, 'Descripción: El job de Jenkins falla en la etapa de "deploy".');

INSERT INTO Funcionalidad_Aceptada (id_funcion, descripcion) VALUES
(1, 'Criterio 1.1: Endpoint responde con JSON.'),
(1, 'Criterio 1.2: Endpoint responde en menos de 200ms.'),
(1, 'Criterio 1.3: Endpoint requiere autenticación Bearer.');

INSERT INTO Funcionalidad_Aceptada (id_funcion, descripcion) VALUES
(3, 'Criterio 3.1: El logo está centrado.'),
(3, 'Criterio 3.2: El botón "Comenzar" navega a la home.'),
(3, 'Criterio 3.3: La pantalla funciona en modo oscuro.'),
(3, 'Criterio 3.4: La pantalla se adapta a tablets.');

INSERT INTO Asignaciones (id_asignacion, rut_ingeniero) VALUES
(4, '3333333-3');

INSERT INTO Resenas (id_solicitud, rut_ingeniero, observacion) VALUES
(2, '4444444-4', 'Revisando el CSS, parece ser un problema de media queries.'),
(3, '5555555-5', 'Funcionalidad completada y probada en emulador Pixel 5.'),
(1, '3333333-3', 'Inicio de desarrollo del endpoint.');

COMMIT;

-- Vista

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

-- Proceso

DELIMITER //
CREATE PROCEDURE SP_Crear_Solicitud_Error(
    IN p_rut_usuario VARCHAR(10),
    IN p_titulo VARCHAR(200),
    IN p_id_topico INT,
    IN p_descripcion VARCHAR(200)
)
BEGIN
    DECLARE nueva_id_solicitud INT;

    INSERT INTO Solicitudes (tipo, titulo, id_topico, rut_usuario)
    VALUES ('Error', p_titulo, p_id_topico, p_rut_usuario);

    SET nueva_id_solicitud = LAST_INSERT_ID();

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
