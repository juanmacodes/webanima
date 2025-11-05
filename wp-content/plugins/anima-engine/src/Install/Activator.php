<?php
namespace Anima\Engine\Install;

use Anima\Engine\PostTypes\RegisterPostTypes;
use Anima\Engine\Taxonomies\RegisterTaxonomies;

use function flush_rewrite_rules;
use function get_page_by_path;
use function get_posts;
use function home_url;
use function is_wp_error;
use function sprintf;
use function term_exists;
use function update_option;
use function update_post_meta;
use function wp_insert_post;
use function wp_insert_term;
use function wp_strip_all_tags;

/**
 * Rutinas de activación del plugin.
 */
class Activator {
    /**
     * Ejecuta la activación.
     */
    public function activate(): void {
        ( new RegisterPostTypes() )->register_post_types();
        ( new RegisterTaxonomies() )->register_taxonomies();

        $this->maybe_create_pages();
        $this->maybe_insert_terms();
        $this->maybe_create_slides();

        flush_rewrite_rules();
    }

    /**
     * Crea páginas base si no existen.
     */
    protected function maybe_create_pages(): void {
        $pages = [
            'inicio' => [
                'title'   => 'Inicio',
                'content' => '<h2>Bienvenidos a Anima Engine</h2><p>Nuestra casa creativa para avatares, experiencias inmersivas y producción XR. Esta página utiliza la plantilla de inicio para mostrar servicios, cursos y avatares populares.</p>',
            ],
            'servicios' => [
                'title'   => 'Servicios',
                'content' => '<h2>Servicios inmersivos</h2><ul><li>Producción y dirección para streaming volumétrico.</li><li>Avatares para eventos en vivo.</li><li>Automatización con inteligencia artificial.</li><li>Implementación de experiencias VR y AR a medida.</li></ul>',
            ],
            'avatares' => [
                'title'   => 'Avatares',
                'content' => '<p>Explora nuestros avatares listos para Unreal Engine, Unity y WebGL. Utiliza los filtros por tecnología para encontrar el pipeline perfecto.</p>',
            ],
            'proyectos' => [
                'title'   => 'Proyectos',
                'content' => '<h2>Proyectos destacados</h2><p>Documentamos KPIs como engagement, retención y tiempo en escena para cada proyecto XR. Contáctanos para recibir un dossier completo.</p>',
            ],
            'cursos' => [
                'title'   => 'Cursos',
                'content' => '<h2>Academia Anima</h2><p>Formaciones especializadas en pipelines realtime, creación de avatares y producción remota.</p><details><summary>¿Requieren experiencia previa?</summary><p>No, los cursos incluyen módulos introductorios y sesiones de acompañamiento.</p></details><details><summary>¿Se entregan certificados?</summary><p>Sí, emitimos certificaciones digitales verificables para cada cohorte.</p></details>',
            ],
            'blog' => [
                'title'   => 'Blog',
                'content' => '<p>Noticias, guías y procesos detrás de cámaras de nuestro laboratorio XR.</p>',
            ],
            'contacto' => [
                'title'   => 'Contacto',
                'content' => '<h2>Hablemos de tu próximo avatar</h2><p>Completa el formulario o usa la API <code>/wp-json/anima/v1/contacto</code> para integrar tus propios canales. Recuerda incluir nombre, correo y tu consulta.</p>',
            ],
            'experiencia-inmersiva' => [
                'title'   => 'Experiencia Inmersiva',
                'content' => '<h2>Experiencia inmersiva Anima</h2><p>Descubre un recorrido interactivo con avatares, ambientes volumétricos y analítica integrada.</p><p>[anima_model src="https://example.com/avatar-demo.glb" alt="Avatar 3D demo" ar="true" auto_rotate="true" camera_controls="true"]</p>',
            ],
            'anima-live' => [
                'title'   => 'Anima Live',
                'content' => $this->anima_live_content(),
            ],
        ];

        foreach ( $pages as $slug => $page ) {
            if ( ! get_page_by_path( $slug ) ) {
                $page_id = wp_insert_post(
                    [
                        'post_title'   => wp_strip_all_tags( $page['title'] ),
                        'post_content' => $page['content'],
                        'post_status'  => 'publish',
                        'post_type'    => 'page',
                        'post_name'    => $slug,
                    ],
                    true
                );

                if ( ! is_wp_error( $page_id ) && 'inicio' === $slug ) {
                    update_option( 'page_on_front', $page_id );
                    update_option( 'show_on_front', 'page' );
                }
            }
        }
    }

