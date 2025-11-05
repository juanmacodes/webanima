// ❌ import type { Metadata } from 'next';
import Script from 'next/script';
import { AnimaLiveLanding } from '../../../components/AnimaLiveLanding';

// Sin tipar Metadata (o usa `as const` si prefieres inmutabilidad)
export const metadata = {
  title: 'Anima Live',
  description:
    'Landing del producto Anima Live con waitlist y descripción de features, integraciones y planes.',
} as const;

const SOFTWARE_JSONLD = {
  '@context': 'https://schema.org',
  '@type': 'SoftwareApplication',
  name: 'Anima Live',
  applicationCategory: 'WebApplication',
  offers: {
    '@type': 'Offer',
    availability: 'https://schema.org/PreOrder',
  },
  operatingSystem: 'Web',
  creator: {
    '@type': 'Organization',
    name: 'Anima Avatar Agency',
  },
};

export default function AnimaLivePage() {
  return (
    <main className="pt-28">
      <AnimaLiveLanding />
      <Script
        id="software-jsonld"
        type="application/ld+json"
        dangerouslySetInnerHTML={{ __html: JSON.stringify(SOFTWARE_JSONLD) }}
      />
    </main>
  );
}
