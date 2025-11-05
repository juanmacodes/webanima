import type { Metadata } from 'next';
import dynamic from 'next/dynamic';
import Script from 'next/script';
import { SharedSection } from '../../components/SharedSection';

const ExperienceCanvas = dynamic(
  () => import('../../components/Experience').then((m) => m.Experience),
  { ssr: false }
);

export const metadata: Metadata = {
  title: 'Cabinas y escenarios holográficos',
  description:
    'Instalaciones holográficas para eventos, retail y museografía. Avatares en vivo, tele-presencia y contenidos volumétricos.'
};

const SERVICE_JSONLD = {
  '@context': 'https://schema.org',
  '@type': 'Service',
  name: 'Cabinas holográficas',
  serviceType: 'Holographic booth / Holostage',
  provider: { '@type': 'Organization', name: 'Anima Avatar Agency' },
  areaServed: 'EU • LATAM'
};

export default function HolograficosPage() {
  return (
    <main className="pt-32">
      <SharedSection
        eyebrow="Holográficos"
        headline="Cabinas y holostages listos para tu evento"
        description="Tele-presencia, keynotes y activaciones retail con avatares en tiempo real. Producción llave en mano."
        cta={
          <>
            <a className="button-primary" href="/proyectos">Ver montajes</a>
            <a className="button-ghost" href="/contacto">Pedir demo</a>
          </>
        }
      >
        <div className="card">
          <p className="text-sm text-foreground/70">
            Cabinas plug-and-play, integración con Unreal/Unity, control por OSC/MIDI y gráficos en vivo sincronizados con
            CRM/analytics.
          </p>

          <div className="mt-6 h-64 overflow-hidden rounded-3xl border border-white/10">
            <ExperienceCanvas
              hotspots={[
                {
                  id: 'booth',
                  title: 'Holo-booth',
                  description: 'Cabina 2×3 m con set de cámara y key inteligente.',
                  position: [1.1, 1.2, -1.0],
                  actionLabel: 'Ficha técnica',
                  href: '/proyectos'
                }
              ]}
            />
          </div>
        </div>
      </SharedSection>

      <Script
        id="holographic-service-jsonld"
        type="application/ld+json"
        dangerouslySetInnerHTML={{ __html: JSON.stringify(SERVICE_JSONLD) }}
      />
    </main>
  );
}
