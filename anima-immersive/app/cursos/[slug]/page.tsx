import type { Metadata } from 'next';
import { notFound } from 'next/navigation';
import { getCourseBySlug, getCourseSlugs } from '../../../lib/wp';

export const revalidate = 60;

export async function generateStaticParams() {
  const slugs = await getCourseSlugs();
  return slugs.map((slug) => ({ slug }));
}

export async function generateMetadata({ params }: { params: { slug: string } }): Promise<Metadata> {
  const course = await getCourseBySlug(params.slug);
  if (!course) {
    return { title: 'Curso no encontrado' };
  }
  return {
    title: course.title,
    description: course.excerpt
  };
}

export default async function CoursePage({ params }: { params: { slug: string } }) {
  const course = await getCourseBySlug(params.slug);
  if (!course) {
    notFound();
  }

  return (
    <main className="pt-28">
      <article className="mx-auto flex w-full max-w-4xl flex-col gap-8 px-6">
        <header className="border-b border-white/10 pb-6">
          <span className="text-xs uppercase tracking-[0.3em] text-secondary">{course.level}</span>
          <h1 className="mt-3 text-4xl font-semibold md:text-5xl">{course.title}</h1>
          <p className="mt-3 text-base text-foreground/70">{course.excerpt}</p>
        </header>
        <section>
          <h2 className="text-xl font-semibold">Temario</h2>
          <div className="mt-4 space-y-3">
            {course.syllabus.map((item, index) => (
              <details key={item.title ?? index} className="rounded-2xl border border-white/10 bg-background/60 p-4">
                <summary className="cursor-pointer text-sm font-semibold text-foreground">
                  {index + 1}. {item.title}
                </summary>
                {item.description ? (
                  <p className="mt-2 text-sm text-foreground/70">{item.description}</p>
                ) : null}
              </details>
            ))}
          </div>
        </section>
        {course.instructors.length ? (
          <section>
            <h2 className="text-xl font-semibold">Instructores</h2>
            <div className="mt-4 grid gap-4 md:grid-cols-2">
              {course.instructors.map((instructor) => (
                <div key={instructor.name} className="card flex items-center gap-4 bg-background/60">
                  <div className="h-16 w-16 overflow-hidden rounded-full border border-white/10 bg-white/5" aria-hidden>
                    {instructor.avatarUrl ? (
                      <img src={instructor.avatarUrl} alt={instructor.name} className="h-full w-full object-cover" loading="lazy" />
                    ) : null}
                  </div>
                  <div>
                    <p className="text-sm font-semibold">{instructor.name}</p>
                    <p className="text-xs text-foreground/60">{instructor.role ?? 'Experto Anima'}</p>
                  </div>
                </div>
              ))}
            </div>
          </section>
        ) : null}
        {course.ctaUrl ? (
          <a className="button-primary w-max" href={course.ctaUrl}>
            Inscribirme
          </a>
        ) : null}
      </article>
    </main>
  );
}
