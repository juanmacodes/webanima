'use client';

import Link from 'next/link';
import { useEffect, useState } from 'react';
import { motion } from 'framer-motion';
import { useGamificationStore } from '../lib/store';

const NAV_LINKS = [
  { href: '/', label: 'Inicio' },
  { href: '/streaming', label: 'Streaming' },
  { href: '/holograficos', label: 'Holográficos' },
  { href: '/ia', label: 'IA' },
  { href: '/vr', label: 'VR' },
  { href: '/world', label: 'World' },
  { href: '/proyectos', label: 'Proyectos' },
  { href: '/historias', label: 'Historias' },
  { href: '/cursos', label: 'Cursos' },
  { href: '/app/anima-live', label: 'Anima Live' }
];

export function HUD() {
  const { xp, badges } = useGamificationStore();
  const [mounted, setMounted] = useState(false);

  useEffect(() => setMounted(true), []);

  return (
    <motion.header
      className="fixed left-0 right-0 top-0 z-50 flex items-center justify-between border-b border-white/10 bg-background/60 px-6 py-4 backdrop-blur"
      initial={{ y: -100, opacity: 0 }}
      animate={{ y: 0, opacity: 1 }}
      transition={{ delay: 0.4, type: 'spring', stiffness: 120 }}
    >
      <Link href="/" className="text-sm font-semibold uppercase tracking-[0.4em] text-accent">
        Anima Immersive
      </Link>
      <nav className="hidden gap-6 text-xs md:flex">
        {NAV_LINKS.map((link) => (
          <Link key={link.href} href={link.href} className="text-foreground/80 transition hover:text-accent">
            {link.label}
          </Link>
        ))}
      </nav>
      <div className="flex items-center gap-3 text-right text-xs font-medium">
        <div>
          <span className="block text-foreground/60">XP</span>
          <span className="text-accent">{mounted ? xp : '—'}</span>
        </div>
        <div>
          <span className="block text-foreground/60">Badges</span>
          <span className="text-secondary">{mounted ? badges.length : '—'}</span>
        </div>
      </div>
    </motion.header>
  );
}
