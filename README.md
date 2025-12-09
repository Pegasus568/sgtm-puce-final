# SGTM v2.0 - Sistema de Gestión de Tutorías Académicas

![Badge PHP](https://img.shields.io/badge/Backend-PHP%207.4%2B-blue?style=flat-square&logo=php)
![Badge MySQL](https://img.shields.io/badge/Database-MySQL-orange?style=flat-square&logo=mysql)
![Badge Architecture](https://img.shields.io/badge/Architecture-MVC-green?style=flat-square)
![Badge Status](https://img.shields.io/badge/Status-Stable-success?style=flat-square)

> **SGTM v2.0** es una plataforma web robusta diseñada para optimizar el agendamiento de citas académicas entre estudiantes y docentes. Migrada de una arquitectura monolítica a un patrón de diseño **MVC (Modelo-Vista-Controlador)** profesional, ofrece seguridad, escalabilidad y una experiencia de usuario fluida.

---

## 📋 Tabla de Contenidos
1. [Características Principales](#-características-principales)
2. [Prerrequisitos](#-prerrequisitos)
3. [Guía de Instalación Rápida](#-guía-de-instalación-rápida)
4. [Configuración del Entorno](#%EF%B8%8F-configuración-del-entorno-crucial)
5. [Acceso y Credenciales](#-acceso-y-credenciales)
6. [Solución de Problemas Frecuentes](#-solución-de-problemas-frecuentes-troubleshooting)

---

## ✨ Características Principales

### 🎓 Para Estudiantes
* **Búsqueda Inteligente:** Filtrado de docentes por área o carrera.
* **Motor de Reservas en Tiempo Real:** Cálculo automático de horarios disponibles mediante AJAX, evitando cruces de horario.
* **Gestión de Solicitudes:** Visualización del estado de las citas (Pendiente, Confirmada, Rechazada) con indicadores visuales.

### 👨‍🏫 Para Docentes
* **Agenda Interactiva:** "Bandeja de entrada" para aceptar (asignando lugar) o rechazar (con motivo) solicitudes.
* **Configuración de Horarios:** Definición flexible de bloques de disponibilidad semanal.
* **Cierre de Ciclo:** Toma de asistencia y registro de observaciones post-tutoría.

### 🛡️ Para Administradores
* **Gestión Total (CRUD):** Control completo sobre usuarios, carreras, materias y tipos de tutoría.
* **Dashboard de Analítica:** Gráficos estadísticos de demanda y KPIs del sistema.
* **Auditoría:** Historial global de citas y control de acceso basado en roles (RBAC).

---

## 📋 Prerrequisitos

Antes de desplegar, asegúrate de contar con el siguiente entorno:

* [XAMPP](https://www.apachefriends.org/es/index.html) (Recomendado: PHP 7.4 o superior).
* [Git](https://git-scm.com/) (Para control de versiones).
* Un navegador web moderno (Chrome, Edge, Firefox).

---

## 🚀 Guía de Instalación Rápida

Sigue estos pasos para tener el sistema corriendo en local en minutos.

### Paso 1: Clonar el Repositorio
Navega a tu carpeta `htdocs` y clona el proyecto.

```bash
cd C:\xampp\htdocs
git clone [https://github.com/TU_USUARIO/sgtm_v2.git](https://github.com/TU_USUARIO/sgtm_v2.git)
# NOTA: La carpeta resultante debe llamarse estrictamente "sgtm_v2"