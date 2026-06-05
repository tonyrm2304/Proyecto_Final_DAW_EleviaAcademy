# Elevia Academy - Plataforma de Formación Digital

Plataforma LMS (Learning Management System) desarrollada como proyecto final del ciclo DAW. Sistema completo para gestión de cursos, estudiantes, matrículas y evaluaciones con roles diferenciados.

## Tecnología

- **Backend**: Laravel 11 (PHP)
- **Frontend**: Vue 3 + Inertia.js
- **Base de datos**: MySQL 8.0
- **Styling**: TailwindCSS
- **Build**: Vite
- **Autenticación**: Laravel Breeze + Sanctum

## Características

✅ Gestión de cursos (crear, editar, eliminar con confirmación)
✅ Subida de imágenes personalizadas para cursos
✅ Sistema de matrículas automático
✅ Evaluación automática de progreso (50/30/20)
✅ Tareas con entregas de evidencias
✅ Mensajería privada entre usuarios
✅ Tablón de anuncios por curso
✅ Panel de administración
✅ API REST pública
✅ Validación completa de formularios
✅ Almacenamiento seguro de archivos
✅ Página 404 personalizada

## Instalación

### Requisitos
- PHP 8.2+, Node.js 18+, MySQL 8.0+, Composer, npm

### Setup

```bash
# Clonar y dependencias
git clone <url>
cd EleviaAcademy
composer install
npm install

# Configurar entorno
cp .env.example .env
php artisan key:generate

# Base de datos (editar .env con tus credenciales)
mysql -u root -p -e "CREATE DATABASE elevia_academy;"
php artisan migrate

# Build y storage
npm run build
php artisan storage:link

# Iniciar (dos terminales)
php artisan serve
npm run dev
```

Acceder en: `http://127.0.0.1:8000`

## Estructura

```
app/Http/Controllers/     - Lógica de negocio
app/Models/               - Modelos Eloquent
app/Services/             - Gestión de datos
resources/js/Pages/       - Componentes Vue
database/migrations/      - Esquema BD
routes/web.php, api.php   - Rutas
storage/app/private/      - Datos persistentes JSON
```

## Arquitectura

- **MySQL**: Usuarios, cursos, categorías, contactos
- **JSON**: Progreso, entregas, mensajes (datos transitorios)

## Licencia

Proyecto educativo - Ciclo DAW
