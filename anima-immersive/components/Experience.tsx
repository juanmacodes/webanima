'use client';

import * as React from 'react';

export type Hotspot = {
  id: string;
  title: string;
  description?: string;
  position?: [number, number, number]; // reservado para futura integración 3D
  actionLabel?: string;
  href?: string;
};

type Props = {
  hotspots?: Hotspot[];
};

/**
 * Placeholder de experiencia 3D compatible con SSR/Static Export.
 * Más adelante aquí podemos montar @react-three/fiber o el visor que prefieras.
 */
export function Experience({ hotspots = [] }: Props) {
  return (
    <div
      className="relative h-full w-full overflow-hidden bg-[radial-gradient(ellipse_at_center,rgba(255,255,255,0.08),transparent_60%)]"
      role="img"
      aria-label="Vista previa holográfica"
    >
      {/* Fondo decorativo */}
      <div className="absolute inset-0 opacity-30"
        style={{
          background:
            'conic-gradient(from 90deg at 50% 50%, rgba(0, 220, 255, .15), rgba(255, 0, 128, .15), rgba(0, 220, 255, .15))'
        }}
      />

      {/* Hotspots sencillos (se renderizan como overlays clicables) */}
      <div className="absolute inset-0 pointer-events-none">
        {hotspots.map((h, i) => (
          <div
            key={h.id}
            className="pointer-events-auto absolute left-4 bottom-4 mb-2"
            style={{ transform: `translateY(-${i * 44}px)` }}
          >
            <div className="rounded-full border border-white/20 bg-black/40 px-3 py-1 text-xs text-white/90 backdrop-blur">
              <span className="font-medium">{h.title}</span>
              {h.description ? <span className="ml-2 opacity-70">{h.description}</span> : null}
              {h.href ? (
                <a href={h.href} className="ml-3 underline underline-offset-2">
                  {h.actionLabel ?? 'Abrir'}
                </a>
              ) : null}
            </div>
          </div>
        ))}
      </div>
    </div>
  );
}
