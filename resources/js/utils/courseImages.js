const normalizeTitle = (value) =>
    String(value ?? '')
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .toLowerCase()
        .replace(/[^a-z0-9]+/g, ' ')
        .trim();

const COURSE_IMAGE_MAP = {
    'integracion de apis de ia en apps web': 'APIs_Integracion.jpg',
    'automatizacion de procesos no code': 'Automatizacion_No_Code.jpg',
    'ciberseguridad en plataformas web': 'Ciberseguridad.jpg',
    'growth hacking crecimiento acelerado': 'Growth_Hacking.jpg',
    'diseno y desarrollo de landing pages': 'Landing_Pages.jpg',
    'fullstack web development con laravel': 'Laravel.jpg',
    'marketing digital para proyectos online': 'Marketing_Digital.jpg',
    'finanzas y monetizacion digital': 'Monetizacion_Digital.jpg',
    'introduccion a la ia y prompt engineering': 'Prompt_Engineering.jpg',
};

export const courseImageByTitle = (title) => {
    const normalized = normalizeTitle(title);
    const fileName = COURSE_IMAGE_MAP[normalized];

    return fileName
        ? `/imagenes/cursos_imagenes/${fileName}`
        : '/imagenes/logo/Logo_EA.jpg';
};

export const getCourseImage = (course) => {
    if (course.image_path) {
        return `/storage/${course.image_path}`;
    }
    return courseImageByTitle(course.title);
};
