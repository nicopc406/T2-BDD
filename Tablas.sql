-- Tablas

CREATE TABLE Usuarios(
    rut_usuario VARCHAR(10) PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    contraseña VARCHAR(255) NOT NULL
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



-- Vistas, Funciones, Procedimientos y Triggers


CREATE VIEW V_Solicitudes_Funcionalidad_Detalle AS
SELECT
    s.id_solicitud,
    s.titulo,
    s.estado,
    s.fecha,
    u.nombre AS nombre_usuario_solicitante,
    t.categoria AS topico,
    sf.ambiente,
    sf.resumen
FROM Solicitudes s
JOIN Usuarios u ON s.rut_usuario = u.rut_usuario
JOIN Topicos t ON s.id_topico = t.id_topico
JOIN Solicitudes_Funcionalidades sf ON s.id_solicitud = sf.id_funcion;




DELIMITER //
CREATE FUNCTION F_Contar_Asignaciones_Ingeniero(
    p_rut_ingeniero VARCHAR(10)
)
RETURNS INT
DETERMINISTIC
READS SQL DATA
BEGIN
    DECLARE total_asignaciones INT;

    SELECT COUNT(*) INTO total_asignaciones
    FROM Asignaciones
    WHERE rut_ingeniero = p_rut_ingeniero;

    RETURN total_asignaciones;
END //
DELIMITER ;




DELIMITER //
CREATE PROCEDURE SP_Crear_Solicitud_Error(
    IN p_rut_usuario VARCHAR(10),
    IN p_titulo VARCHAR(200),
    IN p_id_topico INT,
    IN p_descripcion VARCHAR(200)
)
BEGIN
    DECLARE nueva_id_solicitud INT;

    -- Insertar en la tabla 'padre'
    INSERT INTO Solicitudes (tipo, titulo, id_topico, rut_usuario)
    VALUES ('Error', p_titulo, p_id_topico, p_rut_usuario);

    -- Obtener el ID auto-incremental que se acaba de crear
    SET nueva_id_solicitud = LAST_INSERT_ID();

    -- Insertar en la tabla 'hija'
    INSERT INTO Solicitudes_Errores (id_error, descripcion)
    VALUES (nueva_id_solicitud, p_descripcion);
END //
DELIMITER ;





DELIMITER //
CREATE TRIGGER TR_Asignacion_Automatica
AFTER INSERT ON Solicitudes
FOR EACH ROW
BEGIN
    DECLARE v_ingeniero_disponible VARCHAR(10);

    -- 1. Buscar un ingeniero que tenga la especialidad (id_topico) de la nueva solicitud.
    -- (Esta es una lógica simple, toma al primero que encuentra)
    SELECT rut_ingeniero INTO v_ingeniero_disponible
    FROM Especialidades
    WHERE id_topico = NEW.id_topico
    LIMIT 1;

    -- 2. Si se encontró un ingeniero, crear la asignación.
    IF v_ingeniero_disponible IS NOT NULL THEN
        INSERT INTO Asignaciones (id_asignacion, rut_ingeniero)
        VALUES (NEW.id_solicitud, v_ingeniero_disponible);
    END IF;
END //
DELIMITER ;