    /**
     * Inserta términos base para taxonomías.
     */
    protected function maybe_insert_terms(): void {
        $niveles = [ 'Inicial', 'Intermedio', 'Avanzado' ];
        foreach ( $niveles as $nivel ) {
            if ( ! term_exists( $nivel, 'nivel' ) ) {
                wp_insert_term( $nivel, 'nivel' );
            }
        }

        $modalidades = [ 'Online', 'Híbrido', 'On-demand' ];
        foreach ( $modalidades as $modalidad ) {
            if ( ! term_exists( $modalidad, 'modalidad' ) ) {
                wp_insert_term( $modalidad, 'modalidad' );
            }
        }

        $tecnologias = [ 'Unity', 'Unreal', 'WebGL', 'IA', 'Streaming', 'Metaverso', 'XR' ];
        foreach ( $tecnologias as $tec ) {
            if ( ! term_exists( $tec, 'tecnologia' ) ) {
                wp_insert_term( $tec, 'tecnologia' );
            }
        }
    }

    /**
     * Crea slides de ejemplo si aún no existen.
     */
    protected function maybe_create_slides(): void {
        $existing = get_posts(
            [
                'post_type'      => 'slide',
                'post_status'    => 'publish',
                'posts_per_page' => 1,
                'fields'         => 'ids',
            ]
        );

        if ( ! empty( $existing ) ) {
            return;
        }

        for ( $i = 1; $i <= 3; $i++ ) {
            $slide_id = wp_insert_post(
                [
                    'post_title'   => sprintf( 'Slide demo %d', $i ),
                    'post_content' => '<p>Slide demostrativa generada en la activación del plugin.</p>',
                    'post_status'  => 'publish',
                    'post_type'    => 'slide',
                ],
                true
            );

            if ( ! is_wp_error( $slide_id ) ) {
                update_post_meta( $slide_id, 'anima_slide_url', home_url( '/contacto' ) );
                update_post_meta( $slide_id, 'anima_slide_placeholder', sprintf( 'https://picsum.photos/1600/900?random=%d', $i ) );
            }
        }
    }

    /**
     * Genera el contenido base para la landing Anima Live.
     */
    protected function anima_live_content(): string {
        return <<<HTML
<section class="hero">
    <h1>Anima Live — Crea tu avatar en minutos</h1>
    <p>App en Unreal Engine para generar avatares listos para streaming y VR.</p>
    <p><a class="button" href="#">Descargar demo</a> <a class="button" href="/contacto">Solicitar acceso</a></p>
</section>
<section>
    <h2>Cómo funciona</h2>
    <ol>
        <li><strong>Captura:</strong> escanea desde móvil o sube fotos de referencia.</li>
        <li><strong>Personaliza:</strong> rasgos, ropa, expresiones y rigs listos.</li>
        <li><strong>Exporta:</strong> genera GLB/FBX con blendshapes optimizados.</li>
    </ol>
</section>
<section>
    <h2>Características clave</h2>
    <ul>
        <li>Rig facial completo con lipsync y control de microexpresiones.</li>
        <li>Presets de iluminación y plantillas de overlay para streaming.</li>
        <li>Exportadores directos a Unreal, Unity y entornos WebGL.</li>
    </ul>
</section>
<section>
    <h2>Galería</h2>
    <p>[anima_gallery type="avatar" limit="6"]</p>
</section>
<section>
    <h2>Compatibilidad</h2>
    <ul>
        <li>Unreal Engine 5.x</li>
        <li>Unity 2022+</li>
        <li>WebGL / WebXR</li>
        <li>iOS y Android (roadmap)</li>
    </ul>
</section>
<section>
    <h2>¿Quieres una versión a medida?</h2>
    <p><a class="button" href="/contacto">Contáctanos para una consultoría</a></p>
</section>
<section>
    <h2>Preguntas frecuentes</h2>
    <details><summary>¿La app incluye plantillas de streaming?</summary><p>Sí, incorporamos overlays animados y escenas listas para OBS.</p></details>
    <details><summary>¿Se puede entrenar con nuestra marca?</summary><p>Ofrecemos paquetes de personalización y entrenamiento de modelos dedicados.</p></details>
</section>
HTML;
    }
}
