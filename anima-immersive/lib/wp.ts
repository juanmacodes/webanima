import { gqlFetch } from './gql';
import type {
  CourseDetail,
  CourseSummary,
  HotspotConfig,
  ProjectDetail,
  ProjectSummary,
  StoryDetail,
  StorySummary
} from './types';

const PROJECT_PLACEHOLDER: ProjectDetail[] = [
  {
    id: 'placeholder-streamverse',
    title: 'Streamverse Live',
    slug: 'streamverse-live',
    excerpt: 'Festival volumétrico con 120k asistentes remotos y métricas en vivo.',
    servicio: 'Streaming',
    kpis: ['120K asistentes', '98% retención'],
    cliente: 'Festival Aurora',
    year: '2024',
    stack: ['Captura volumétrica', 'WebRTC', 'Panel IA'],
    gallery: [],
    content:
      '<p>Streamverse Live combina captura volumétrica y overlays generados por IA para ofrecer un concierto interactivo con monetización integrada.</p>'
  },
  {
    id: 'placeholder-holo',
    title: 'Holo Retail Network',
    slug: 'holo-retail-network',
    excerpt: 'Portales holográficos conectados a inventario real y analítica in-store.',
    servicio: 'Holográficos',
    kpis: ['+35% conversión', '12 tiendas'],
    cliente: 'Retail Nova',
    year: '2023',
    stack: ['Sensores LiDAR', 'CMS Headless'],
    gallery: [],
    content:
      '<p>Instalación holográfica modular con soporte para catálogos dinámicos y asistentes remotos.</p>'
  }
];

const STORY_PLACEHOLDER: StoryDetail[] = [
  {
    id: 'story-ai-director',
    title: 'Cómo entrenamos al director virtual de Anima',
    slug: 'director-virtual-anima',
    excerpt: 'Workflow de IA generativa para dirigir shows volumétricos en tiempo real.',
    date: new Date().toISOString(),
    content: '<p>Documentamos el pipeline de prompts, controladores y operadores humanos detrás del director virtual.</p>'
  },
  {
    id: 'story-world-hotspots',
    title: 'Diseñando hotspots inmersivos con WordPress',
    slug: 'hotspots-inmersivos-wordpress',
    excerpt: 'Tips para estructurar contenido 3D desde el panel editorial.',
    date: new Date().toISOString(),
    content: '<p>Exploramos cómo aprovechar ACF y GraphQL para mantener el tour inmersivo siempre actualizado.</p>'
  }
];

const COURSE_PLACEHOLDER: CourseDetail[] = [
  {
    id: 'course-streaming',
    title: 'Bootcamp de Streaming Volumétrico',
    slug: 'bootcamp-streaming-volumetrico',
    excerpt: 'Aprende a capturar, comprimir y distribuir experiencias volumétricas.',
    level: 'Intermedio',
    modality: 'Online',
    syllabus: [
      { title: 'Fundamentos volumétricos', description: 'Captura, compresión y transporte.' },
      { title: 'Integraciones en vivo', description: 'Integración con paneles de métricas y CRM.' }
    ],
    instructors: [
      { name: 'Alicia Gómez', role: 'Productora XR', avatarUrl: undefined },
      { name: 'Marco Silva', role: 'Ingeniero de streaming', avatarUrl: undefined }
    ],
    ctaUrl: 'https://animaavataragency.com/cursos'
  },
  {
    id: 'course-ia',
    title: 'Taller de Agentes IA Inmersivos',
    slug: 'taller-agentes-ia-inmersivos',
    excerpt: 'Construye agentes autónomos que habitan mundos 3D y responden al contexto.',
    level: 'Avanzado',
    modality: 'Híbrido',
    syllabus: [
      { title: 'Arquitectura de agentes', description: 'Memoria, herramientas y control de escena.' },
      { title: 'Evaluación y métricas', description: 'KPIs de agentes inmersivos.' }
    ],
    instructors: [
      { name: 'Laura Chen', role: 'Head of AI', avatarUrl: undefined }
    ],
    ctaUrl: 'https://animaavataragency.com/cursos'
  }
];

const HOTSPOTS_PLACEHOLDER: HotspotConfig[] = [
  {
    id: 'hq',
    title: 'HQ Anima',
    description: 'Centro de control para monitorear experiencias y XP de usuarios.',
    actionLabel: 'Ver panel',
    href: '/app/anima-live',
    position: [1.2, 1.6, -0.8]
  },
  {
    id: 'lab',
    title: 'Laboratorio IA',
    description: 'Experimenta con agentes, prompts y mundos generativos.',
    actionLabel: 'Entrar',
    href: '/ia',
    position: [-1.4, 1.4, 1.2]
  }
];

