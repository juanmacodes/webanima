import type { Metadata } from 'next';
import dynamic from 'next/dynamic';
import Script from 'next/script';
import { SharedSection } from '../../components/SharedSection';

const ExperienceCanvas = dynamic(
  () => import('../../components/Experience').then((m) => m.default),
  {
    ssr: false,
    loading: () => <div className="h-full w-full bg-black/20" />,
  }
);

export const metadata: Metadata = {
  title: 'Cabinas holográficas',
  description:
    'Alquila cabinas holográficas y proyectores volumétricos para eventos, retail y activaciones con avatares en tiempo real.',
};

const PRODUCT_JSONLD = {
  '@context': 'https://schema.org',
  '@type': 'Product',
  name: 'Cabina holográfica',
  brand: { '@type': 'Organization', name: 'Anima Avatar Agency' },
  category: 'EventRental',
  offers: {
    '@type': 'AggregateOffer',
    priceCurrency: 'EUR',
    lowPrice: '1200',
    highPrice: '8900',
    availability: 'https://schema.org/InStock',
  },
};

export default function HolograficosPage() {
  return (
    <main className="pt-32">
      <SharedSection
        eyebrow="Holográficos"
        headline="Cabinas holográficas listas para eventos"
        description="Despliegue rápido, control remoto y contenidos interactivos. Compatible con avatares y streaming volumétrico."
        cta={
          <>
            <a className="button-primary" href="/contacto">Solicitar presupuesto</a>
            <a className="button-ghost" href="/proyectos">Ver proyectos</a>
          </>
        }
      >
        <div className="card">
          <p className="text-sm text-foreground/70">
            Ofrecemos distintos tamaños, flight-case, sensores y opciones de
            interacción. Integración con Unreal, web y redes sociales.
          </p>

          <div className="mt-6 h-64 overflow-hidden rounded-3xl border border-white/10">
            <ExperienceCanvas
              hotspots={[
                {
                  id: 'setup',
                  title: 'Montaje rápido',
                  description: '2 técnicos · < 90 min',
                  actionLabel: 'Detalles',
                  href: '/proyectos',
                },
              ]}
            />
          </div>
        </div>
      </SharedSection>

      <Script
        id="holo-product-jsonld"
        type="application/ld+json"
        dangerouslySetInnerHTML={{ __html: JSON.stringify(PRODUCT_JSONLD) }}
      />
    </main>
  );
}
