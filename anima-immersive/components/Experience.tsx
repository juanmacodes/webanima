'use client';

import React, { Suspense } from 'react';
import { Canvas } from '@react-three/fiber';
import { OrbitControls } from '@react-three/drei';

/**
 * Componente interno con el contenido del lienzo 3D.
 * (Separarlo ayuda a que el wrapper pueda manejar Suspense/fallbacks fácilmente)
 */
function ExperienceImpl() {
  return (
    <Canvas className="h-full w-full">
      <ambientLight intensity={0.6} />
      <directionalLight position={[1, 2, 3]} intensity={1} />
      <mesh>
        <boxGeometry />
        <meshStandardMaterial color="hotpink" />
      </mesh>
      <OrbitControls />
    </Canvas>
  );
}

/**
 * Export default – imprescindible para que dynamic() sin .then(m => m.algo) funcione
 */
export default function Experience() {
  return (
    <div className="relative h-[60vh] w-full overflow-hidden rounded-2xl">
      <Suspense
        fallback={
          <div className="flex h-full w-full items-center justify-center text-sm text-foreground/60">
            Cargando escena 3D…
          </div>
        }
      >
        <ExperienceImpl />
      </Suspense>
    </div>
  );
}
