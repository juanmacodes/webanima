'use client';

import Link from 'next/link';
import React from 'react';

export type Hotspot = {
  id: string;
  /** Posiciones opcionales si las usas para anclar algo en 2D/3D */
  x?: number;
  y?: number;
};

export type Chapter = {
  id: string;
  title: string;
  /** Etiqueta pequeña opcional */
  eyebrow?: string;
  /** Texto descriptivo opcional */
  description?: string;

  /** CTA opcionales */
  ctaLabel?: string;
  ctaHref?: string;

  /** Punto interactivo opcional */
  hotspot?: Hotspot;

  /** Contenido adicional opcional */
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
            data-hotspot-id={ch.hotspot?.id}
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

            {ch.ctaHref && ch.ctaLabel ? (
              <div className="mt-6">
                <Link href={ch.ctaHref} className="button-primary">
                  {ch.ctaLabel}
                </Link>
              </div>
            ) : null}
          </article>
        ))}
      </div>
    </section>
  );
};

export default ImmersiveScroll;
