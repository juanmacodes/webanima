export type ProjectSummary = {
  id: string;
  title: string;
  slug: string;
  excerpt: string;
  servicio: string;
  kpis: string[];
};

export type ProjectDetail = ProjectSummary & {
  year?: string;
  cliente?: string;
  stack?: string[];
  gallery?: string[];
  content?: string;
};

export type StorySummary = {
  id: string;
  title: string;
  slug: string;
  excerpt: string;
  date: string;
};

export type StoryDetail = StorySummary & {
  content: string;
};

export type CourseSummary = {
  id: string;
  title: string;
  slug: string;
  level: string;
  modality: string;
  excerpt: string;
};

export type CourseDetail = CourseSummary & {
  syllabus: { title: string; description?: string }[];
  instructors: { name: string; role?: string; avatarUrl?: string }[];
  ctaUrl?: string;
};

export type HotspotConfig = {
  id: string;
  title: string;
  description: string;
  position: [number, number, number];
  actionLabel?: string;
  href?: string;
};
