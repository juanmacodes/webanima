# Anima Core

Plugin de funcionalidades centrales para un stack WordPress headless orientado a exponer contenido mediante REST y WPGraphQL.

## Requisitos

- WordPress 6.x (instalación headless, sin temas ni maquetadores).
- PHP 8.2 o superior.
- Extensiones recomendadas: `mysqli`, `curl`, `json`.
- Plugins obligatorios:
  - [WPGraphQL](https://www.wpgraphql.com/)
  - [WPGraphQL for Advanced Custom Fields](https://github.com/wp-graphql/wp-graphql-acf) (si está instalado ACF Pro)
  - Un proveedor JWT: [WPGraphQL JWT Auth](https://github.com/wp-graphql/wp-graphql-jwt-authentication) **o** [JWT Authentication for WP REST API](https://wordpress.org/plugins/jwt-authentication-for-wp-rest-api/)
- Plugins opcionales:
  - Offload Media (S3/R2) para gestionar medios remotos.
  - Contact Form 7 o Fluent Forms si se desea centralizar formularios adicionales.

## Instalación

1. Copiar la carpeta `anima-core` a `wp-content/plugins/`.
2. Activar el plugin desde el panel de WordPress.
3. Al activarse se ejecuta una migración que crea la tabla personalizada `wp_anima_waitlist` (el prefijo depende de tu instalación) y se registra la opción `anima_core_version`.
4. Verificar que los plugins obligatorios estén activos y configurados.
5. Configurar el proveedor JWT elegido para futuras integraciones privadas.

## CORS

El plugin expone cabeceras CORS para los dominios:

- `https://animaavataragency.com`
- `https://staging.animaavataragency.com`

Para añadir más orígenes basta con utilizar el filtro:

```php
add_filter( 'anima_core_cors_allowed_origins', function ( array $origins ) {
    $origins[] = 'https://tu-dominio.com';
    return $origins;
} );
```

Las peticiones `OPTIONS` reciben respuesta automática para facilitar preflight en CDNs.

## Tipos de contenido

### CPT `proyecto`

- Slug: `proyecto`, archivo público en `/proyectos`.
- Soporta título, editor, extracto e imagen destacada.
- Taxonomía asociada: `servicio` (Streaming, Holográficos, IA, VR).
- Metacampos expuestos en REST y GraphQL:
  - `anima_client` (string)
  - `anima_year` (int)
  - `anima_stack` (array de strings)
  - `anima_kpis` (array de objetos `{ label, value }`)
  - `anima_gallery` (array de IDs de adjuntos)
  - `anima_video_url` (string)

### CPT `curso`

- Slug: `curso`, archivo público en `/cursos`.
- Soporta título, editor, extracto e imagen destacada.
- Taxonomías asociadas:
  - `nivel` (Inicial, Intermedio, Avanzado)
  - `modalidad` (Grabado, Directo, Blended)
- Metacampos expuestos:
  - `anima_duration_hours` (int)
  - `anima_price` (float)
  - `anima_syllabus` (array `{ title, lessons[] }`)
  - `anima_requirements` (string con HTML permitido)
  - `anima_instructors` (array `{ name, bio, avatar_url }`)
  - `anima_upcoming_dates` (array de fechas en ISO-8601)

Ambos CPTs y taxonomías se registran con `show_in_graphql = true` y cuentan con tipos personalizados para estructuras anidadas.

## GraphQL

El endpoint por defecto es `/graphql`. Desde ahí podrás consultar los nodos `proyectos` y `cursos`, acceder a taxonomías y a todos los metacampos descritos.

Ejemplo de consulta:

```graphql
query Proyectos {
  proyectos {
    nodes {
      title
      animaClient
      animaYear
      animaStack
      animaKpis {
        label
        value
      }
      animaGallery
      animaVideoUrl
      servicios {
        nodes {
          name
        }
      }
    }
  }
}
```

Si ACF está activo, los grupos y campos relevantes se registran vía `acf_add_local_field_group` con `show_in_graphql` habilitado, listos para ser gestionados desde el panel.

## REST API

### Lista de espera

- **Ruta:** `POST /wp-json/anima/v1/waitlist`
- **Body JSON:**

```json
{
  "name": "Jane Doe",
  "email": "jane@example.com",
  "network": "Instagram",
  "country": "ES",
  "consent": true
}
```

- **Validaciones:**
  - `email` obligatorio y válido.
  - `consent` debe ser `true`.

- **Respuesta:**

```json
{ "ok": true }
```

- **Errores:**
  - `400` para datos faltantes o inválidos.
  - `500` si hay problemas al insertar en la base de datos.

#### Ejemplo cURL

```bash
curl -X POST https://tu-dominio.com/wp-json/anima/v1/waitlist \
  -H 'Content-Type: application/json' \
  -d '{
    "name": "Jane Doe",
    "email": "jane@example.com",
    "network": "Instagram",
    "country": "ES",
    "consent": true
  }'
```

Si se quiere reaccionar a nuevos registros (por ejemplo, para disparar integraciones con Mailchimp o SendGrid), se expone el hook:

```php
add_action( 'anima_waitlist_added', function ( array $data ) {
    // Enviar a tu sistema externo si existe API Key configurada.
} );
```

## Base de datos

Al activarse se crea la tabla personalizada `{$wpdb->prefix}anima_waitlist` con los campos:

- `id` (PK, autoincrement)
- `name`
- `email`
- `network`
- `country`
- `consent`
- `created_at`

La tabla facilita exportaciones futuras o integraciones serverless.

## Seguridad y autenticación

- Las cabeceras CORS limitan el acceso a dominios de producción y staging. Puedes ampliarlo vía filtro.
- Los endpoints futuros que requieran autenticación podrán aprovechar JWT gracias a los plugins recomendados. El endpoint de lista de espera se mantiene público, asumiendo que el rate-limit se aplicará en CDN.
- Se recomienda proteger `/wp-admin/` y utilizar HTTPS en todos los entornos.

## Mantenimiento

- La versión actual del plugin se almacena en la opción `anima_core_version` para facilitar migraciones.
- Los CPTs, taxonomías y campos se registran en `init`, por lo que se pueden personalizar fácilmente mediante hooks estándar de WordPress.
- No se incluyen binarios (imágenes, fuentes, vídeos) en el repositorio.

## Desarrollo adicional

- El filtro `anima_core_cors_allowed_origins` y el hook `anima_waitlist_added` permiten extender la lógica sin modificar el core del plugin.
- Para añadir campos personalizados extra se recomienda seguir el patrón de `register_post_meta` y, si aplica, exponerlos manualmente en GraphQL.

---

Hecho con ❤️ para la Anima Avatar Agency.