const stripTags = (html: string) => html.replace(/<[^>]+>/g, '');

type WpProjectNode = {
  id: string;
  slug: string;
  title: string;
  excerpt: string;
  proyectoMeta?: {
    servicio?: string;
    kpis?: string[];
    cliente?: string;
    year?: string;
    stack?: string[];
    gallery?: { sourceUrl: string }[];
  };
  content?: string;
};

type WpStoryNode = {
  id: string;
  slug: string;
  title: string;
  excerpt: string;
  date: string;
  content?: string;
};

type WpCourseNode = {
  id: string;
  slug: string;
  title: string;
  excerpt: string;
  courseMeta?: {
    level?: string;
    modality?: string;
    syllabus?: { title: string; description?: string }[];
    instructors?: { name: string; role?: string; avatar?: { sourceUrl: string } }[];
    ctaUrl?: string;
  };
};

type WpHotspotNode = {
  id: string;
  title: string;
  hotspotFields?: {
    description?: string;
    actionLabel?: string;
    link?: string;
    position?: { x: number; y: number; z: number };
  };
};

export async function getProjects(): Promise<ProjectSummary[]> {
  if (!process.env.NEXT_PUBLIC_WP) {
    return PROJECT_PLACEHOLDER.map((project) => ({
      id: project.id,
      title: project.title,
      slug: project.slug,
      excerpt: project.excerpt,
      servicio: project.servicio,
      kpis: project.kpis
    }));
  }

  try {
    const data = await gqlFetch<{ proyectos: { nodes: WpProjectNode[] } }>({
      query: /* GraphQL */ `
        query AllProyectos {
          proyectos(first: 12, where: { orderby: { field: DATE, order: DESC } }) {
            nodes {
              id
              slug
              title
              excerpt
              proyectoMeta {
                servicio
                kpis
              }
            }
          }
        }
      `,
      tags: ['proyectos']
    });

    return data.proyectos.nodes.map((node) => ({
      id: node.id,
      title: node.title,
      slug: node.slug,
      excerpt: stripTags(node.excerpt ?? ''),
      servicio: node.proyectoMeta?.servicio ?? 'Streaming',
      kpis: node.proyectoMeta?.kpis ?? []
    }));
  } catch (error) {
    return PROJECT_PLACEHOLDER.map((project) => ({
      id: project.id,
      title: project.title,
      slug: project.slug,
      excerpt: project.excerpt,
      servicio: project.servicio,
      kpis: project.kpis
    }));
  }
}

export async function getProjectSlugs(): Promise<string[]> {
  if (!process.env.NEXT_PUBLIC_WP) {
    return PROJECT_PLACEHOLDER.map((project) => project.slug);
  }
  try {
    const data = await gqlFetch<{ proyectos: { nodes: Pick<WpProjectNode, 'slug'>[] } }>({
      query: /* GraphQL */ `
        query ProyectoSlugs {
          proyectos(first: 50) {
            nodes {
              slug
            }
          }
        }
      `,
      tags: ['proyectos']
    });
    return data.proyectos.nodes.map((node) => node.slug);
  } catch (error) {
    return PROJECT_PLACEHOLDER.map((project) => project.slug);
  }
}

export async function getProjectBySlug(slug: string): Promise<ProjectDetail | null> {
  if (!process.env.NEXT_PUBLIC_WP) {
    return PROJECT_PLACEHOLDER.find((project) => project.slug === slug) ?? null;
  }
  try {
    const data = await gqlFetch<{ proyecto: WpProjectNode | null }, { slug: string }>({
      query: /* GraphQL */ `
        query ProyectoBySlug($slug: ID!) {
          proyecto(id: $slug, idType: SLUG) {
            id
            slug
            title
            excerpt
            content
            proyectoMeta {
              servicio
              kpis
              cliente
              year
              stack
              gallery {
                sourceUrl
              }
            }
          }
        }
      `,
      variables: { slug },
      tags: ['proyectos', `proyecto:${slug}`]
    });

    if (!data.proyecto) return null;
    const node = data.proyecto;

    return {
      id: node.id,
      title: node.title,
      slug: node.slug,
      excerpt: stripTags(node.excerpt ?? ''),
      servicio: node.proyectoMeta?.servicio ?? 'Streaming',
      kpis: node.proyectoMeta?.kpis ?? [],
      cliente: node.proyectoMeta?.cliente,
      year: node.proyectoMeta?.year,
      stack: node.proyectoMeta?.stack,
      gallery: node.proyectoMeta?.gallery?.map((item) => item.sourceUrl).filter(Boolean) ?? [],
      content: node.content ?? undefined
    };
  } catch (error) {
    return PROJECT_PLACEHOLDER.find((project) => project.slug === slug) ?? null;
  }
}

