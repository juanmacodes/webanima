import type { Metadata } from 'next';
import Link from 'next/link';
import { getStories } from '../../lib/wp';

export const revalidate = 60;

export const metadata: Metadata = {
  title: 'Historias y bitácoras',
  description: 'Blog headless conectado a WordPress para compartir historias y actualizaciones de Anima.'
};

export default async function StoriesPage() {
  const stories = await getStories();

  return (
    <main className="pt-28">
      <header className="mx-auto w-full max-w-4xl px-6 text-center">
        <h1 className="text-4xl font-semibold md:text-6xl">Historias del laboratorio</h1>
        <p className="mt-4 text-base text-foreground/70">
          Cada entrada se hidratará con contenido HTML renderizado de forma segura usando `sanitize-html`.
        </p>
      </header>
      <section className="mx-auto mt-12 grid w-full max-w-5xl gap-6 px-6">
        {stories.map((story) => (
          <article key={story.id} className="card flex flex-col gap-4 bg-background/70">
            <div>
              <span className="text-xs uppercase tracking-[0.3em] text-secondary">
                {new Date(story.date).toLocaleDateString('es-ES', { day: '2-digit', month: 'short', year: 'numeric' })}
              </span>
              <h2 className="mt-2 text-2xl font-semibold">{story.title}</h2>
              <p className="mt-2 text-sm text-foreground/70">{story.excerpt}</p>
            </div>
            <Link className="button-primary w-max" href={`/historias/${story.slug}`}>
              Leer historia
            </Link>
          </article>
        ))}
      </section>
    </main>
  );
}
