import type { Metadata } from 'next';
import dynamic from 'next/dynamic';
import Script from 'next/script';
import { SharedSection } from '../../components/SharedSection';

const ExperienceCanvas = dynamic(() => import('../../components/Experience').then((mod) => mod.Experience), {
  ssr: false
});

export const metadata: Metadata = {
  title: 'Hologramas interactivos',
  description:
    'Instalaciones holográficas con interacción bidireccional, sensores y flujos comerciales integrados en CRM.'
};

const SERVICE_JSONLD = {
  '@context': 'https://schema.org',
  '@type': 'Service',
  name: 'Instalaciones holográficas',
  serviceType: 'Holographic telepresence',
  provider: {
    '@type': 'Organization',
    name: 'Anima Avatar Agency'
  }
};

export default function HolographicsPage() {
  return (
    <main className="pt-32">
      <SharedSection
        eyebrow="Hologramas"
        headline="Portales holográficos listos para desplegar"
        description="Teletransporta talento, expertos y productos a tu espacio físico con sensores de proximidad y captura volumétrica."
        cta={
          <>
            <a className="button-primary" href="/world">
              Tour holográfico
            </a>
            <a className="button-ghost" href="/proyectos">
              Proyectos holográficos
            </a>
          </>
        }
      >
        <div className="card">
          <p className="text-sm text-foreground/70">
            Configura hologramas bi-direccionales, triggers de contenido y sincronización con plataformas de e-commerce. Controla
            sensores LiDAR, iluminación y audio espacial desde un único panel.
          </p>
          <div className="mt-6 h-64 overflow-hidden rounded-3xl border border-white/10">
            <ExperienceCanvas
              hotspots={[
                {
                  id: 'retail',
                  title: 'Retail portal',
                  description: 'Hotspot que conecta inventario en vivo con demostraciones holográficas.',
                  position: [-1.2, 1.6, 0.4],
                  actionLabel: 'Ver blueprint',
                  href: '/proyectos'
                }
              ]}
            />
          </div>
        </div>
      </SharedSection>
      <Script id="holograficos-service-jsonld" type="application/ld+json" dangerouslySetInnerHTML={{ __html: JSON.stringify(SERVICE_JSONLD) }} />
    </main>
  );
}
