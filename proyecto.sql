-- MySQL dump corrected for Docker compatibility

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- Create the database if it doesn't exist
CREATE DATABASE IF NOT EXISTS `proyecto`;
USE `proyecto`;

-- Disable foreign key checks temporarily
SET FOREIGN_KEY_CHECKS=0;

-- Table structure for table `grupos`
DROP TABLE IF EXISTS `grupos`;
CREATE TABLE `grupos` (
  `id_grupo` int(11) NOT NULL AUTO_INCREMENT,
  `namecreador` varchar(255) NOT NULL,
  `correocreador` varchar(255) NOT NULL,
  `descripcion` text NOT NULL,
  `id_alumno` text DEFAULT NULL,
  PRIMARY KEY (`id_grupo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert data for table `grupos`
INSERT INTO `grupos` (`id_grupo`, `namecreador`, `correocreador`, `descripcion`, `id_alumno`) VALUES
(1, 'Profesor', 'lisbetcg79@gmail.com', 'Prueba de actiualizacion', '1,1'),
(15, 'Davprofesor', 'lisbetcg79@gmail.com', 'Prueba', '1'),
(17, 'Davprofesor', 'lisbetcg79@gmail.com', 'hola', NULL),
(19, 'Davprofesor', 'lisbetcg79@gmail.com', 'Hola mundo', '1');

-- Table structure for table `likes`
DROP TABLE IF EXISTS `likes`;
CREATE TABLE `likes` (
  `id_like` int(11) NOT NULL AUTO_INCREMENT,
  `id_user` int(11) NOT NULL,
  `id_mensaje` int(11) NOT NULL,
  PRIMARY KEY (`id_like`),
  UNIQUE KEY `id_user_mensaje` (`id_user`,`id_mensaje`),
  KEY `id_mensaje` (`id_mensaje`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert data for table `likes`
INSERT INTO `likes` (`id_like`, `id_user`, `id_mensaje`) VALUES
(9, 1, 56),
(1, 1, 57);

-- Table structure for table `mensajes`
DROP TABLE IF EXISTS `mensajes`;
CREATE TABLE `mensajes` (
  `id_mensaje` int(11) NOT NULL AUTO_INCREMENT,
  `id_grupo` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `mensaje` text NOT NULL,
  `fecha` datetime NOT NULL DEFAULT current_timestamp(),
  `imagen` varchar(255) NOT NULL,
  PRIMARY KEY (`id_mensaje`),
  KEY `id_grupo` (`id_grupo`),
  KEY `id_user` (`id_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert data for table `mensajes`
-- (Insert statements for `mensajes` table here, omitted for brevity)

-- Table structure for table `usuarios`
DROP TABLE IF EXISTS `usuarios`;
CREATE TABLE `usuarios` (
  `id_user` int(3) NOT NULL AUTO_INCREMENT,
  `username` varchar(30) NOT NULL,
  `correo` varchar(30) NOT NULL,
  `password` varchar(30) NOT NULL,
  `instituto` int(2) NOT NULL,
  PRIMARY KEY (`id_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert data for table `usuarios`
INSERT INTO `usuarios` (`id_user`, `username`, `correo`, `password`, `instituto`) VALUES
(1, 'DavC', 'davidprofesor14@gmail.com', '1234', 0),
(3, 'Davprofesor', 'lisbetcg79@gmail.com', '1111', 2);

-- Add foreign key constraints
ALTER TABLE `likes`
  ADD CONSTRAINT `likes_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `usuarios` (`id_user`),
  ADD CONSTRAINT `likes_ibfk_2` FOREIGN KEY (`id_mensaje`) REFERENCES `mensajes` (`id_mensaje`);

ALTER TABLE `mensajes`
  ADD CONSTRAINT `mensajes_ibfk_1` FOREIGN KEY (`id_grupo`) REFERENCES `grupos` (`id_grupo`) ON DELETE CASCADE,
  ADD CONSTRAINT `mensajes_ibfk_2` FOREIGN KEY (`id_user`) REFERENCES `usuarios` (`id_user`) ON DELETE CASCADE;

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS=1;

COMMIT;
