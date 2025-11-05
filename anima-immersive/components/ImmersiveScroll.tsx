'use client';

import { useEffect, useMemo, useRef, useState } from 'react';
import Lenis from 'lenis';
import { motion } from 'framer-motion';
import { Experience, Hotspot } from './Experience';
import { useGamificationStore } from '../lib/store';

export type Chapter = {
  id: string;
  eyebrow: string;
  title: string;
  description: string;
  hotspot: Hotspot;
  ctaLabel: string;
  ctaHref: string;
};

export function ImmersiveScroll({ chapters }: { chapters: Chapter[] }) {
  const containerRef = useRef<HTMLDivElement | null>(null);
  const [activeIndex, setActiveIndex] = useState(0);
  const [hasWebGL, setHasWebGL] = useState(true);
  const addXp = useGamificationStore((state) => state.addXp);

  useEffect(() => {
    if (typeof window === 'undefined') return;
    const prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const lenis = prefersReduced
      ? null
      : new Lenis({
          smoothWheel: true,
          duration: 1.2,
          easing: (t: number) => Math.min(1, 1.001 - Math.pow(2, -10 * t))
        });
    if (!lenis) return;

    let frame: number;
    const raf = (time: number) => {
      lenis.raf(time);
      frame = requestAnimationFrame(raf);
    };
    frame = requestAnimationFrame(raf);
    return () => {
      cancelAnimationFrame(frame);
      lenis.destroy();
    };
  }, []);

  useEffect(() => {
    if (typeof window === 'undefined') return;
    try {
      const canvas = document.createElement('canvas');
      const gl = canvas.getContext('webgl') || canvas.getContext('experimental-webgl');
      if (!gl) setHasWebGL(false);
    } catch (error) {
      setHasWebGL(false);
    }
  }, []);

  useEffect(() => {
    const sections = containerRef.current?.querySelectorAll('[data-chapter]');
    if (!sections?.length) return;

    const observer = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            const index = Number((entry.target as HTMLElement).dataset.index);
            setActiveIndex(index);
            addXp(5);
          }
        });
      },
      { threshold: 0.5 }
    );

    sections.forEach((section) => observer.observe(section));
    return () => observer.disconnect();
  }, [chapters.length, addXp]);

  const hotspots = useMemo(() => chapters.map((chapter) => chapter.hotspot), [chapters]);

  return (
    <div ref={containerRef} className="relative min-h-screen">
      <div className="fixed inset-0 -z-10 h-screen w-screen">
        {hasWebGL ? (
          <Experience hotspots={hotspots} />
        ) : (
          <div className="flex h-full items-center justify-center bg-gradient-to-br from-[#0b0f17] via-[#111826] to-[#161b2c]">
            <div className="card max-w-md text-center">
              <p className="text-sm text-foreground/70">
                Tu navegador no soporta WebGL. Puedes explorar el contenido en formato tradicional o abrir esta experiencia en un
                dispositivo compatible.
              </p>
            </div>
          </div>
        )}
      </div>

      <div className="relative flex min-h-screen flex-col">
        {chapters.map((chapter, index) => {
          const isActive = activeIndex === index;
          return (
            <motion.section
              key={chapter.id}
              data-chapter
              data-index={index}
              aria-current={isActive ? 'step' : undefined}
              className="flex min-h-screen flex-col justify-center px-6"
              initial={{ opacity: 0, y: 50 }}
              whileInView={{ opacity: 1, y: 0 }}
              viewport={{ once: false, amount: 0.4 }}
              transition={{ duration: 0.8, ease: 'easeOut' }}
            >
              <div
                className="mx-auto w-full max-w-4xl rounded-3xl border bg-background/70 p-10 backdrop-blur transition"
                style={{ borderColor: isActive ? 'rgba(125, 249, 255, 0.6)' : 'rgba(255, 255, 255, 0.1)' }}
              >
                <span className="text-xs font-semibold uppercase tracking-[0.4em] text-secondary">
                  {chapter.eyebrow}
                </span>
                <h1 className="mt-4 text-4xl font-semibold md:text-6xl">{chapter.title}</h1>
                <p className="mt-4 max-w-2xl text-lg text-foreground/70">{chapter.description}</p>
                <div className="mt-8 flex flex-wrap gap-3">
                  <a className="button-primary" href={chapter.ctaHref}>
                    {chapter.ctaLabel}
                  </a>
                  <a className="button-ghost" href="#proyectos">
                    Ver proyectos
                  </a>
                </div>
                <div className="mt-6 text-xs uppercase tracking-[0.4em] text-foreground/40">
                  Capítulo {index + 1} de {chapters.length}
                </div>
              </div>
            </motion.section>
          );
        })}
      </div>
      <div className="pointer-events-none fixed bottom-10 left-1/2 hidden -translate-x-1/2 flex-col items-center text-xs text-foreground/60 md:flex">
        <span className="uppercase tracking-[0.4em]">Scroll</span>
        <motion.span
          animate={{ y: [0, 10, 0] }}
          transition={{ duration: 1.6, repeat: Infinity, ease: 'easeInOut' }}
        >
          ↓
        </motion.span>
      </div>
    </div>
  );
}
