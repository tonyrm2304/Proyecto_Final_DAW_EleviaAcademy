<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Insertar Categorías
        DB::table('categories')->insert([
            ['name' => 'Inteligencia Artificial', 'description' => 'Cursos enfocados en el uso de LLMs, modelado de prompts e integración de APIs de IA.'],
            ['name' => 'Desarrollo y Automatización', 'description' => 'Diseño de landing pages, flujos de trabajo eficientes y desarrollo robusto con Laravel.'],
            ['name' => 'Negocio Digital', 'description' => 'Formación en marketing online, finanzas para proyectos digitales y estrategias de crecimiento.'],
        ]);

        // ============================================================================
        // NOTA TÉCNICA:
        // Se implementa la fachada Hash::make() nativa de Laravel,garantizando la 
        // privacidad de los usuarios y permitiendo el correcto 
        // funcionamiento del sistema de autenticación nativo (Auth) del framework.
        // ============================================================================

        // 2. Insertar Usuarios
        DB::table('users')->insert([
            ['name' => 'Carlos Alumno Uno', 'email' => 'alumno1@eacademy.com', 'password' => Hash::make('ejemplo1'), 'role' => 'alumno', 'avatar' => 'avatar-alumno1.png'],
            ['name' => 'Lucía Alumna Dos', 'email' => 'alumno2@eacademy.com', 'password' => Hash::make('ejemplo2'), 'role' => 'alumno', 'avatar' => 'avatar-alumno2.png'],
            ['name' => 'Profesor Alejandro', 'email' => 'profe1@eacademy.com', 'password' => Hash::make('profe1'), 'role' => 'profesor', 'avatar' => 'avatar-profe1.png'],
            ['name' => 'Profesora Marta', 'email' => 'profe2@eacademy.com', 'password' => Hash::make('profe2'), 'role' => 'profesor', 'avatar' => 'avatar-profe2.png'],
            ['name' => 'Superprofe Administrador', 'email' => 'superprofe@eacademy.com', 'password' => Hash::make('superprofe'), 'role' => 'admin', 'avatar' => 'avatar-admin.png'],
        ]);

        // 3. Insertar Cursos
        DB::table('courses')->insert([
            ['title' => 'Introducción a la IA y Prompt Engineering', 'short_description' => 'Aprende a modelar textos y optimizar tus interacciones con modelos de lenguaje masivos.', 'long_description' => 'Este curso sienta las bases para entender cómo funcionan los modelos de IA actuales. Aprenderás técnicas avanzadas de prompt engineering para automatizar tareas, redactar contenido y programar de forma más eficiente.', 'duration_hours' => 15, 'category_id' => 1, 'teacher_id' => 3],
            ['title' => 'Integración de APIs de IA en Apps Web', 'short_description' => 'Conecta tus proyectos con las APIs de OpenAI, Claude o DeepSeek usando PHP y JS.', 'long_description' => 'Lleva tus aplicaciones web al siguiente nivel. En este curso práctico aprenderás a consumir los endpoints de las principales plataformas de IA para integrar chatbots, generadores de imágenes y análisis de texto en tus desarrollos.', 'duration_hours' => 30, 'category_id' => 1, 'teacher_id' => 3],
            ['title' => 'Diseño y Desarrollo de Landing Pages', 'short_description' => 'Maquetación avanzada con HTML5, CSS3 y componentes dinámicos de alta conversión.', 'long_description' => 'Aprende a ajustar, diseñar y desplegar páginas de aterrizaje que conviertan visitas en clientes. Analizaremos las mejores prácticas de UX/UI, optimización de velocidad de carga y adaptabilidad móvil.', 'duration_hours' => 20, 'category_id' => 2, 'teacher_id' => 4],
            ['title' => 'Automatización de Procesos No-Code', 'short_description' => 'Crea flujos de trabajo automáticos integrando webhooks, bases de datos y correos.', 'long_description' => 'Optimiza tu tiempo delegando las tareas repetitivas a la tecnología. Aprenderás a conectar diferentes herramientas de software del mercado mediante webhooks y automatizaciones lógicas sin escribir código.', 'duration_hours' => 25, 'category_id' => 2, 'teacher_id' => 4],
            ['title' => 'Fullstack Web Development con Laravel', 'short_description' => 'Desarrollo ágil de aplicaciones web comerciales utilizando la arquitectura MVC.', 'long_description' => 'El curso definitivo de backend en DAW. Aprenderás a dominar Laravel desde las rutas y controladores hasta el sistema de migraciones, ORM Eloquent, autenticación y despliegue seguro.', 'duration_hours' => 40, 'category_id' => 2, 'teacher_id' => 3],
            ['title' => 'Marketing Digital para Proyectos Online', 'short_description' => 'Domina el SEO, la analítica web y el diseño de embudos de venta modernos.', 'long_description' => 'Estrategias reales para posicionar proyectos en el entorno digital. Aprenderás a interpretar métricas de tráfico, optimizar el posicionamiento orgánico y estructurar campañas atractivas.', 'duration_hours' => 30, 'category_id' => 3, 'teacher_id' => 4],
            ['title' => 'Finanzas y Monetización Digital', 'short_description' => 'Gestión de pasarelas de pago, presupuestos y métricas clave para negocios SaaS.', 'long_description' => 'Aprende cómo gestionar la economía de una plataforma online. Veremos flujos de caja, control de suscripciones recurrentes y cómo plantear la contabilidad inicial de un proyecto tecnológico.', 'duration_hours' => 20, 'category_id' => 3, 'teacher_id' => 3],
            ['title' => 'Growth Hacking: Crecimiento Acelerado', 'short_description' => 'Técnicas de experimentación y optimización para captar usuarios rápidamente.', 'long_description' => 'Descubre la mentalidad de las startups más exitosas. Aprenderás metodologías ágiles orientadas exclusivamente a hacer crecer la base de usuarios de tu producto mediante experimentos de marketing y producto.', 'duration_hours' => 15, 'category_id' => 3, 'teacher_id' => 4],
            ['title' => 'Ciberseguridad en Plataformas Web', 'short_description' => 'Buenas prácticas de seguridad (OWASP) aplicadas al e-learning y protección de datos.', 'long_description' => 'Asegura tu código y el de tus clientes. Analizaremos las vulnerabilidades más comunes en aplicaciones web, cómo prevenir inyecciones SQL, ataques XSS y cómo aplicar normativas de privacidad vigentes.', 'duration_hours' => 25, 'category_id' => 3, 'teacher_id' => 5],
        ]);
    }
}