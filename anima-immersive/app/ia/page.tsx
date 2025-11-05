import type { Metadata } from 'next';
import dynamic from 'next/dynamic';

export const metadata: Metadata = {
  title: 'IA conversacional',
  description:
    'Agentes con memoria y voz conectados a personajes/avatares.',
};

const ExperienceCanvas = dynamic(() => import('../../components/Experience'), {
  ssr: false,
  loading: () => <div className="h-[60vh] w-full bg-black/20" />,
});

export default function IAPage() {
  return (
    <main className="pt-28">
      <section className="mx-auto w-full max-w-5xl px-6">
        <header className="text-center">
          <h1 className="text-4xl font-semibold md:text-6xl">IA Conversacional</h1>
          <p className="mt-4 text-base text-foreground/70">
            Demostración de IA + escena 3D cliente.
          </p>
        </header>

        <div className="mt-10">
          <ExperienceCanvas />
        </div>
      </section>
    </main>
  );
}