export async function getStories(): Promise<StorySummary[]> {
  if (!process.env.NEXT_PUBLIC_WP) {
    return STORY_PLACEHOLDER.map((story) => ({
      id: story.id,
      title: story.title,
      slug: story.slug,
      excerpt: story.excerpt,
      date: story.date
    }));
  }
  try {
    const data = await gqlFetch<{ posts: { nodes: WpStoryNode[] } }>({
      query: /* GraphQL */ `
        query BlogStories {
          posts(first: 12, where: { orderby: { field: DATE, order: DESC } }) {
            nodes {
              id
              slug
              title
              excerpt
              date
            }
          }
        }
      `,
      tags: ['stories']
    });

    return data.posts.nodes.map((node) => ({
      id: node.id,
      title: node.title,
      slug: node.slug,
      excerpt: stripTags(node.excerpt ?? ''),
      date: node.date
    }));
  } catch (error) {
    return STORY_PLACEHOLDER.map((story) => ({
      id: story.id,
      title: story.title,
      slug: story.slug,
      excerpt: story.excerpt,
      date: story.date
    }));
  }
}

export async function getStorySlugs(): Promise<string[]> {
  if (!process.env.NEXT_PUBLIC_WP) {
    return STORY_PLACEHOLDER.map((story) => story.slug);
  }
  try {
    const data = await gqlFetch<{ posts: { nodes: Pick<WpStoryNode, 'slug'>[] } }>({
      query: /* GraphQL */ `
        query StorySlugs {
          posts(first: 50) {
            nodes {
              slug
            }
          }
        }
      `,
      tags: ['stories']
    });
    return data.posts.nodes.map((node) => node.slug);
  } catch (error) {
    return STORY_PLACEHOLDER.map((story) => story.slug);
  }
}

export async function getStoryBySlug(slug: string): Promise<StoryDetail | null> {
  if (!process.env.NEXT_PUBLIC_WP) {
    return STORY_PLACEHOLDER.find((story) => story.slug === slug) ?? null;
  }
  try {
    const data = await gqlFetch<{ post: WpStoryNode | null }, { slug: string }>({
      query: /* GraphQL */ `
        query StoryBySlug($slug: ID!) {
          post(id: $slug, idType: SLUG) {
            id
            slug
            title
            excerpt
            date
            content
          }
        }
      `,
      variables: { slug },
      tags: ['stories', `story:${slug}`]
    });

    if (!data.post) return null;
    return {
      id: data.post.id,
      slug: data.post.slug,
      title: data.post.title,
      excerpt: stripTags(data.post.excerpt ?? ''),
      date: data.post.date,
      content: data.post.content ?? ''
    };
  } catch (error) {
    return STORY_PLACEHOLDER.find((story) => story.slug === slug) ?? null;
  }
}

export async function getCourses(filters?: { level?: string; modality?: string }): Promise<CourseSummary[]> {
  const applyFilters = (courses: CourseSummary[]) =>
    courses.filter((course) => {
      const matchesLevel = filters?.level ? course.level === filters.level : true;
      const matchesModality = filters?.modality ? course.modality === filters.modality : true;
      return matchesLevel && matchesModality;
    });

  if (!process.env.NEXT_PUBLIC_WP) {
    return applyFilters(
      COURSE_PLACEHOLDER.map((course) => ({
        id: course.id,
        slug: course.slug,
        title: course.title,
        excerpt: course.excerpt,
        level: course.level,
        modality: course.modality
      }))
    );
  }

  try {
    const data = await gqlFetch<{ cursos: { nodes: WpCourseNode[] } }>({
      query: /* GraphQL */ `
        query Cursos {
          cursos(first: 30) {
            nodes {
              id
              slug
              title
              excerpt
              courseMeta {
                level
                modality
              }
            }
          }
        }
      `,
      tags: ['cursos']
    });

    const courses = data.cursos.nodes.map((node) => ({
      id: node.id,
      slug: node.slug,
      title: node.title,
      excerpt: stripTags(node.excerpt ?? ''),
      level: node.courseMeta?.level ?? 'Intermedio',
      modality: node.courseMeta?.modality ?? 'Online'
    }));

    return applyFilters(courses);
  } catch (error) {
    return applyFilters(
      COURSE_PLACEHOLDER.map((course) => ({
        id: course.id,
        slug: course.slug,
        title: course.title,
        excerpt: course.excerpt,
        level: course.level,
        modality: course.modality
      }))
    );
  }
}

