import type { Metadata } from 'next';
import dynamic from 'next/dynamic';
import Script from 'next/script';
import { SharedSection } from '../../components/SharedSection';

const ExperienceCanvas = dynamic(
  () => import('../../components/Experience').then((m) => m.Experience),
  { ssr: false }
);

export const metadata: Metadata = {
  title: 'Inteligencia artificial inmersiva',
  description:
    'Diseña clones digitales, asistentes multimodales y mundos generados por IA conectados a datos en tiempo real.'
};

const SERVICE_JSONLD = {
  '@context': 'https://schema.org',
  '@type': 'Service',
  name: 'Experiencias con inteligencia artificial',
  serviceType: 'AI driven immersive design',
  provider: { '@type': 'Organization', name: 'Anima Avatar Agency' }
};

export default function IAPage() {
  return (
    <main className="pt-32">
      <SharedSection
        eyebrow="Inteligencia artificial"
        headline="IA que diseña y opera tus mundos"
        description="Orquesta pipelines de IA generativa, simulaciones de agentes y asistentes volumétricos conectados a tus APIs."
        cta={
          <>
            <a className="button-primary" href="/historias">Leer historias</a>
            <a className="button-ghost" href="/cursos">Cursos de IA inmersiva</a>
          </>
        }
      >
        <div className="card">
          <p className="text-sm text-foreground/70">
            Combina modelos de lenguaje, visión y audio con controladores
            físicos. Diseña NPCs con personalidad, I+D y experiencias
            autogeneradas basadas en contexto.
          </p>
          <div className="mt-6 h-64 overflow-hidden rounded-3xl border border-white/10">
            <ExperienceCanvas
              hotspots={[
                {
                  id: 'agents',
                  title: 'AI Agents',
                  description: 'Agentes autónomos con memoria y control de escena.',
                  actionLabel: 'Ver flujo',
                  href: '/cursos'
                }
              ]}
            />
          </div>
        </div>
      </SharedSection>

      <Script
        id="ia-service-jsonld"
        type="application/ld+json"
        dangerouslySetInnerHTML={{ __html: JSON.stringify(SERVICE_JSONLD) }}
      />
    </main>
  );
}
