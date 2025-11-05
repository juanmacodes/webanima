import { ReactNode } from 'react';
import clsx from 'clsx';

interface SharedSectionProps {
  id?: string;
  headline: string;
  eyebrow?: string;
  description?: string;
  cta?: ReactNode;
  children?: ReactNode;
  className?: string;
}

export function SharedSection({
  id,
  headline,
  eyebrow,
  description,
  cta,
  children,
  className
}: SharedSectionProps) {
  return (
    <section
      id={id}
      className={clsx(
        'relative mx-auto flex w-full max-w-6xl flex-col gap-6 px-6 py-20 text-left md:flex-row md:items-start md:gap-12',
        className
      )}
    >
      <div className="md:w-1/2">
        {eyebrow ? (
          <span className="text-xs font-semibold uppercase tracking-[0.4em] text-secondary">{eyebrow}</span>
        ) : null}
        <h2 className="mt-3 text-3xl font-semibold md:text-5xl">{headline}</h2>
        {description ? <p className="mt-4 text-base text-foreground/70">{description}</p> : null}
        {cta ? <div className="mt-6 flex flex-wrap gap-3">{cta}</div> : null}
      </div>
      <div className="md:w-1/2">{children}</div>
    </section>
  );
}
