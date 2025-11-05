import type { Metadata } from 'next';
import dynamic from 'next/dynamic';
import Script from 'next/script';
import { SharedSection } from '../../components/SharedSection';

const ExperienceCanvas = dynamic(
  () => import('../../components/Experience').then((m) => m.Experience),
  { ssr: false }
);

export const metadata: Metadata = {
  title: 'Realidad virtual multiusuario',
  description: 'Metaversos y espacios VR sincronizados, con cross-play y analítica en tiempo real.'
};

const SERVICE_JSONLD = {
  '@context': 'https://schema.org',
  '@type': 'Service',
  name: 'Producción de VR multiusuario',
  serviceType: 'Multiuser VR production',
  provider: { '@type': 'Organization', name: 'Anima Avatar Agency' }
};

export default function VRPage() {
  return (
    <main className="pt-32">
      <SharedSection
        eyebrow="Realidad virtual"
        headline="Shows y training VR sincronizados"
        description="Espacios multiusuario con audio espacial, interacción háptica y monitoreo en vivo para eventos, onboarding y training."
        cta={
          <>
            <a className="button-primary" href="/proyectos">Casos VR</a>
            <a className="button-ghost" href="/world">Explorar World</a>
          </>
        }
      >
        <div className="card">
          <p className="text-sm text-foreground/70">
            Cross-play entre VR, desktop y mobile con servidores de estado sincronizados (WebRTC, Spatial Audio y KPIs de engagement).
          </p>

          <div className="mt-6 h-64 overflow-hidden rounded-3xl border border-white/10">
            <ExperienceCanvas
              hotspots={[
                {
                  id: 'stage',
                  title: 'Stage multiuser',
                  description: 'Stage configurable con seats y HUD compartido.',
                  position: [1.4, 1.5, -1.5],
                  actionLabel: 'Ver world',
                  href: '/world'
                }
              ]}
            />
          </div>
        </div>
      </SharedSection>

      <Script
        id="vr-service-jsonld"
        type="application/ld+json"
        dangerouslySetInnerHTML={{ __html: JSON.stringify(SERVICE_JSONLD) }}
      />
    </main>
  );
}
