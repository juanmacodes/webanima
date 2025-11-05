'use client';

import { Canvas } from '@react-three/fiber';
import { Html, OrbitControls, Stars } from '@react-three/drei';
import { Suspense, useMemo } from 'react';
import { motion } from 'framer-motion';
import { useGamificationStore } from '../lib/store';

export type Hotspot = {
  id: string;
  position: [number, number, number];
  title: string;
  description: string;
  actionLabel?: string;
  href?: string;
};

function HotspotMarker({ hotspot }: { hotspot: Hotspot }) {
  const grantBadge = useGamificationStore((state) => state.grantBadge);

  const handleSelect = () => {
    grantBadge({ id: hotspot.id, title: hotspot.title, description: hotspot.description });
    if (hotspot.href) {
      window.open(hotspot.href, '_blank', 'noopener');
    }
  };

  return (
    <Html
      position={hotspot.position}
      className="group"
      center
      transform
      occlude
      distanceFactor={6}
    >
      <motion.button
        type="button"
        role="button"
        tabIndex={0}
        onClick={handleSelect}
        whileHover={{ scale: 1.08 }}
        whileTap={{ scale: 0.92 }}
        className="card flex w-56 flex-col gap-2 bg-background/90 text-left"
      >
        <span className="text-xs font-semibold uppercase tracking-[0.3em] text-accent">{hotspot.title}</span>
        <p className="text-sm text-foreground/80">{hotspot.description}</p>
        {hotspot.actionLabel ? (
          <span className="text-xs text-secondary">{hotspot.actionLabel}</span>
        ) : null}
      </motion.button>
    </Html>
  );
}

export function Experience({ hotspots = [] }: { hotspots?: Hotspot[] }) {
  const normalizedHotspots = useMemo(() => hotspots, [hotspots]);

  return (
    <Canvas camera={{ position: [0, 3, 8], fov: 60 }} className="pointer-events-auto">
      <color attach="background" args={[0.02, 0.05, 0.09]} />
      <ambientLight intensity={0.6} />
      <directionalLight position={[5, 10, 3]} intensity={1} />
      <mesh rotation={[-Math.PI / 2, 0, 0]} receiveShadow>
        <planeGeometry args={[40, 40]} />
        <meshStandardMaterial color="#111827" />
      </mesh>
      <Suspense fallback={null}>
        <Stars radius={80} depth={50} factor={4} fade speed={1} />
        {normalizedHotspots.map((hotspot) => (
          <HotspotMarker key={hotspot.id} hotspot={hotspot} />
        ))}
      </Suspense>
      <OrbitControls enablePan enableZoom={false} />
    </Canvas>
  );
}
