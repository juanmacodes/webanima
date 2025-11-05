'use client';

import dynamic from 'next/dynamic';

// IMPORTAMOS EL NAMED EXPORT "Experience"
const Experience = dynamic(
  () => import('./Experience').then((m) => m.Experience),
  {
    ssr: false,
    loading: () => <div className="h-[70vh] w-full bg-black/20" />,
  }
);

export default function ImmersiveScroll() {
  return <Experience />;
}
