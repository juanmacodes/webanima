import type { Metadata } from 'next';
import { notFound } from 'next/navigation';
import sanitizeHtml from 'sanitize-html';
import { getStoryBySlug, getStorySlugs } from '../../../lib/wp';

export const revalidate = 60;

export async function generateStaticParams() {
  const slugs = await getStorySlugs();
  return slugs.map((slug) => ({ slug }));
}

export async function generateMetadata({ params }: { params: { slug: string } }): Promise<Metadata> {
  const story = await getStoryBySlug(params.slug);
  if (!story) {
    return { title: 'Historia no encontrada' };
  }
  return {
    title: story.title,
    description: story.excerpt,
    openGraph: {
      type: 'article',
      title: story.title,
      description: story.excerpt,
      publishedTime: story.date
    }
  };
}

export default async function StoryPage({ params }: { params: { slug: string } }) {
  const story = await getStoryBySlug(params.slug);
  if (!story) {
    notFound();
  }

  const sanitized = sanitizeHtml(story.content, {
    allowedTags: sanitizeHtml.defaults.allowedTags.concat(['img', 'video', 'iframe', 'figure', 'figcaption']),
    allowedAttributes: {
      ...sanitizeHtml.defaults.allowedAttributes,
      '*': ['class', 'style', 'data-*', 'src', 'href', 'target', 'rel']
    }
  });

  return (
    <main className="pt-28">
      <article className="mx-auto flex w-full max-w-3xl flex-col gap-6 px-6">
        <header className="border-b border-white/10 pb-6">
          <span className="text-xs uppercase tracking-[0.3em] text-secondary">
            {new Date(story.date).toLocaleDateString('es-ES', { day: '2-digit', month: 'long', year: 'numeric' })}
          </span>
          <h1 className="mt-4 text-4xl font-semibold md:text-5xl">{story.title}</h1>
        </header>
        <div className="prose prose-invert max-w-none" dangerouslySetInnerHTML={{ __html: sanitized }} />
      </article>
    </main>
  );
}
