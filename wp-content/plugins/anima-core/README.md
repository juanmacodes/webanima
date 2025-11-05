# Anima Core

Plugin núcleo para la web WordPress de la agencia de avatares Anima. Registra los tipos de contenido, taxonomías, metacampos y endpoints necesarios para gestionar cursos inmersivos, avatares, proyectos y experiencias interactivas.

## Características principales

- **Custom Post Types:** `curso`, `avatar`, `proyecto` y `experiencia`, todos disponibles en el editor de bloques, REST API y listos para WPGraphQL.
- **Taxonomías:** `nivel`, `modalidad` y `tecnologia` para clasificar el contenido de formación y producción.
- **Metacampos personalizados:**
  - `anima_instructores`
  - `anima_duracion`
  - `anima_kpis`
  - `anima_demo_url`
  - `anima_destacado` (checkbox para marcar cursos destacados)
- **Shortcode** `[anima_cursos]` para mostrar cursos destacados, con plantilla básica en HTML lista para personalizarse con CSS.
- **Endpoint REST** `POST /wp-json/anima/v1/contacto` que recibe `nombre`, `email` y `consulta`, envía un correo al administrador y dispara la acción `anima_contact_request_received`.
- **Integraciones preparadas:** hooks definidos para ampliar el esquema de WPGraphQL y para conectar con BuddyPress cuando estén activos.

## Uso del shortcode

Insertar en cualquier entrada o plantilla:

```html
[anima_cursos cantidad="4"]
```

El atributo `cantidad` es opcional (por defecto muestra 3 cursos). Solo se listan los cursos marcados como destacados en el meta box del CPT.

## Endpoint REST de contacto

Ejemplo de petición:

```bash
curl -X POST https://tu-sitio.com/wp-json/anima/v1/contacto \
  -H "Content-Type: application/json" \
  -d '{
    "nombre": "Ada Lovelace",
    "email": "ada@example.com",
    "consulta": "Quiero contratar un avatar para mi evento."
  }'
```

Respuesta exitosa:

```json
{
  "ok": true,
  "mensaje": "Gracias, nos pondremos en contacto contigo muy pronto."
}
```

Usa la acción `anima_contact_request_received` para integrar CRMs, automatizaciones o notificaciones personalizadas.

## Requisitos

- WordPress 6.x
- PHP 8.0 o superior

## Desarrollo

La lógica del plugin se encuentra dividida en `/includes/` para facilitar la lectura y futuras extensiones. Añade tus personalizaciones en archivos nuevos manteniendo el prefijo `anima_` para evitar colisiones.
