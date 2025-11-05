// app/cursos/page.tsx
import type { Metadata } from 'next';
import Link from 'next/link';
import { Suspense } from 'react';
import { getCourses } from '../../lib/wp';

export const revalidate = 60;

export const metadata: Metadata = {
  title: 'Cursos inmersivos',
  description:
    'Formaciones sobre streaming volumétrico, hologramas, IA y VR conectadas a la plataforma Anima.',
};

type CursosPageProps = {
  searchParams: { level?: string; modality?: string };
};

// Async Server Component
async function CoursesList({
  level,
  modality,
}: {
  level?: string;
  modality?: string;
}) {
  const courses = await getCourses({ level, modality });

  return (
    <div className="mt-10 grid gap-6 md:grid-cols-2">
      {courses.map((course) => (
        <article
          key={course.id}
          className="card flex h-full flex-col justify-between bg-background/70"
        >
          <div>
            <span className="text-xs uppercase tracking-[0.3em] text-secondary">
              {course.level}
            </span>
            <h2 className="mt-3 text-2xl font-semibold">{course.title}</h2>
            <p className="mt-3 text-sm text-foreground/70">{course.excerpt}</p>
          </div>
          <div className="mt-6 flex items-center justify-between text-sm text-foreground/60">
            <span>{course.modality}</span>
            <Link className="button-primary" href={`/cursos/${course.slug}`}>
              Ver temario
            </Link>
          </div>
        </article>
      ))}
    </div>
  );
}

export default function CursosPage({ searchParams }: CursosPageProps) {
  const level = searchParams.level;
  const modality = searchParams.modality;

  return (
    <main className="pt-28">
      <section className="mx-auto w-full max-w-5xl px-6">
        <header className="text-center">
          <h1 className="text-4xl font-semibold md:text-6xl">
            Cursos y workshops
          </h1>
          <p className="mt-4 text-base text-foreground/70">
            Ajusta nivel y modalidad para consumir el contenido desde WordPress.
            Los filtros se envían como query params.
          </p>
        </header>

        <form className="mt-8 flex flex-wrap items-center justify-center gap-4" method="get">
          <label className="flex items-center gap-2 text-sm text-foreground/70">
            Nivel
            <select
              name="level"
              defaultValue={level ?? ''}
              className="rounded-xl border border-white/10 bg-background px-3 py-2 text-sm text-foreground"
            >
              <option value="">Todos</option>
              <option value="Intro">Intro</option>
              <option value="Intermedio">Intermedio</option>
              <option value="Avanzado">Avanzado</option>
            </select>
          </label>

          <label className="flex items-center gap-2 text-sm text-foreground/70">
            Modalidad
            <select
              name="modality"
              defaultValue={modality ?? ''}
              className="rounded-xl border border-white/10 bg-background px-3 py-2 text-sm text-foreground"
            >
              <option value="">Todas</option>
              <option value="Online">Online</option>
              <option value="Presencial">Presencial</option>
              <option value="Híbrido">Híbrido</option>
            </select>
          </label>

        <button type="submit" className="button-primary">
          Aplicar filtros
        </button>
      </form>

      <Suspense
        key={`${level ?? 'all'}-${modality ?? 'all'}`}
        fallback={
          <div className="mt-10 text-center text-sm text-foreground/60">
            Cargando cursos...
          </div>
        }
      >
        <CoursesList level={level} modality={modality} />
      </Suspense>
    </section>
  </main>
  );
}

