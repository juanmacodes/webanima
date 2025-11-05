// app/world/page.tsx
import type { Metadata } from 'next';
import dynamic from 'next/dynamic';

export const metadata: Metadata = {
  title: 'Anima World',
  description: 'Tour 3D interactivo con hotspots.',
};

// evita SSG/ISR agresivo en esta ruta 3D
export const revalidate = 0;

// CARGA ÚNICAMENTE EN CLIENTE (importantísimo)
const ExperienceCanvas = dynamic(
  () => import('../../components/Experience').then((m) => m.default),
  {
    ssr: false,
    loading: () => (
      <div className="flex h-[70vh] w-full items-center justify-center rounded-2xl border border-white/10 bg-black/20 text-sm text-foreground/60">
        Cargando mundo…
      </div>
    ),
  }
);

export default function WorldPage() {
  // Si en el futuro quieres hotspots desde WP, cárgalos en el cliente o pásalos serializables aquí
  const HOTSPOTS = [
    { id: 'demo', title: 'Hotspot demo', position: [0, 1.5, 0] as [number, number, number] },
  ];

  return (
    <main className="pt-28">
      <section className="mx-auto w-full max-w-6xl px-6 text-center">
        <h1 className="text-4xl font-semibold md:text-6xl">Explora Anima World</h1>
        <p className="mt-4 text-base text-foreground/70">
          Render del canvas 3D solo en cliente (Next dynamic import).
        </p>
      </section>

      <div className="mx-auto mt-10 max-w-6xl">
        <ExperienceCanvas hotspots={HOTSPOTS} />
      </div>
    </main>
  );
}
