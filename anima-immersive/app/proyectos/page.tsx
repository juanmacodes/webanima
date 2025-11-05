import type { Metadata } from 'next';
import Link from 'next/link';
import { getProjects } from '../../lib/wp';

export const revalidate = 60;

export const metadata: Metadata = {
  title: 'Proyectos inmersivos',
  description: 'Colección de proyectos streaming, holográficos, IA y VR conectados a Anima Avatar Agency.'
};

export default async function ProjectsPage() {
  const projects = await getProjects();

  return (
    <main id="proyectos" className="pt-28">
      <header className="mx-auto w-full max-w-5xl px-6 text-center">
        <h1 className="text-4xl font-semibold md:text-6xl">Proyectos seleccionados</h1>
        <p className="mt-4 text-base text-foreground/70">
          Datos listos para hidratarse desde WordPress. Cada proyecto expone servicio, KPIs y contenido extendido desde el CMS.
        </p>
      </header>
      <section className="mx-auto mt-12 grid w-full max-w-6xl gap-6 px-6 md:grid-cols-2">
        {projects.map((project) => (
          <article key={project.id} className="card flex h-full flex-col justify-between border-white/10 bg-background/70">
            <div>
              <span className="text-xs font-semibold uppercase tracking-[0.4em] text-secondary">
                {project.servicio}
              </span>
              <h2 className="mt-3 text-2xl font-semibold">{project.title}</h2>
              <p className="mt-3 text-sm text-foreground/70">{project.excerpt}</p>
              {project.kpis.length ? (
                <ul className="mt-4 flex flex-wrap gap-2 text-xs text-foreground/60">
                  {project.kpis.map((kpi) => (
                    <li key={kpi} className="rounded-full border border-white/10 px-3 py-1">
                      {kpi}
                    </li>
                  ))}
                </ul>
              ) : null}
            </div>
            <div className="mt-6 flex items-center justify-between text-sm">
              <Link className="button-primary" href={`/proyectos/${project.slug}`}>
                Ver proyecto
              </Link>
              <span className="text-foreground/40">Actualizar desde WP</span>
            </div>
          </article>
        ))}
      </section>
    </main>
  );
}
