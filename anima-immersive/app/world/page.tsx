import type { Metadata } from 'next';
import dynamic from 'next/dynamic';

export const metadata: Metadata = {
  title: 'Experiencia 3D',
  description: 'Escena WebGL interactiva.',
};

// IMPORTAMOS EL NAMED EXPORT "Experience" (ruta relativa desde /app/world)
const Experience = dynamic(
  () => import('../../components/Experience').then((m) => m.Experience),
  {
    ssr: false,
    loading: () => <div className="h-[70vh] w-full bg-black/20" />,
  }
);

export default function WorldPage() {
  return (
    <main className="pt-28">
      <section className="mx-auto h-[70vh] max-w-5xl px-6">
        <Experience />
      </section>
    </main>
  );
}
