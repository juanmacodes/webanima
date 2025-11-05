import type { Metadata } from 'next';
import Script from 'next/script';
import { ImmersiveScroll, Chapter } from '../components/ImmersiveScroll';

const CHAPTERS: Chapter[] = [
  {
    id: 'streaming',
    eyebrow: 'Capítulo 1',
    title: 'Streaming volumétrico en tiempo real',
    description:
      'Transmite conciertos inmersivos y keynotes holográficas con cámaras volumétricas y compresión de baja latencia.',
    ctaLabel: 'Explorar streaming',
    ctaHref: '/streaming',
    hotspot: {
      id: 'streaming-hub',
      title: 'Anima Stream Hub',
      description: 'Panel en vivo para productores y artistas con métricas volumétricas.',
      position: [2, 1.5, -1],
      actionLabel: 'Ver flujo',
      href: '/streaming'
    }
  },
  {
    id: 'holographics',
    eyebrow: 'Capítulo 2',
    title: 'Hologramas que habitan tus espacios',
    description:
      'Instala portales holográficos en retail, educación o entretenimiento con interacción bidireccional y telepresencia.',
    ctaLabel: 'Explorar holográficos',
    ctaHref: '/holograficos',
    hotspot: {
      id: 'portal-ar',
      title: 'Portal Retail',
      description: 'Configura hotspots físicos y flujos de compra conectados con CRM.',
      position: [-1.5, 1.2, 1.5],
      actionLabel: 'Abrir demo',
      href: '/holograficos'
    }
  },
  {
    id: 'ia',
    eyebrow: 'Capítulo 3',
    title: 'IA generativa que diseña experiencias',
    description:
      'Crea asistentes personalizados, clones digitales y mundos generados por prompts conectados a datos reales.',
    ctaLabel: 'Explorar IA',
    ctaHref: '/ia',
    hotspot: {
      id: 'ia-orchestrator',
      title: 'AI Orchestrator',
      description: 'Orquesta modelos de lenguaje, visión y audio desde un mismo canvas.',
      position: [0, 2, 0],
      actionLabel: 'Ver casos',
      href: '/ia'
    }
  },
  {
    id: 'vr',
    eyebrow: 'Capítulo 4',
    title: 'VR multiusuario para eventos y formación',
    description:
      'Escenarios inmersivos listos para onboarding, training y shows interactivos con cross-play entre dispositivos.',
    ctaLabel: 'Explorar VR',
    ctaHref: '/vr',
    hotspot: {
      id: 'vr-stage',
      title: 'Stage VR',
      description: 'Escenario multiusuario con spatial audio y sincronización de avatares.',
      position: [-2, 1.8, -2],
      actionLabel: 'Entrar al stage',
      href: '/vr'
    }
  }
];

export const metadata: Metadata = {
  title: 'Experiencias inmersivas que cuentan historias',
  description:
    'Anima Immersive une streaming volumétrico, hologramas, IA y VR en una narrativa cinemática controlada por scroll.'
};

const SERVICE_JSONLD = {
  '@context': 'https://schema.org',
  '@type': 'Service',
  name: 'Producción de experiencias inmersivas',
  provider: {
    '@type': 'Organization',
    name: 'Anima Avatar Agency'
  },
  areaServed: 'Global',
  serviceType: ['Streaming volumétrico', 'Hologramas', 'Inteligencia artificial', 'Realidad virtual']
};

export default function Page() {
  return (
    <main className="relative min-h-screen">
      <ImmersiveScroll chapters={CHAPTERS} />
      <Script id="service-jsonld" type="application/ld+json" dangerouslySetInnerHTML={{ __html: JSON.stringify(SERVICE_JSONLD) }} />
    </main>
  );
}
