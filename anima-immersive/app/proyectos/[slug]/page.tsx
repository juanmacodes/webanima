import type { Metadata } from 'next';
import { notFound } from 'next/navigation';
import sanitizeHtml from 'sanitize-html';
import { getProjectBySlug, getProjectSlugs } from '../../../lib/wp';

export const revalidate = 60;

export async function generateStaticParams() {
  const slugs = await getProjectSlugs();
  return slugs.map((slug) => ({ slug }));
}

export async function generateMetadata({ params }: { params: { slug: string } }): Promise<Metadata> {
  const project = await getProjectBySlug(params.slug);
  if (!project) {
    return {
      title: 'Proyecto no encontrado'
    };
  }
  return {
    title: project.title,
    description: project.excerpt
  };
}

export default async function ProjectPage({ params }: { params: { slug: string } }) {
  const project = await getProjectBySlug(params.slug);
  if (!project) {
    notFound();
  }

  const sanitized = project.content
    ? sanitizeHtml(project.content, {
        allowedTags: sanitizeHtml.defaults.allowedTags.concat(['img', 'video', 'iframe']),
        allowedAttributes: { ...sanitizeHtml.defaults.allowedAttributes, '*': ['class', 'style', 'data-*'] }
      })
    : '';

  return (
    <main className="pt-28">
      <article className="mx-auto flex w-full max-w-5xl flex-col gap-8 px-6">
        <header>
          <span className="text-xs font-semibold uppercase tracking-[0.4em] text-secondary">
            {project.servicio}
          </span>
          <h1 className="mt-4 text-4xl font-semibold md:text-6xl">{project.title}</h1>
          <p className="mt-4 text-base text-foreground/70">{project.excerpt}</p>
          <dl className="mt-6 grid gap-4 text-sm text-foreground/60 md:grid-cols-3">
            {project.cliente ? (
              <div>
                <dt className="uppercase tracking-[0.3em] text-foreground/40">Cliente</dt>
                <dd>{project.cliente}</dd>
              </div>
            ) : null}
            {project.year ? (
              <div>
                <dt className="uppercase tracking-[0.3em] text-foreground/40">Año</dt>
                <dd>{project.year}</dd>
              </div>
            ) : null}
            {project.stack?.length ? (
              <div>
                <dt className="uppercase tracking-[0.3em] text-foreground/40">Stack</dt>
                <dd>{project.stack.join(', ')}</dd>
              </div>
            ) : null}
          </dl>
        </header>
        {sanitized ? (
          <div className="prose prose-invert max-w-none" dangerouslySetInnerHTML={{ __html: sanitized }} />
        ) : null}
        {project.gallery?.length ? (
          <div className="grid gap-4 md:grid-cols-2">
            {project.gallery.map((url) => (
              <div key={url} className="relative aspect-video overflow-hidden rounded-3xl border border-white/10">
                <img src={url} alt={project.title} className="h-full w-full object-cover" loading="lazy" />
              </div>
            ))}
          </div>
        ) : null}
      </article>
    </main>
  );
}
