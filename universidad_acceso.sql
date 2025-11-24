-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 24-11-2025 a las 22:38:34
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `universidad_acceso`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `credenciales`
--

CREATE TABLE `credenciales` (
  `id` int(11) NOT NULL,
  `num_cedula` varchar(20) DEFAULT NULL,
  `nombre` varchar(50) DEFAULT NULL,
  `apellido` varchar(50) DEFAULT NULL,
  `correo` varchar(100) DEFAULT NULL,
  `contraseña` varchar(255) DEFAULT NULL,
  `num_telefono` varchar(20) DEFAULT NULL,
  `tipo_persona` enum('ESTUDIANTE','PROFESOR','EMPLEADO','VISITANTE') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `credenciales`
--

INSERT INTO `credenciales` (`id`, `num_cedula`, `nombre`, `apellido`, `correo`, `contraseña`, `num_telefono`, `tipo_persona`) VALUES
(1, '1043654719', 'jorge', 'molina', 'jorge@gmail.com', '1234', '3023435859', 'ESTUDIANTE'),
(2, '0123456789', 'karol', 'hernandez', 'karol@gmail.com', '123', '3127289526', 'PROFESOR'),
(3, '9876543210', 'dulce', 'ramos', 'dulce@gmail.com', '12345', '3004556276', 'ESTUDIANTE'),
(7, '1234567', 'york', 'garcia', 'york@gmail.com', 'york12*', '3023534859', 'EMPLEADO'),
(8, '33133380', 'miriam', 'ramirez', 'miriam@gmail.com', 'miriam1*', '0356408454', 'PROFESOR'),
(9, '1066729412', 'juan', 'moreno', 'juan@gmail.com', 'juan123*', '3012765909', 'VISITANTE'),
(10, '010203', 'benito', 'canelo', 'benito@gmail.com', 'juan123*', '3111111111', 'PROFESOR'),
(11, '1140916161', 'Isaias', 'Mendoza', 'isaias@gmail.com', 'Isaias1*', '3017425723', 'EMPLEADO'),
(13, '00123456', 'Juan', 'Perez', 'valeriamartinez1050@gmail.com', '$2y$10$ZepjM/Tk..sjtTFfhFqh2Og1SqsyRj4v5BKwTXKdrNEpGMka7U.Vq', '312456', 'ESTUDIANTE'),
(15, '1043654711', 'jorginho', 'Molininho', 'Jorge11@gmail.com', '1234', '1234567890', 'EMPLEADO'),
(16, '12345', 'Andres', 'Ramos', 'Andres@gmail.com', '7890', '3022345678', 'ESTUDIANTE'),
(17, '1043637330', 'Jostin', 'Banquez', 'Jostin@gmail.com', '12345678990', '3145305532', 'PROFESOR'),
(19, '123456', 'fulano', 'fulanito', 'fulano@gmail.com', '123', '0987654321', 'ESTUDIANTE'),
(20, '0987', 'Juanda', 'Iriarte', 'Juanda@gmail.com', '12345', '3145305531', 'EMPLEADO'),
(21, '0192837465', 'Mr', 'Black', 'Mrblack@gmail.com', '4567', '3145366532', 'VISITANTE'),
(22, '111', 'pirlo', 'blessd', 'pirlo@gmail.com', '12345', '1122334455', 'VISITANTE'),
(23, '999', 'Mc', 'Car', 'mccar@gmail.com', '5678', '3013454678', 'PROFESOR'),
(24, '0101', 'Kris', 'r', 'Krisr@gmail.com', '12345', '3145305529', 'ESTUDIANTE');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `empleados`
--

CREATE TABLE `empleados` (
  `id` int(11) NOT NULL,
  `credencial_id` int(11) DEFAULT NULL,
  `departamento` varchar(100) DEFAULT NULL,
  `area_laboral` varchar(100) DEFAULT NULL,
  `cargo` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `empleados`
--

INSERT INTO `empleados` (`id`, `credencial_id`, `departamento`, `area_laboral`, `cargo`) VALUES
(1, 7, 'calculo', 'profesor', 'profesor'),
(2, 11, 'ecopetrol', 'Financiero', 'Gerente'),
(3, 20, 'Mantenimiento', NULL, 'Aires acondicionados');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estudiantes`
--

CREATE TABLE `estudiantes` (
  `id` int(11) NOT NULL,
  `credencial_id` int(11) DEFAULT NULL,
  `carrera` varchar(100) DEFAULT NULL,
  `semestre` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `estudiantes`
--

INSERT INTO `estudiantes` (`id`, `credencial_id`, `carrera`, `semestre`) VALUES
(1, 19, 'Contaduría Pública', 7),
(2, 24, 'Derecho', 8);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `horarios`
--

CREATE TABLE `horarios` (
  `id` int(11) NOT NULL,
  `credencial_id` int(11) DEFAULT NULL,
  `hora_inicio` time DEFAULT NULL,
  `hora_fin` time DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `horarios`
--

INSERT INTO `horarios` (`id`, `credencial_id`, `hora_inicio`, `hora_fin`) VALUES
(1, 1, '22:46:11', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `profesores`
--

CREATE TABLE `profesores` (
  `id` int(11) NOT NULL,
  `credencial_id` int(11) DEFAULT NULL,
  `departamento` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `profesores`
--

INSERT INTO `profesores` (`id`, `credencial_id`, `departamento`) VALUES
(2, 1, 'ingles'),
(3, 8, 'Ingeniería'),
(4, 10, 'Ingeniería'),
(5, 17, 'Ingeniería de Sistemas'),
(6, 23, 'Administración de Empresas');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `registro_accesos`
--

CREATE TABLE `registro_accesos` (
  `id` int(11) NOT NULL,
  `credencial_id` int(11) DEFAULT NULL,
  `tipo_persona` enum('ESTUDIANTE','PROFESOR','EMPLEADO','VISITANTE') NOT NULL,
  `tipo_acceso` enum('ENTRADA','SALIDA') NOT NULL,
  `area` enum('ENTRADA_PRINCIPAL','AULAS','BIBLIOTECA','LABORATORIOS','OFICINAS_ADMIN','CAFETERIA','AUDITORIO','GIMNASIO','ESTACIONAMIENTO','AREA_DEPORTIVA','SALA_PROFESORES','LABORATORIO_COMPUTO') NOT NULL,
  `fecha_hora` datetime DEFAULT current_timestamp(),
  `acceso_permitido` tinyint(1) NOT NULL,
  `observacion` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `registro_accesos`
--

INSERT INTO `registro_accesos` (`id`, `credencial_id`, `tipo_persona`, `tipo_acceso`, `area`, `fecha_hora`, `acceso_permitido`, `observacion`) VALUES
(1, 1, 'ESTUDIANTE', 'ENTRADA', 'ENTRADA_PRINCIPAL', '2025-05-08 22:46:11', 1, 'Bienvenido'),
(2, 8, 'PROFESOR', 'ENTRADA', 'AULAS', '2025-05-08 18:18:04', 1, 'Acceso registrado correctamente'),
(3, 9, 'VISITANTE', 'ENTRADA', 'SALA_PROFESORES', '2025-05-08 19:36:10', 1, 'Acceso registrado correctamente'),
(4, 8, 'PROFESOR', 'SALIDA', 'ENTRADA_PRINCIPAL', '2025-05-15 08:35:10', 1, 'Acceso registrado correctamente'),
(5, 11, 'EMPLEADO', 'ENTRADA', 'OFICINAS_ADMIN', '2025-05-20 13:05:11', 1, 'Acceso registrado correctamente'),
(6, 13, 'ESTUDIANTE', 'ENTRADA', 'AUDITORIO', '2025-05-21 17:11:57', 1, 'Acceso registrado correctamente'),
(7, 16, 'ESTUDIANTE', 'ENTRADA', 'CAFETERIA', '2025-11-19 17:37:51', 1, 'Acceso registrado correctamente'),
(8, 16, 'ESTUDIANTE', 'SALIDA', 'AREA_DEPORTIVA', '2025-11-20 08:02:47', 1, 'Acceso registrado correctamente'),
(9, 2, 'PROFESOR', 'SALIDA', 'OFICINAS_ADMIN', '2025-11-20 08:04:51', 1, 'Acceso registrado correctamente'),
(10, 1, 'ESTUDIANTE', 'ENTRADA', 'ESTACIONAMIENTO', '2025-11-20 08:09:44', 1, 'Acceso registrado correctamente'),
(11, 16, 'ESTUDIANTE', 'ENTRADA', 'AREA_DEPORTIVA', '2025-11-20 08:29:13', 1, 'OK'),
(12, 16, 'ESTUDIANTE', 'SALIDA', 'AREA_DEPORTIVA', '2025-11-20 08:29:42', 1, 'OK'),
(13, 16, 'ESTUDIANTE', 'ENTRADA', 'AULAS', '2025-11-20 08:43:04', 1, 'OK'),
(14, 16, 'ESTUDIANTE', 'SALIDA', 'AULAS', '2025-11-20 08:43:25', 1, 'OK'),
(15, 22, 'VISITANTE', 'ENTRADA', 'OFICINAS_ADMIN', '2025-11-20 10:58:11', 1, 'OK'),
(16, 22, 'VISITANTE', 'SALIDA', 'OFICINAS_ADMIN', '2025-11-20 11:23:52', 1, 'OK'),
(17, 17, 'PROFESOR', 'ENTRADA', 'ESTACIONAMIENTO', '2025-11-20 11:53:25', 1, 'OK');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios_admin`
--

CREATE TABLE `usuarios_admin` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `nombre_completo` varchar(100) NOT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  `ultimo_acceso` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios_admin`
--

INSERT INTO `usuarios_admin` (`id`, `username`, `password_hash`, `email`, `nombre_completo`, `creado_en`, `ultimo_acceso`) VALUES
(1, 'admin', '123', 'admin@unicolombo.edu.co', 'Administrador Principal', '2025-05-20 20:55:55', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `visitantes`
--

CREATE TABLE `visitantes` (
  `id` int(11) NOT NULL,
  `credencial_id` int(11) DEFAULT NULL,
  `motivo_visita` text DEFAULT NULL,
  `persona_visitar` varchar(100) DEFAULT NULL,
  `empresa_organizacion` varchar(100) DEFAULT NULL,
  `fecha_visita` date DEFAULT NULL,
  `autorizacion_previa` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `visitantes`
--

INSERT INTO `visitantes` (`id`, `credencial_id`, `motivo_visita`, `persona_visitar`, `empresa_organizacion`, `fecha_visita`, `autorizacion_previa`) VALUES
(1, 9, 'entretener los ojos', 'key ospino', 'playboy', '2025-05-09', 1),
(2, 21, 'Ver al chawala', 'Chawala', NULL, '2025-11-19', 1),
(3, 22, 'arreglar las cosas con blessd ', 'blessd', NULL, '2025-11-20', 1);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `credenciales`
--
ALTER TABLE `credenciales`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `num_cedula` (`num_cedula`),
  ADD UNIQUE KEY `correo` (`correo`);

--
-- Indices de la tabla `empleados`
--
ALTER TABLE `empleados`
  ADD PRIMARY KEY (`id`),
  ADD KEY `credencial_id` (`credencial_id`);

--
-- Indices de la tabla `estudiantes`
--
ALTER TABLE `estudiantes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `credencial_id` (`credencial_id`);

--
-- Indices de la tabla `horarios`
--
ALTER TABLE `horarios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `credencial_id` (`credencial_id`);

--
-- Indices de la tabla `profesores`
--
ALTER TABLE `profesores`
  ADD PRIMARY KEY (`id`),
  ADD KEY `credencial_id` (`credencial_id`);

--
-- Indices de la tabla `registro_accesos`
--
ALTER TABLE `registro_accesos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `credencial_id` (`credencial_id`);

--
-- Indices de la tabla `usuarios_admin`
--
ALTER TABLE `usuarios_admin`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indices de la tabla `visitantes`
--
ALTER TABLE `visitantes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `credencial_id` (`credencial_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `credenciales`
--
ALTER TABLE `credenciales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT de la tabla `empleados`
--
ALTER TABLE `empleados`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `estudiantes`
--
ALTER TABLE `estudiantes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `horarios`
--
ALTER TABLE `horarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `profesores`
--
ALTER TABLE `profesores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `registro_accesos`
--
ALTER TABLE `registro_accesos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT de la tabla `usuarios_admin`
--
ALTER TABLE `usuarios_admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `visitantes`
--
ALTER TABLE `visitantes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `empleados`
--
ALTER TABLE `empleados`
  ADD CONSTRAINT `empleados_ibfk_1` FOREIGN KEY (`credencial_id`) REFERENCES `credenciales` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `estudiantes`
--
ALTER TABLE `estudiantes`
  ADD CONSTRAINT `estudiantes_ibfk_1` FOREIGN KEY (`credencial_id`) REFERENCES `credenciales` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `horarios`
--
ALTER TABLE `horarios`
  ADD CONSTRAINT `horarios_ibfk_1` FOREIGN KEY (`credencial_id`) REFERENCES `credenciales` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `profesores`
--
ALTER TABLE `profesores`
  ADD CONSTRAINT `profesores_ibfk_1` FOREIGN KEY (`credencial_id`) REFERENCES `credenciales` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `registro_accesos`
--
ALTER TABLE `registro_accesos`
  ADD CONSTRAINT `registro_accesos_ibfk_1` FOREIGN KEY (`credencial_id`) REFERENCES `credenciales` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `visitantes`
--
ALTER TABLE `visitantes`
  ADD CONSTRAINT `visitantes_ibfk_1` FOREIGN KEY (`credencial_id`) REFERENCES `credenciales` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
