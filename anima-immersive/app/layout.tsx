import './globals.css';
import type { Metadata } from 'next';
import { Inter } from 'next/font/google';
import Script from 'next/script';
import { Suspense } from 'react';
import type { ReactNode } from 'react';
import { HUD } from '../components/HUD';

const inter = Inter({ subsets: ['latin'], variable: '--font-sans' });

export const metadata: Metadata = {
  metadataBase: new URL('https://anima-immersive.vercel.app'),
  title: {
    default: 'Anima Immersive Experiences',
    template: '%s · Anima Immersive'
  },
  description:
    'Explora experiencias streaming, holográficas, IA y VR diseñadas por Anima Avatar Agency. Historias interactivas, proyectos y cursos con un canvas 3D envolvente.',
  openGraph: {
    title: 'Anima Immersive Experiences',
    description:
      'Plataforma inmersiva con storytelling 3D, proyectos y cursos impulsados por WordPress headless.',
    url: 'https://anima-immersive.vercel.app',
    siteName: 'Anima Immersive',
    locale: 'es_ES',
    type: 'website'
  },
  twitter: {
    card: 'summary_large_image',
    title: 'Anima Immersive Experiences',
    description:
      'Descubre el universo inmersivo de Anima Avatar Agency con WebGL, streaming holográfico e IA.'
  }
};

const ORGANIZATION_JSONLD = {
  '@context': 'https://schema.org',
  '@type': 'Organization',
  name: 'Anima Avatar Agency',
  url: 'https://anima-immersive.vercel.app',
  logo: `${process.env.NEXT_PUBLIC_CDN ?? 'https://cdn.animaavataragency.com'}/brand/logo.svg`,
  sameAs: [
    'https://www.youtube.com/@animaavataragency',
    'https://www.instagram.com/animaavataragency'
  ]
};

export default function RootLayout({
  children
}: {
  children: ReactNode;
}) {
  return (
    <html lang="es" className={inter.variable}>
      <body>
        <Suspense fallback={null}>
          <HUD />
        </Suspense>
        {children}
        <Script
          id="organization-jsonld"
          type="application/ld+json"
          dangerouslySetInnerHTML={{ __html: JSON.stringify(ORGANIZATION_JSONLD) }}
        />
      </body>
    </html>
  );
}
