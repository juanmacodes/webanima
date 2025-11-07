'use client';

import * as React from 'react';
import { Canvas } from '@react-three/fiber';
import { OrbitControls } from '@react-three/drei';

type Vec3 = [number, number, number];

export type Hotspot = {
    id: string;
    title: string;
    description?: string;
    position: Vec3;
    actionLabel?: string;
    href?: string;
};

export type ExperienceProps = {
    hotspots?: Hotspot[];
};

function HotspotSphere({ position = [0, 1.5, 0] as Vec3 }: { position?: Vec3 }) {
    return (
        <mesh position={position}>
            <sphereGeometry args={[0.12, 32, 32]} />
            <meshStandardMaterial color="#58a6ff" emissive="#1f6feb" emissiveIntensity={0.6} />
        </mesh>
    );
}

export function Experience({ hotspots = [] }: ExperienceProps) {
    return (
        <Canvas camera={{ position: [2.5, 1.8, 2.8], fov: 50 }}>
            <ambientLight intensity={0.7} />
            <directionalLight position={[3, 4, 2]} intensity={1.1} />
            <OrbitControls enableDamping />

            {/* Suelo simple */}
            <mesh rotation={[-Math.PI / 2, 0, 0]} receiveShadow>
                <planeGeometry args={[20, 20]} />
                <meshStandardMaterial color="#0b1220" />
            </mesh>

            {/* Hotspots */}
            {hotspots.map((h) => (
                <HotspotSphere key={h.id} position={h.position} />
            ))}
        </Canvas>
    );
}

/** export default + named export para evitar desajustes en el Client Manifest */
export default Experience;