export async function getCourseSlugs(): Promise<string[]> {
  if (!process.env.NEXT_PUBLIC_WP) {
    return COURSE_PLACEHOLDER.map((course) => course.slug);
  }
  try {
    const data = await gqlFetch<{ cursos: { nodes: Pick<WpCourseNode, 'slug'>[] } }>({
      query: /* GraphQL */ `
        query CursoSlugs {
          cursos(first: 50) {
            nodes {
              slug
            }
          }
        }
      `,
      tags: ['cursos']
    });

    return data.cursos.nodes.map((node) => node.slug);
  } catch (error) {
    return COURSE_PLACEHOLDER.map((course) => course.slug);
  }
}

export async function getCourseBySlug(slug: string): Promise<CourseDetail | null> {
  if (!process.env.NEXT_PUBLIC_WP) {
    return COURSE_PLACEHOLDER.find((course) => course.slug === slug) ?? null;
  }
  try {
    const data = await gqlFetch<{ curso: WpCourseNode | null }, { slug: string }>({
      query: /* GraphQL */ `
        query CursoBySlug($slug: ID!) {
          curso(id: $slug, idType: SLUG) {
            id
            slug
            title
            excerpt
            courseMeta {
              level
              modality
              ctaUrl
              syllabus {
                title
                description
              }
              instructors {
                name
                role
                avatar {
                  sourceUrl
                }
              }
            }
          }
        }
      `,
      variables: { slug },
      tags: ['cursos', `curso:${slug}`]
    });

    if (!data.curso) return null;
    const node = data.curso;

    return {
      id: node.id,
      slug: node.slug,
      title: node.title,
      excerpt: stripTags(node.excerpt ?? ''),
      level: node.courseMeta?.level ?? 'Intermedio',
      modality: node.courseMeta?.modality ?? 'Online',
      syllabus: node.courseMeta?.syllabus ?? [],
      instructors:
        node.courseMeta?.instructors?.map((instructor) => ({
          name: instructor.name,
          role: instructor.role,
          avatarUrl: instructor.avatar?.sourceUrl
        })) ?? [],
      ctaUrl: node.courseMeta?.ctaUrl
    };
  } catch (error) {
    return COURSE_PLACEHOLDER.find((course) => course.slug === slug) ?? null;
  }
}

export async function getWorldHotspots(): Promise<HotspotConfig[]> {
  if (!process.env.NEXT_PUBLIC_WP) {
    return HOTSPOTS_PLACEHOLDER;
  }
  try {
    const data = await gqlFetch<{ hotspots: { nodes: WpHotspotNode[] } }>({
      query: /* GraphQL */ `
        query WorldHotspots {
          hotspots(first: 20) {
            nodes {
              id
              title
              hotspotFields {
                description
                actionLabel
                link
                position {
                  x
                  y
                  z
                }
              }
            }
          }
        }
      `,
      tags: ['hotspots']
    });

    return data.hotspots.nodes.map((node) => ({
      id: node.id,
      title: node.title,
      description: node.hotspotFields?.description ?? '',
      actionLabel: node.hotspotFields?.actionLabel,
      href: node.hotspotFields?.link ?? undefined,
      position: [
        node.hotspotFields?.position?.x ?? 0,
        node.hotspotFields?.position?.y ?? 1.5,
        node.hotspotFields?.position?.z ?? 0
      ] as [number, number, number]
    }));
  } catch (error) {
    return HOTSPOTS_PLACEHOLDER;
  }
}

export async function submitWaitlist(payload: {
  name: string;
  email: string;
  network: string;
  country: string;
  consent: boolean;
}): Promise<Response> {
  const endpoint = process.env.NEXT_PUBLIC_WP
    ? `${process.env.NEXT_PUBLIC_WP}/wp-json/anima/v1/waitlist`
    : undefined;
  if (!endpoint) {
    throw new Error('NEXT_PUBLIC_WP env variable must be set to submit the waitlist form');
  }

  const response = await fetch(endpoint, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(payload)
  });

  return response;
}
