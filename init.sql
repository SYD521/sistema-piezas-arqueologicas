-- Inicialización de base de datos para Sistema de Piezas Arqueológicas
-- Ejecutado automáticamente por el entrypoint de MySQL en /docker-entrypoint-initdb.d/

CREATE DATABASE IF NOT EXISTS `patrimonio_db` 
  DEFAULT CHARACTER SET utf8mb4 
  COLLATE utf8mb4_unicode_ci;

USE `patrimonio_db`;

-- Tabla de piezas arqueológicas
CREATE TABLE IF NOT EXISTS `piezas_arqueologicas` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nombre_tipo_objeto` VARCHAR(150) NOT NULL COMMENT 'Nombre o clasificación del objeto (ej. Vasija ceremonial)',
    `sitio_hallazgo` VARCHAR(200) NOT NULL COMMENT 'Nombre del sitio arqueológico o ubicación del hallazgo',
    `latitud` DECIMAL(10, 8) NULL COMMENT 'Coordenada de latitud en grados decimales (-90 a 90)',
    `longitud` DECIMAL(11, 8) NULL COMMENT 'Coordenada de longitud en grados decimales (-180 a 180)',
    `fecha_hallazgo` DATE NOT NULL COMMENT 'Fecha en que se encontró la pieza',
    `descripcion` TEXT NULL COMMENT 'Detalles morfológicos, contexto o anotaciones de campo',
    `estado_conservacion` ENUM('EXCELENTE', 'REGULAR', 'FRAGMENTADO') NOT NULL DEFAULT 'REGULAR' COMMENT 'Estado actual de la pieza',
    `fecha_registro` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Auditoría: fecha de ingreso al sistema',
    INDEX `idx_sitio_hallazgo` (`sitio_hallazgo`),
    INDEX `idx_fecha_hallazgo` (`fecha_hallazgo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inserción de registros iniciales de prueba
INSERT INTO `piezas_arqueologicas` 
    (`nombre_tipo_objeto`, `sitio_hallazgo`, `latitud`, `longitud`, `fecha_hallazgo`, `descripcion`, `estado_conservacion`)
VALUES 
    (
        'Vasija Policromada Copador', 
        'Joya de Cerén, Estructura 3', 
        13.82670000, 
        -89.35640000, 
        '2024-02-15', 
        'Vasija de barro con decoración geométrica y antropomorfa en tonos rojo y negro.', 
        'EXCELENTE'
    ),
    (
        'Hacha Votiva de Obsidiana', 
        'Tazumal, Sector B', 
        13.98030000, 
        -89.67470000, 
        '2024-03-01', 
        'Hoja de obsidiana negra pulida con restos de pigmento ocre en la empuñadura.', 
        'REGULAR'
    ),
    (
        'Fragmento de Figurilla Zoomorfa', 
        'San Andrés, Plaza Central', 
        13.79780000, 
        -89.39080000, 
        '2024-03-10', 
        'Sección craneal de felino en terracota con incisiones detalladas.', 
        'FRAGMENTADO'
    ),
    (
        'Collar de Cuentas de Jade', 
        'Cihuatán, Acrópolis', 
        13.97860000, 
        -89.17220000, 
        '2024-04-05', 
        'Conjunto de 12 cuentas esféricas de jade verde brillante pulidas a mano.', 
        'EXCELENTE'
    );
