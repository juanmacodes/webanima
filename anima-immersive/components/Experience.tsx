'use client';

import { Suspense } from 'react';
import { Canvas } from '@react-three/fiber';
import { OrbitControls } from '@react-three/drei';

// Si tienes tu escena real, métela aquí dentro del <Suspense>
function ExperienceInner() {
  // ...tu escena three.js (meshes, models, etc.)
  return null;
}

/** Componente cliente que pinta el Canvas */
export function Experience() {
  return (
    <div className="relative h-[70vh] w-full">
      <Canvas camera={{ position: [0, 0, 4], fov: 50 }}>
        <color attach="background" args={['#030307']} />
        <ambientLight intensity={0.7} />
        <Suspense fallback={null}>
          <ExperienceInner />
        </Suspense>
        <OrbitControls enableZoom={false} />
      </Canvas>
    </div>
  );
}

// exportamos también como default para quien importe por defecto
export default Experience;
