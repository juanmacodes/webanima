// components/ImmersiveScroll.tsx
'use client';

import Link from 'next/link';
import React from 'react';

export type Hotspot = {
  id: string;
  title?: string;
  description?: string;
  /** posición 3D opcional [x, y, z] */
  position?: [number, number, number];
  actionLabel?: string;
  href?: string;
};

export type Chapter = {
  id: string;
  title: string;
  eyebrow?: string;
  description?: string;

  /** CTA opcionales */
  ctaLabel?: string;
  ctaHref?: string;

  /** Hotspot opcional */
  hotspot?: Hotspot;

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
              <span className="text-xs uppercase tracking-[0.3em] text-secondary">{ch.eyebrow}</span>
            ) : null}

            <h2 className="mt-2 text-2xl font-semibold">{ch.title}</h2>

            {ch.description ? <p className="mt-3 text-foreground/70">{ch.description}</p> : null}

            {ch.content ? <div className="prose prose-invert mt-4">{ch.content}</div> : null}

            {(ch.ctaHref && ch.ctaLabel) || ch.hotspot ? (
              <div className="mt-6 flex flex-wrap items-center gap-3">
                {ch.ctaHref && ch.ctaLabel ? (
                  <Link href={ch.ctaHref} className="button-primary">
                    {ch.ctaLabel}
                  </Link>
                ) : null}

                {ch.hotspot && ch.hotspot.href && (
                  <Link href={ch.hotspot.href} className="button-secondary">
                    {ch.hotspot.actionLabel ?? 'Ver más'}
                  </Link>
                )}
              </div>
            ) : null}

            {ch.hotspot?.title || ch.hotspot?.description ? (
              <div className="mt-3 text-sm text-foreground/60">
                {ch.hotspot?.title && <strong>{ch.hotspot.title}</strong>}
                {ch.hotspot?.description && (
                  <p className="mt-1">
                    {ch.hotspot.description}
                    {ch.hotspot?.position && (
                      <span className="ml-2 opacity-70">
                        ({ch.hotspot.position.join(', ')})
                      </span>
                    )}
                  </p>
                )}
              </div>
            ) : null}
          </article>
        ))}
      </div>
    </section>
  );
};

export default ImmersiveScroll;
