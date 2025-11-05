'use client';

import React from 'react';

export type Chapter = {
  id: string;
  title: string;
  content?: React.ReactNode;
};

type Props = {
  chapters: Chapter[];
  className?: string;
};

const ImmersiveScroll: React.FC<Props> = ({ chapters, className }) => {
  return (
    <section className={className ?? ''}>
      {/* Render muy básico para evitar fallos de build.
         Sustituye por tu lógica real de “scroll inmersivo”. */}
      <div className="mx-auto max-w-5xl space-y-12 px-6 py-20">
        {chapters.map((ch) => (
          <article key={ch.id} className="rounded-2xl border border-white/10 bg-background/40 p-8">
            <h2 className="text-2xl font-semibold">{ch.title}</h2>
            {ch.content ? (
              <div className="prose prose-invert mt-4">{ch.content}</div>
            ) : (
              <p className="mt-4 text-foreground/70">Contenido próximamente…</p>
            )}
          </article>
        ))}
      </div>
    </section>
  );
};

export default ImmersiveScroll;
