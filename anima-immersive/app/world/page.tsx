import type { Metadata } from 'next';
import { Suspense } from 'react';
import Experience from '../../components/Experience';
import type { Hotspot } from '../../components/Experience';
import { getWorldHotspots } from '../../lib/wp';

export const metadata: Metadata = {
    title: 'Anima World',
    description: 'Tour 3D interactivo con hotspots, waypoints y misiones gamificadas.',
};

// evita SSG agresivo para esta ruta 3D (puedes subir a 60/120 si prefieres)
export const revalidate = 0;

async function WorldCanvas() {
    let hotspots: Hotspot[] = [];
    try {
        hotspots = await getWorldHotspots();
    } catch {
        hotspots = [
            {
                id: 'default-node',
                title: 'Demo hotspot',
                description: 'Configura hotspots desde WordPress para personalizar el tour.',
                actionLabel: 'Configurar',
                href: '/proyectos',
                position: [0, 1.5, 0],
            },
        ];
    }
    // Renderiza el componente client dentro del server
    return <Experience hotspots={hotspots} />;
}

export default function WorldPage() {
    return (
        <main className="flex min-h-screen flex-col pt-24">
            <section className="mx-auto w-full max-w-4xl px-6 text-center">
                <h1 className="text-4xl font-semibold md:text-6xl">Explora Anima World</h1>
                <p className="mt-4 text-base text-foreground/70">
                    Recorre hotspots volumétricos, desbloquea badges y descubre integraciones. Los waypoints se cargan
                    dinámicamente desde WordPress.
                </p>
            </section>
            <div className="mt-10 h-[70vh] overflow-hidden rounded-3xl border border-white/10">
                <Suspense
                    fallback={<div className="flex h-full items-center justify-center text-sm text-foreground/60">
                        Cargando mundo...
                    </div>}
                >
                    <WorldCanvas />
                </Suspense>
            </div>
        </main>
    );
}
