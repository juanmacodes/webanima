'use client';

import { Suspense } from 'react';
import { Canvas } from '@react-three/fiber';
import { OrbitControls } from '@react-three/drei';

export type Hotspot = {
  id: string;
  title?: string;
  description?: string;
  position?: [number, number, number];
  actionLabel?: string;
  href?: string;
};

type Props = {
  hotspots?: Hotspot[];
};

function Scene() {
  return (
    <>
      <ambientLight intensity={0.7} />
      <directionalLight position={[2, 3, 1]} intensity={1.1} />
      <mesh>
        <boxGeometry />
        <meshStandardMaterial color="#7c3aed" />
      </mesh>
      <OrbitControls enableDamping />
    </>
  );
}

export default function Experience({ hotspots = [] }: Props) {
  return (
    <div className="relative h-[70vh] w-full">
      <Canvas camera={{ position: [2.5, 1.8, 2.8], fov: 50 }}>
        <Suspense fallback={null}>
          <Scene />
        </Suspense>
      </Canvas>

      {/* Overlay simple para hotspots (opcional) */}
      {hotspots.length > 0 && (
        <div className="pointer-events-none absolute inset-0 p-4">
          {hotspots.map((h, i) => (
            <div
              key={h.id}
              className="pointer-events-auto mb-2 inline-block rounded-full border border-white/20 bg-black/40 px-3 py-1 text-xs text-white/90 backdrop-blur"
              style={{ transform: `translateY(${i * 0}px)` }}
            >
              <span className="font-medium">{h.title ?? h.id}</span>
            </div>
          ))}
        </div>
      )}
    </div>
  );
}
