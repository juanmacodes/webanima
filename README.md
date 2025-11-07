# WebAnima — Futuristic WordPress Experience

Este repositorio contiene la infraestructura como código, el tema hijo y los plugins personalizados necesarios para lanzar el ecosistema "Anima" en WordPress. Está organizado en torno a los entregables EP01–EP12 solicitados en el backlog.

## Requisitos previos

- Docker Desktop 4.0+
- Node.js 18+ (solo para tooling opcional)
- Cuenta con hosting que ofrezca PHP 8.2+, MariaDB 10.6+, HTTPS gestionado y copias de seguridad diarias

## Estructura

```
wp-content/
├── plugins/
│   ├── anima-engine/         # Motor principal (CPT cursos, APIs, widgets Elementor)
│   ├── anima-swiper-slider/  # Shortcode [anima_slider]
│   └── anima-world/          # Shortcode [anima_world] con three.js
└── themes/
    └── anima-child/          # Tema hijo TT4 con diseño oscuro futurista
```

## Widget "Proyectos — Tabs"

El plugin **anima-engine** incorpora el widget de Elementor "Proyectos — Tabs" dentro de la categoría **Anima**. Permite pintar una pestaña por cada servicio (taxonomía `servicio`) y mostrar los proyectos relacionados en formato **Grid**, **Masonry** o **Carrusel** reutilizando las tarjetas `.an-card` del diseño.

Características destacadas:

- Filtros por servicios, orden, rango de años y búsqueda.
- Controles para el contenido de la tarjeta (imagen, cliente, año, excerpt, chips de stack, KPIs y CTA "Ver caso").
- Ajustes de diseño de pestañas (posición, alineación, tipografía, colores e indicador) y del layout (columnas, gap, autoplay/loop en carrusel, etc.).
- Modo **AJAX** opcional con precarga y caché en `sessionStorage` (TTL configurable) que consulta `GET /anima/v1/proyectos`.
- Accesible mediante roles ARIA (`tab`/`tabpanel`) y navegación por teclado.

El JS asociado (`assets/js/anima-projects-tabs.js`) gestiona el cambio de pestañas, las animaciones y la inicialización de Swiper para el carrusel. El CSS se extiende en `assets/css/anima-ui.css` para tablist, skeletons y KPIs.

### Shortcode equivalente

Para usarlo fuera de Elementor existe el shortcode:

```text
[anima_proyectos_tabs servicios="Streaming,IA" layout="grid" per_page="6" ajax="1" cache_ttl="900"]
```

Parámetros disponibles:

- `servicios`: lista separada por comas de slugs o nombres de servicio. Vacío = todos.
- `layout`: `grid`, `masonry` o `carousel` (por defecto `grid`).
- `per_page`: número de proyectos por pestaña (máx. 24).
- `orderby` / `order`: `date`, `title`, `meta_value`, `rand` y dirección `ASC|DESC`.
- `year_min`, `year_max`, `search`: filtros adicionales opcionales.
- `columns_desktop`, `columns_tablet`, `columns_mobile`, `gap`: ajustes del grid.
- `show_image`, `show_client`, `show_year`, `show_excerpt`, `show_stack`, `show_kpis`, `excerpt_length`, `kpi_limit`, `button_text`.
- `ajax`: `1` para cargar pestañas por REST; `prefetch` y `cache_ttl` controlan precarga y TTL de caché (segundos).

El shortcode reutiliza el helper `ProjectCardRenderer` para las tarjetas y comparte la misma estructura HTML/JS que el widget, por lo que hereda la experiencia responsiva y accesible.

## Puesta en marcha local (staging)

1. Copia `.env.example` a `.env` y ajusta credenciales.
2. Ejecuta `docker compose up -d` para iniciar WordPress (PHP 8.2 FPM, Nginx y MariaDB).
3. Abre `https://anima.localhost:8080` (agrega excepción SSL si usas mkcert).
4. Accede al admin con las credenciales creadas en el asistente.
5. Instala los plugins de terceros requeridos (Elementor, Rank Math, LiteSpeed/WP Rocket, WebP Express/Imagify, Redirection, WP Mail SMTP, Fluent Forms o Contact Form 7) desde el dashboard.
6. Activa el tema **Anima Child**.
7. Importa `content/demo-content.xml` usando "Herramientas → Importar" para precargar proyectos y posts de ejemplo.

## Despliegues

- La rama `main` representa producción.
- La rama `dev` despliega automáticamente a staging mediante GitHub Actions y SFTP. Revisa `.github/workflows/deploy-staging.yml` para credenciales.
- Utiliza Pull Requests desde `feature/*` hacia `dev`. Mantenemos historial limpio con conventional commits.

## Backups y caching

- El contenedor de base de datos incluye un volumen persistente (`db-data`). Para producción, configura backups diarios en tu hosting.
- Activa OPcache desde el panel del servidor (ya incluido en la imagen PHP 8.2) y complementa con el plugin de caché elegido.

## Scripts útiles

```
./infrastructure/scripts/sync-staging.sh  # Empuja tema y plugins al servidor SFTP staging
./infrastructure/scripts/pull-db.sh       # Descarga dump de producción a local
```

Ambos scripts utilizan variables de entorno `SFTP_HOST`, `SFTP_USER`, `SFTP_PATH` y `PROD_DB_SSH`. Personaliza antes de ejecutar.

## Licencia

Código propietario — uso interno del equipo Anima.
