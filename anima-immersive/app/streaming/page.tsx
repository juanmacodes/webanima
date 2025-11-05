import type { Metadata } from 'next';
import dynamic from 'next/dynamic';

export const metadata: Metadata = {
  title: 'Streaming de Avatares',
  description:
    'Avatares en vivo para Twitch/TikTok/YouTube con lip-sync y eventos.',
};

const ExperienceCanvas = dynamic(() => import('../../components/Experience'), {
  ssr: false,
  loading: () => <div className="h-[60vh] w-full bg-black/20" />,
});

export default function StreamingPage() {
  return (
    <main className="pt-28">
      <section className="mx-auto w-full max-w-5xl px-6">
        <header className="text-center">
          <h1 className="text-4xl font-semibold md:text-6xl">Streaming de Avatares</h1>
          <p className="mt-4 text-base text-foreground/70">
            Render del canvas sólo en cliente (Next dynamic import).
          </p>
        </header>

        <div className="mt-10">
          <ExperienceCanvas />
        </div>
      </section>
    </main>
  );
}
