# Elevia Academy — Paso 1: Alcance funcional

> Proyecto **nuevo**: `c:\xampp\htdocs\ProyectoFinal\EleviaAcademy`
>
> Referencia **antigua**: `c:\xampp\htdocs\EleviaAcademy`

## 1) Objetivo del producto
Crear una plataforma académica web con dos perfiles principales:
- **Alumno**: consulta cursos matriculados, progreso, anuncios, mensajes, calificaciones y fechas de entrega.
- **Profesor**: gestiona cursos y contenidos, publica anuncios, deja calificaciones y mensajes.

## 2) Roles y permisos

### Alumno
- Página con informacion del perfil del alumno, incluye avatar, nombre completo, email y cursos matriculados.
- Ver listado de cursos en los que está matriculado.
- Ver barra/progreso por curso.
- Ver tablón de anuncios.
- Ver mensajes del profesor.
- Poder subir entregas (archivos) para tareas asignadas.
- Consultar calificaciones y fecha de entrega.

### Profesor
- Crear/editar/ocultar/eliminar cursos (según permisos internos).
- Subir y gestionar contenido del curso.
- Añadir tareas y fechas de entrega.
- Login para cada profesor, con acceso a su panel privado (máximo 4 profesores)
- 1 profesor con rol de superadmin para gestionar cursos y profesores y todo lo relacionado con la plataforma.
- Publicar anuncios en tablón.
- Asignar calificaciones.
- Enviar mensajes a alumnos.

## 3) Vistas públicas (banner principal)
1. **Home**
   - Presentación de la academia. Logo, slogan, breve descripción.
   - Información básica.
   - Catálogo resumido de ~9 cursos.
   - Botón **Inscríbete**.
   - El logo/nombre **Elevia Academy** redirige a Home.

2. **Cursos**
   - Listado de cursos disponibles.
   - Profesor que imparte.
   - Breve descripción.
   - Horas/carga lectiva.
   - Botón para ver detalle de cada curso.
   - Descripción completa del curso y competencias a adquirir.
   - Botón al pie: **Inscríbete**.
   - Redirección a formulario de inscripción.
   - Tras enviar formulario: pantalla de **Gracias por su interés**.
   - Orientador de Cursos (Test de Afinidades): Módulo dinámico que, mediante un cuestionario breve de 3 o 4 preguntas (ej. preferencia de lenguaje, nivel inicial, disponibilidad de tiempo), procesa las respuestas mediante lógica condicional (if/switch) en el código para recomendar al usuario los 3 cursos del catálogo que mejor se adaptan a su perfil.

3. **Nosotros**
   - Historia breve de la academia.
   - ¿Por qué elegirnos?.
   - Valoraciones/testimonios.

4. **Contacto**
   - Layout en 2 columnas:
     - Izquierda: formulario (nombre, email, asunto, mensaje en box que incluye un "Acepto la Política de Privacidad").
     - Derecha: teléfono, email, horario de atención.
   - Ubicación: Mapa interactivo integrado mediante un elemento iframe estándar de Google   Maps (sin requerir claves de API).

5. **Acceder**
   - Formulario de login.
   - Redirección por credenciales al panel de **Alumno** o **Profesor**.

## 4) Login de demostración 
Credenciales de ejemplo para primera versión:
- `alumno1@eacademy.com` / `ejemplo1`
- `alumno2@eacademy.com` / `ejemplo2`
- `profe1@eacademy.com` / `profe1`
- `profe2@eacademy.com` / `profe2`
- `superprofe@eacademy.com` / `superprofe` (rol superadmin)

> Nota: en producción se usará autenticación segura con contraseñas cifradas y gestión de sesiones.

## 5) Vistas privadas tras login

### Panel Alumno
- Mis cursos matriculados.
- Contenido del curso (según progreso).
- Progreso por curso.
- Tablón de anuncios.
- Calificaciones.
- Mensajes.
- En esta zona se oculta la navegación pública (Nosotros, Contacto, etc.).

> Nota: en futuro se añadirá una sección de chat con un asistente virtual para resolver dudas frecuentes.

### Panel Profesor
- Gestión de cursos y contenidos.
- Publicación de anuncios.
- Gestión de calificaciones.
- Mensajería con alumnos.

## 6) Pie de página global (no se muestra tras acceso de profesor o alumno)
- **Elevia Academy**
- “Impulsa tu futuro digital”

Secciones:
- **Enlaces**: Inicio, Cursos, Contacto (botones redirigidos)
- **Redes**: Twitter, LinkedIn, GitHub (íconos con enlaces a perfiles)
- **Legal**: Privacidad, Términos y Condiciones, Cookies (enlaces a páginas legales con textos simples de ejemplo)

Cierre:
- `© 2026 Elevia Academy. Todos los derechos reservados.`

## 7) Gestión de errores
- Incluir página **404** para rutas no válidas.


> **Importante**: Este documento define el alcance funcional inicial. En futuras iteraciones se podrán añadir nuevas funcionalidades o ajustar las existentes según feedback y necesidades detectadas. 