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
│   ├── anima-core/           # CPT Proyectos, taxonomía "stack" y metacampos
│   ├── anima-swiper-slider/  # Shortcode [anima_slider]
│   └── anima-world/          # Shortcode [anima_world] con three.js
└── themes/
    └── anima-child/          # Tema hijo TT4 con diseño oscuro futurista
```

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
