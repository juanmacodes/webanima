import { MetadataRoute } from 'next';
import { getCourseSlugs, getProjectSlugs, getStorySlugs } from '../lib/wp';

const BASE_URL = 'https://anima-immersive.vercel.app';

export default async function sitemap(): Promise<MetadataRoute.Sitemap> {
  const [projectSlugs, storySlugs, courseSlugs] = await Promise.all([
    getProjectSlugs().catch(() => []),
    getStorySlugs().catch(() => []),
    getCourseSlugs().catch(() => [])
  ]);

  const staticRoutes: MetadataRoute.Sitemap = [
    '',
    '/streaming',
    '/holograficos',
    '/ia',
    '/vr',
    '/world',
    '/proyectos',
    '/historias',
    '/cursos',
    '/app/anima-live'
  ].map((path) => ({ url: `${BASE_URL}${path}` }));

  const projectRoutes = projectSlugs.map((slug) => ({
    url: `${BASE_URL}/proyectos/${slug}`,
    changefreq: 'weekly' as const,
    priority: 0.7
  }));

  const storyRoutes = storySlugs.map((slug) => ({
    url: `${BASE_URL}/historias/${slug}`,
    changefreq: 'weekly' as const,
    priority: 0.6
  }));

  const courseRoutes = courseSlugs.map((slug) => ({
    url: `${BASE_URL}/cursos/${slug}`,
    changefreq: 'monthly' as const,
    priority: 0.6
  }));

  return [...staticRoutes, ...projectRoutes, ...storyRoutes, ...courseRoutes];
}
