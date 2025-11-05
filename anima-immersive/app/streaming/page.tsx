import type { Metadata } from 'next';
import dynamic from 'next/dynamic';
import Script from 'next/script';
import { SharedSection } from '../../components/SharedSection';

const ExperienceCanvas = dynamic(
  () => import('../../components/Experience').then((m) => m.Experience),
  { ssr: false }
);

export const metadata: Metadata = {
  title: 'Streaming volumétrico',
  description:
    'Producción y distribución de streaming volumétrico en tiempo real con métricas interactivas y monetización integrada.'
};

const SERVICE_JSONLD = {
  '@context': 'https://schema.org',
  '@type': 'Service',
  name: 'Streaming volumétrico',
  serviceType: 'Live volumetric streaming',
  provider: { '@type': 'Organization', name: 'Anima Avatar Agency' },
  areaServed: 'Global'
};

export default function StreamingPage() {
  return (
    <main className="pt-32">
      <SharedSection
        eyebrow="Streaming volumétrico"
        headline="Escenarios volumétricos en vivo"
        description="Produce conciertos, keynotes y shows multicámara con captura volumétrica y distribución global optimizada."
        cta={
          <>
            <a className="button-primary" href="/proyectos">Casos de éxito</a>
            <a className="button-ghost" href="/app/anima-live">Conoce Anima Live</a>
          </>
        }
      >
        <div className="card">
          <p className="text-sm text-foreground/70">
            Cámaras volumétricas, overlays generados por IA, métricas en vivo y comandos OSC para escenarios híbridos.
          </p>

          <div className="mt-6 h-64 overflow-hidden rounded-3xl border border-white/10">
            <ExperienceCanvas
              hotspots={[
                {
                  id: 'analytics',
                  title: 'Live analytics',
                  description: 'KPIs volumétricos en tiempo real conectados a CRM.',
                  position: [1.2, 1.4, -0.8],
                  actionLabel: 'Dashboard',
                  href: '/proyectos'
                }
              ]}
            />
          </div>
        </div>
      </SharedSection>

      <Script
        id="streaming-service-jsonld"
        type="application/ld+json"
        dangerouslySetInnerHTML={{ __html: JSON.stringify(SERVICE_JSONLD) }}
      />
    </main>
  );
}
