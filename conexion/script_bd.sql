DROP DATABASE IF EXISTS ecoconexion;
CREATE DATABASE ecoconexion
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE ecoconexion;

CREATE TABLE usuarios (
    id_usuario INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    contrasena_hash VARCHAR(255) NOT NULL,
    telefono VARCHAR(20) NOT NULL,
    tipo_usuario VARCHAR(20) NOT NULL DEFAULT 'generador',
    creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE materiales (
    id_material INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE publicaciones (
    id_publicacion INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(150) NOT NULL,
    cantidad VARCHAR(80) NOT NULL,
    direccion VARCHAR(255) NOT NULL,
    descripcion TEXT NULL,
    estado VARCHAR(20) NOT NULL DEFAULT 'disponible',
    fecha_publicacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    id_material INT UNSIGNED NOT NULL,
    id_usuario INT UNSIGNED NOT NULL,
    CONSTRAINT fk_pub_materiales FOREIGN KEY (id_material) REFERENCES materiales(id_material) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_pub_usuarios FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

INSERT INTO materiales (nombre) VALUES
('Cartón'), ('Plástico'), ('Vidrio'), ('Metal'), ('Papel');

-- Contraseña demo para todos: EcoDemo2024!
INSERT INTO usuarios (nombre, email, contrasena_hash, telefono, tipo_usuario, creado_en) VALUES
('María Ovalle', 'maria@ecoconexion.co', '$2y$10$5CImrN.cWPBjzZYTrz.pp.gE7FJmE9jK1GsvJPlwzY42cSUEyfQG6', '3001234567', 'generador', '2026-03-01 09:15:00'),
('Carlos Recolector', 'carlos@ecoconexion.co', '$2y$10$5CImrN.cWPBjzZYTrz.pp.gE7FJmE9jK1GsvJPlwzY42cSUEyfQG6', '3109876543', 'reciclador', '2026-03-02 11:00:00'),
('Andrés Rincón', 'andres@ecoconexion.co', '$2y$10$5CImrN.cWPBjzZYTrz.pp.gE7FJmE9jK1GsvJPlwzY42cSUEyfQG6', '3154447788', 'generador', '2026-03-05 14:30:00'),
('Laura Tienda Verde', 'laura@ecoconexion.co', '$2y$10$5CImrN.cWPBjzZYTrz.pp.gE7FJmE9jK1GsvJPlwzY42cSUEyfQG6', '3205558899', 'generador', '2026-03-08 08:45:00'),
('Juan Olaya', 'juan@ecoconexion.co', '$2y$10$5CImrN.cWPBjzZYTrz.pp.gE7FJmE9jK1GsvJPlwzY42cSUEyfQG6', '3012223344', 'reciclador', '2026-03-10 16:20:00'),
('Emmanuel Malagón', 'emmanuel@ecoconexion.co', '$2y$10$5CImrN.cWPBjzZYTrz.pp.gE7FJmE9jK1GsvJPlwzY42cSUEyfQG6', '3187776655', 'generador', '2026-03-12 10:10:00');

INSERT INTO publicaciones (titulo, cantidad, direccion, descripcion, estado, fecha_publicacion, id_material, id_usuario) VALUES
('Cajas de cartón corrugado post-mudanza', '8 cajas grandes / ~12 kg', 'Calle 72 # 10-45, Chapinero, Bogotá', 'Cartón limpio, plegado y amarrado. Disponible entre semana 8 a.m. – 6 p.m. Portero autorizado.', 'disponible', '2026-04-02 10:22:00', 1, 1),
('Botellas PET de agua y gaseosa', '3 bolsas grandes / ~6 kg', 'Carrera 15 # 93-07, Chicó, Bogotá', 'Sin tapa. Enjuagadas. Preferible recogida sábado por la mañana.', 'disponible', '2026-04-05 15:40:00', 2, 3),
('Frascos y botellas de vidrio transparente', '2 cajas / ~9 kg', 'Calle 53 # 25-30, Teusaquillo, Bogotá', 'Vidrio limpio, sin roturas grandes. No hay cristalería fina.', 'disponible', '2026-04-08 09:05:00', 3, 4),
('Latas de aluminio y hojalata', '1 saco / ~4 kg', 'Av. Caracas # 34-12, Mártires, Bogotá', 'Latas aplastadas. Disponible todo el día; llamar antes de llegar.', 'disponible', '2026-04-10 18:15:00', 4, 6),
('Papel de oficina y periódicos', '5 kg empacados', 'Calle 100 # 19-61, Usaquén, Bogotá', 'Hojas bond y periódicos. Sin carpetas plásticas ni grapas grandes.', 'disponible', '2026-04-12 11:30:00', 5, 1),
('Cartón de empaque de electrodomésticos', '4 planchas grandes', 'Calle 26 # 68D-35, Fontibón, Bogotá', 'Cartón grueso de nevera y TV. Seco, sin cinta excesiva.', 'disponible', '2026-04-14 13:00:00', 1, 4),
('Plástico rígido tipo yogurt y detergente', '2 bolsas / ~3 kg', 'Carrera 7 # 45-28, La Candelaria, Bogotá', 'Envases enjuagados. Horario: lunes a viernes después de 4 p.m.', 'disponible', '2026-04-15 17:45:00', 2, 6),
('Vidrio verde y ámbar (botellas)', '1 caja / ~5 kg', 'Calle 85 # 11-40, El Nogal, Bogotá', 'Principalmente botellas de vino. Conjunto de edificio, pedir en portería.', 'disponible', '2026-04-16 08:20:00', 3, 3),
('Chatarra liviana y cables cortos', '≈ 7 kg', 'Calle 13 # 68-20, Puente Aranda, Bogotá', 'Restos de taller pequeño. Sin baterías. Recogida solo entre semana.', 'disponible', '2026-04-17 12:10:00', 4, 1),
('Revistas y papel de archivo', '3 paquetes / ~8 kg', 'Carrera 30 # 45-03, Galerías, Bogotá', 'Papel seco, atado con pita. Disponible esta semana.', 'disponible', '2026-04-18 09:50:00', 5, 4),
('Cajas de supermercado aplanadas', '10 kg aprox.', 'Calle 63 # 11-50, Quinta Camacho, Bogotá', 'Cartón limpio de tienda. Ya recogido el mes pasado — caso histórico.', 'recogido', '2026-03-20 14:00:00', 1, 3),
('Bolsa de tapas plásticas', '1 bolsa mediana', 'Calle 40 # 22-15, Palermo, Bogotá', 'Solo tapas. Material ya entregado a reciclador local.', 'recogido', '2026-03-25 16:30:00', 2, 1);
