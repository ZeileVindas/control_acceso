# Sistema de Control de Acceso de Empleados (ISB-32)

Aplicación web desarrollada en PHP (PDO) y MySQL para la gestión de empleados y el registro automatizado de asistencias (entradas y salidas) en una empresa, desarrollada como proyecto para el curso de Programación Avanzada.

---

## Características Principales

1. Acceso Administrativo: Módulo de autenticación seguro mediante sesiones PHP y contraseñas cifradas.
2. Mantenimiento de Empleados: 
   - Registro, edición y consulta de colaboradores.
   - Activación y desactivación de cuentas.
   - Generación automática de un código de acceso único de 6 dígitos por empleado.
3. Pantalla Pública de Marcación: Interfaz rápida para que los empleados registren su entrada o salida ingresando únicamente su cédula y código de acceso.
4. Control Inteligente de Asistencia: El sistema detecta automáticamente si corresponde registrar una entrada o una salida, evitando registros duplicados o salidas sin entrada previa.
5. Historial de Asistencia y Tardanzas: 
   - Visualización de registros con cálculo automático de horas trabajadas.
   - Detección automática de llegadas tardías.
   - Filtros avanzados por empleado, cédula y rangos de fechas.
6. Dashboard en Tiempo Real: Métricas con el total de empleados, activos, personal que ingresó hoy y lista de personas que se encuentran actualmente dentro de la empresa.

---

## Tecnologías Utilizadas

* Backend: PHP (con objetos PDO para consultas seguras a base de datos).
* Base de Datos: MySQL / MariaDB.
* Frontend: HTML5, CSS3, JavaScript.
* Framework CSS: Bootstrap 5.
* Control de Sesiones: PHP Sessions.

---

## Estructura de Archivos

* conexion.php: Archivo centralizado de conexión a la base de datos mediante PDO.
* login.php / logout.php: Gestión de inicio y cierre de sesión del administrador.
* dashboard.php: Panel principal con estadísticas e indicadores en tiempo real.
* empleados.php: Módulo CRUD y gestión de estados de los empleados.
* historial.php: Consulta de registros de asistencia y filtros de búsqueda.
* index.php: Pantalla pública de marcación de entradas y salidas.
* estilos.css: Estilos visuales personalizados y diseño responsive.
* database.sql: Esquema de la base de datos.

---

## Instrucciones de Instalación y Configuración

1. Clonar o descargar el repositorio en tu servidor local (por ejemplo, dentro de la carpeta htdocs de XAMPP).
2. Crear una base de datos en phpMyAdmin llamada control_acceso_db.
3. Importar el archivo de la base de datos incluido en el repositorio (database.sql).
4. Verificar los parámetros de conexión en el archivo conexion.php si es necesario.
5. Ejecutar la aplicación en el navegador ingresando a:
   http://localhost/control_acceso/index.php

---

## Autor
Proyecto académico desarrollado para Universidad Internacional San Isidro Labrador (UISIL).
