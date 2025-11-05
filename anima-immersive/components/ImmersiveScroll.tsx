'use client';

import React from 'react';

export type Chapter = {
  id: string;
  title: string;
  /** etiqueta pequeña encima del título (opcional) */
  eyebrow?: string;
  /** texto descriptivo (opcional) */
  description?: string;
  /** contenido adicional renderizable (opcional) */
  content?: React.ReactNode;
};

type Props = {
  chapters: Chapter[];
  className?: string;
};

const ImmersiveScroll: React.FC<Props> = ({ chapters, className }) => {
  return (
    <section className={className ?? ''}>
      <div className="mx-auto max-w-5xl space-y-12 px-6 py-20">
        {chapters.map((ch) => (
          <article
            key={ch.id}
            className="rounded-2xl border border-white/10 bg-background/40 p-8"
          >
            {ch.eyebrow ? (
              <span className="text-xs uppercase tracking-[0.3em] text-secondary">
                {ch.eyebrow}
              </span>
            ) : null}

            <h2 className="mt-2 text-2xl font-semibold">{ch.title}</h2>

            {ch.description ? (
              <p className="mt-3 text-foreground/70">{ch.description}</p>
            ) : null}

            {ch.content ? (
              <div className="prose prose-invert mt-4">{ch.content}</div>
            ) : null}
          </article>
        ))}
      </div>
    </section>
  );
};

export default ImmersiveScroll;
