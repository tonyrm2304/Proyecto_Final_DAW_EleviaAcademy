# Explicación básica: Test de afinidad de cursos

Este documento explica de forma sencilla cómo funciona el test que recomienda cursos.

## 1) ¿Qué hace el test?

El test hace 4 preguntas al usuario y, con sus respuestas, calcula qué cursos encajan mejor.

Después:
- ordena los cursos por encaje,
- y muestra los 3 primeros como recomendación.

---

## 2) ¿Qué preguntas se usan?

El test usa estas 4 respuestas:

1. **Interés principal** (categoría)
2. **Nivel actual** (principiante, intermedio, avanzado)
3. **Disponibilidad de tiempo** (baja, media, alta)
4. **Enfoque** (técnico o negocio)

---

## 3) ¿Cómo se puntúa cada curso?

A cada curso se le asigna una puntuación interna (`score`).

### Reglas de puntuación

- **Interés principal**: +4 si la categoría del curso coincide.
- **Nivel**: +3 si las horas del curso encajan con el nivel.
- **Tiempo disponible**: +2 si las horas encajan con la disponibilidad.
- **Enfoque**: +3 si el tipo de curso encaja con técnico/negocio.

Puntuación máxima posible: **12 puntos**.

> Nota: actualmente esta puntuación se usa por dentro para ordenar, pero no se muestra al usuario (interfaz minimalista).

---

## 4) Flujo simple del algoritmo

1. Recorres todos los cursos.
2. Para cada curso, empiezas en `score = 0`.
3. Sumas puntos según las 4 reglas.
4. Guardas el curso con su score.
5. Ordenas de mayor a menor score.
6. Te quedas con los 3 primeros.

---

## 5) Ejemplo rápido

Si una persona selecciona:
- Interés: IA
- Nivel: intermedio
- Tiempo: media
- Enfoque: técnico

Un curso de IA, de 24h y enfoque técnico, sumará muchos puntos y quedará arriba en la recomendación.

---

## 6) ¿Dónde está implementado?

Archivo principal:
- `resources/js/Pages/Courses/Index.vue`

Bloques clave:
- estado de respuestas del orientador,
- cálculo de `recommendedCourses`,
- renderizado de “3 cursos recomendados”.

---

## 7) Cómo hacerlo tú en otro proyecto (versión básica)

1. Crea un objeto con respuestas del formulario.
2. Crea una función que reciba un curso y devuelva score.
3. Aplica esa función a todos los cursos (`map`).
4. Ordena por score descendente (`sort`).
5. Muestra los primeros 3 (`slice(0, 3)`).

Con eso ya tienes un recomendador simple, entendible y fácil de mantener.
