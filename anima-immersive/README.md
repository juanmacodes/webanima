# Anima Immersive Starter

Starter kit for building an immersive storytelling experience with Next.js 14, React Three Fiber and a headless WordPress backend.

## Stack

- Next.js 14 (App Router, TypeScript)
- React 18 with React Three Fiber, Drei and Three.js
- Tailwind CSS design system (dark neon theme)
- Zustand for persistent XP/badges state
- Framer Motion + Lenis for cinematic scroll interactions
- Custom GraphQL helper for consuming the Anima WordPress instance

## Getting Started

```bash
pnpm install
pnpm dev
```

Visit `http://localhost:3000` to explore the immersive landing.

### Production build & ISR

```bash
pnpm build
pnpm start
```

Static assets are revalidated (ISR) every 60 seconds for project, course and story detail pages.

## Environment Variables

Create a `.env.local` file with at least:

```
NEXT_PUBLIC_WP=https://wp.animaavataragency.com
NEXT_PUBLIC_CDN=https://cdn.animaavataragency.com
```

`NEXT_PUBLIC_WP` is used for both GraphQL queries (`/graphql`) and REST endpoints (waitlist submission).
`NEXT_PUBLIC_CDN` powers optional asset downloads and runtime references for 3D models, HDRIs or media.

## WordPress Integration

- GraphQL endpoint: `${NEXT_PUBLIC_WP}/graphql`
- Waitlist endpoint: `${NEXT_PUBLIC_WP}/wp-json/anima/v1/waitlist`
- Waypoints/hotspots JSON: host via WordPress Media or Advanced Custom Fields returning a JSON URL

Configure WordPress to expose custom post types (`proyecto`, `curso`, etc.) and ACF fields that match the queries defined in `lib/wp.ts` and `lib/types.ts`.

## Assets & CDN

- Place custom 3D assets in the CDN referenced by `NEXT_PUBLIC_CDN`
- Use `bin/fetch_assets.sh` (runs on `postinstall`) to grab lightweight placeholders for development
- Reference CDN assets inside scenes by building absolute URLs with `process.env.NEXT_PUBLIC_CDN`

## Extending Content

### Story Chapters & Scroll Experience

Update the `CHAPTERS` array in `app/page.tsx` to add or reorder narrative beats. Connect each chapter to WordPress data by replacing the hardcoded copy with GraphQL results.

### Hotspots & Waypoints

`app/world/page.tsx` loads hotspots from WordPress and hydrates the 3D tour (`components/Experience.tsx`). Populate `lib/wp.ts` to parse waypoint JSON structures.

### Gamification & Mini-games

Use the Zustand store in `lib/store.ts` to award XP or badges from client interactions (e.g., completing hotspots or micro-challenges). Extend `components/HUD.tsx` to surface new game loops.

## Accessibility & Performance

- `prefers-reduced-motion` detection disables scroll-linked animations when requested
- Role/tabIndex are applied to interactive hotspots
- Metadata, JSON-LD, sitemap and robots files are generated server-side for SEO

## Project Structure

Refer to the repository tree for routes, components and lib helpers. Each route is ready to hydrate with headless WordPress content while keeping demo copy lightweight.
