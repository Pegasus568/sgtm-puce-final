-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 09-12-2025 a las 02:46:24
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
-- Base de datos: `sgtm_puce_v2`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `carreras`
--

CREATE TABLE `carreras` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `codigo` varchar(20) DEFAULT NULL,
  `estado` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `carreras`
--

INSERT INTO `carreras` (`id`, `nombre`, `codigo`, `estado`) VALUES
(1, 'Ingeniería de Software', 'ISO', 1),
(2, 'Enfermería', 'ENF', 1),
(3, 'Gastronomía', 'GAS', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `horarios_docentes`
--

CREATE TABLE `horarios_docentes` (
  `id` int(11) NOT NULL,
  `docente_id` int(11) NOT NULL,
  `dia_semana` enum('1','2','3','4','5') NOT NULL COMMENT '1=Lunes',
  `hora_inicio` time NOT NULL,
  `hora_fin` time NOT NULL,
  `ubicacion_default` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `horarios_docentes`
--

INSERT INTO `horarios_docentes` (`id`, `docente_id`, `dia_semana`, `hora_inicio`, `hora_fin`, `ubicacion_default`) VALUES
(1, 2, '2', '10:00:00', '12:00:00', 'Cubículo 102');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `materias`
--

CREATE TABLE `materias` (
  `id` int(11) NOT NULL,
  `carrera_id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `semestre` varchar(20) DEFAULT NULL,
  `estado` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notificaciones`
--

CREATE TABLE `notificaciones` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `titulo` varchar(100) NOT NULL,
  `mensaje` text NOT NULL,
  `leido` tinyint(1) DEFAULT 0,
  `enviado_por_email` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reportes_sesion`
--

CREATE TABLE `reportes_sesion` (
  `id` int(11) NOT NULL,
  `tutoria_id` int(11) NOT NULL,
  `creado_por` int(11) NOT NULL,
  `observaciones` text NOT NULL,
  `archivo_adjunto` varchar(255) DEFAULT NULL,
  `privado` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipos_tutorias`
--

CREATE TABLE `tipos_tutorias` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `color_etiqueta` varchar(20) DEFAULT '#007bff',
  `activo` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tipos_tutorias`
--

INSERT INTO `tipos_tutorias` (`id`, `nombre`, `color_etiqueta`, `activo`) VALUES
(1, 'Tutoría Académica', '#17a2b8', 1),
(2, 'Mentoría Profesional', '#28a745', 1),
(3, 'Revisión de Tesis', '#ffc107', 1),
(4, 'Consejería Estudiantil', '#6c757d', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tutorias`
--

CREATE TABLE `tutorias` (
  `id` int(11) NOT NULL,
  `codigo_reserva` varchar(20) NOT NULL,
  `solicitado_por` int(11) NOT NULL,
  `tutor_id` int(11) NOT NULL,
  `estudiante_id` int(11) NOT NULL,
  `materia_id` int(11) DEFAULT NULL,
  `tipo_id` int(11) NOT NULL,
  `tema` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `fecha` date NOT NULL,
  `hora_inicio` time NOT NULL,
  `hora_fin` time NOT NULL,
  `modalidad` enum('PRESENCIAL','VIRTUAL','HIBRIDA') DEFAULT 'PRESENCIAL',
  `lugar` varchar(255) DEFAULT NULL,
  `estado` enum('PENDIENTE','CONFIRMADA','RECHAZADA','CANCELADA','REALIZADA','NO_ASISTIO') DEFAULT 'PENDIENTE',
  `motivo_rechazo` text DEFAULT NULL,
  `asistio` tinyint(1) DEFAULT 0,
  `calificacion_estudiante` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tutorias`
--

INSERT INTO `tutorias` (`id`, `codigo_reserva`, `solicitado_por`, `tutor_id`, `estudiante_id`, `materia_id`, `tipo_id`, `tema`, `descripcion`, `fecha`, `hora_inicio`, `hora_fin`, `modalidad`, `lugar`, `estado`, `motivo_rechazo`, `asistio`, `calificacion_estudiante`, `created_at`, `updated_at`) VALUES
(1, 'TR-2025-GV4X', 3, 2, 3, NULL, 4, 'Test', NULL, '2025-12-16', '10:30:00', '11:00:00', 'PRESENCIAL', NULL, 'PENDIENTE', NULL, 0, NULL, '2025-12-08 19:57:18', '2025-12-08 19:57:18');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `correo` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `cedula` varchar(20) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `rol` enum('ADMIN','DOCENTE','ESTUDIANTE') NOT NULL,
  `carrera_id` int(11) DEFAULT NULL,
  `semestre_actual` varchar(20) DEFAULT NULL,
  `foto_perfil` varchar(255) DEFAULT 'default.jpg',
  `token_recuperacion` varchar(255) DEFAULT NULL,
  `estado` enum('ACTIVO','INACTIVO','SUSPENDIDO') DEFAULT 'ACTIVO',
  `ultimo_login` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `correo`, `password_hash`, `cedula`, `telefono`, `rol`, `carrera_id`, `semestre_actual`, `foto_perfil`, `token_recuperacion`, `estado`, `ultimo_login`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Super Administrador', 'admin@sgtm.edu.ec', '$2y$10$RA.yfaDZPyNcq16pSNbewuoyvdBnHypgVDsteRMDWQCDwJKsr.SmC', NULL, NULL, 'ADMIN', NULL, NULL, 'default.jpg', NULL, 'ACTIVO', '2025-12-08 19:43:40', '2025-12-08 18:18:39', '2025-12-08 19:43:40', NULL),
(2, 'Docente', 'Docente@pucesa.edu.ec', '$2y$10$04MuNWKCEekv8FLS8fpdSucKstZjUODbVBe65zQ3LmSMuLSYhX2Gu', '1805129580', '09988111111111111111', 'DOCENTE', 1, NULL, 'default.jpg', NULL, 'ACTIVO', '2025-12-08 19:48:58', '2025-12-08 19:41:30', '2025-12-08 19:48:58', NULL),
(3, 'Estudiante', 'estudiante@pucesa.edu.ec', '$2y$10$F2C1/5r62PpWYavwu6qQXeFy/cdnKk.4ZXsR.GwQCVRKAIkEN9VOi', '1805129572', '09988111111121111111', 'ESTUDIANTE', 1, '5to', 'default.jpg', NULL, 'ACTIVO', '2025-12-08 19:53:57', '2025-12-08 19:44:12', '2025-12-08 19:53:57', NULL);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `carreras`
--
ALTER TABLE `carreras`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo` (`codigo`);

--
-- Indices de la tabla `horarios_docentes`
--
ALTER TABLE `horarios_docentes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `docente_id` (`docente_id`);

--
-- Indices de la tabla `materias`
--
ALTER TABLE `materias`
  ADD PRIMARY KEY (`id`),
  ADD KEY `carrera_id` (`carrera_id`);

--
-- Indices de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `reportes_sesion`
--
ALTER TABLE `reportes_sesion`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tutoria_id` (`tutoria_id`),
  ADD KEY `creado_por` (`creado_por`);

--
-- Indices de la tabla `tipos_tutorias`
--
ALTER TABLE `tipos_tutorias`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `tutorias`
--
ALTER TABLE `tutorias`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo_reserva` (`codigo_reserva`),
  ADD KEY `solicitado_por` (`solicitado_por`),
  ADD KEY `tutor_id` (`tutor_id`),
  ADD KEY `estudiante_id` (`estudiante_id`),
  ADD KEY `materia_id` (`materia_id`),
  ADD KEY `tipo_id` (`tipo_id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `correo` (`correo`),
  ADD KEY `carrera_id` (`carrera_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `carreras`
--
ALTER TABLE `carreras`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `horarios_docentes`
--
ALTER TABLE `horarios_docentes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `materias`
--
ALTER TABLE `materias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `reportes_sesion`
--
ALTER TABLE `reportes_sesion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tipos_tutorias`
--
ALTER TABLE `tipos_tutorias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `tutorias`
--
ALTER TABLE `tutorias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `horarios_docentes`
--
ALTER TABLE `horarios_docentes`
  ADD CONSTRAINT `horarios_docentes_ibfk_1` FOREIGN KEY (`docente_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `materias`
--
ALTER TABLE `materias`
  ADD CONSTRAINT `materias_ibfk_1` FOREIGN KEY (`carrera_id`) REFERENCES `carreras` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  ADD CONSTRAINT `notificaciones_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `reportes_sesion`
--
ALTER TABLE `reportes_sesion`
  ADD CONSTRAINT `reportes_sesion_ibfk_1` FOREIGN KEY (`tutoria_id`) REFERENCES `tutorias` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reportes_sesion_ibfk_2` FOREIGN KEY (`creado_por`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `tutorias`
--
ALTER TABLE `tutorias`
  ADD CONSTRAINT `tutorias_ibfk_1` FOREIGN KEY (`solicitado_por`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `tutorias_ibfk_2` FOREIGN KEY (`tutor_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `tutorias_ibfk_3` FOREIGN KEY (`estudiante_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `tutorias_ibfk_4` FOREIGN KEY (`materia_id`) REFERENCES `materias` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `tutorias_ibfk_5` FOREIGN KEY (`tipo_id`) REFERENCES `tipos_tutorias` (`id`);

--
-- Filtros para la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`carrera_id`) REFERENCES `carreras` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
