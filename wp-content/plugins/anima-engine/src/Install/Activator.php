<?php
namespace Anima\Engine\Install;

use Anima\Engine\PostTypes\RegisterPostTypes;
use Anima\Engine\Taxonomies\RegisterTaxonomies;

use function array_key_exists;
use function class_exists;
use function dbDelta;
use function flush_rewrite_rules;
use function get_page_by_path;
use function get_page_by_title;
use function get_post_status;
use function get_posts;
use function get_option;
use function home_url;
use function is_wp_error;
use function method_exists;
use function sprintf;
use function term_exists;
use function update_option;
use function update_post_meta;
use function wp_insert_post;
use function wp_insert_term;
use function wp_strip_all_tags;
use function wp_parse_args;
use const OBJECT;

/**
 * Rutinas de activación del plugin.
 */
class Activator {
    /**
     * Ejecuta la activación.
     */
    public function activate(): void {
        $this->maybe_create_tables();

        if ( function_exists( 'anima_engine_register_curso_post_type' ) ) {
            anima_engine_register_curso_post_type();
        }

        ( new RegisterPostTypes() )->register_post_types();

        if ( function_exists( 'anima_engine_register_curso_taxonomies' ) ) {
            anima_engine_register_curso_taxonomies();
        }

        ( new RegisterTaxonomies() )->register_taxonomies();

        $this->maybe_create_pages();
        $this->maybe_insert_terms();
        if ( $this->is_feature_enabled( 'enable_slider', true ) ) {
            $this->maybe_create_slides();
        }

        $this->maybe_create_subscription_product();

        flush_rewrite_rules();
    }

    /**
     * Crea las tablas personalizadas necesarias.
     */
    protected function maybe_create_tables(): void {
        global $wpdb;

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $charset_collate = $wpdb->get_charset_collate();

        $entitlements_table = "CREATE TABLE {$wpdb->prefix}anima_entitlements (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            user_id bigint(20) unsigned NOT NULL,
            asset_id bigint(20) unsigned NOT NULL,
            asset_type varchar(32) NOT NULL,
            license_key varchar(64) NOT NULL,
            source_order bigint(20) unsigned DEFAULT NULL,
            expires_at datetime DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY user_id (user_id),
            KEY asset_type (asset_type)
        ) {$charset_collate};";

        $assets_table = "CREATE TABLE {$wpdb->prefix}anima_assets (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            slug varchar(190) NOT NULL,
            type varchar(32) NOT NULL,
            title varchar(190) NOT NULL,
            media_url text,
            version varchar(32) DEFAULT '',
            price decimal(10,2) DEFAULT 0,
            active tinyint(1) DEFAULT 1,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY slug (slug),
            KEY type (type),
            KEY active (active)
        ) {$charset_collate};";

        $avatars_table = "CREATE TABLE {$wpdb->prefix}anima_avatars (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            user_id bigint(20) unsigned NOT NULL,
            glb_url text,
            poster_url text,
            updated_at datetime DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY user_id (user_id)
        ) {$charset_collate};";

        dbDelta( $entitlements_table );
        dbDelta( $assets_table );
        dbDelta( $avatars_table );

        update_option( 'anima_engine_db_version', ANIMA_ENGINE_DB_VERSION );
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
     * Crea el producto de suscripción base si es necesario.
     */
    protected function maybe_create_subscription_product(): void {
        if ( ! class_exists( '\\WC_Product' ) ) {
            return;
        }

        $options   = get_option( 'anima_engine_options', [] );
        $product_id = isset( $options['live_product_id'] ) ? (int) $options['live_product_id'] : 0;

        if ( $product_id > 0 && get_post_status( $product_id ) ) {
            return;
        }

        $existing = get_page_by_title( 'Anima Live', OBJECT, 'product' );
        if ( $existing ) {
            $product_id = (int) $existing->ID;
        } else {
            $product = null;

            if ( class_exists( '\\WC_Product_Subscription' ) ) {
                $product = new \WC_Product_Subscription();
                $product->set_regular_price( '5' );
                $product->set_price( '5' );
                $product->update_meta_data( '_subscription_period', 'month' );
                $product->update_meta_data( '_subscription_period_interval', 1 );
                $product->update_meta_data( '_subscription_trial_length', 7 );
                $product->update_meta_data( '_subscription_trial_period', 'day' );
                $product->update_meta_data( '_subscription_sign_up_fee', '0' );
            } else {
                $product = new \WC_Product_Simple();
                $product->set_regular_price( '5' );
                $product->set_price( '5' );
            }

            $product->set_name( 'Anima Live' );
            $product->set_status( 'publish' );
            $product->set_catalog_visibility( 'hidden' );
            if ( method_exists( $product, 'set_virtual' ) ) {
                $product->set_virtual( true );
            }
            if ( method_exists( $product, 'set_sold_individually' ) ) {
                $product->set_sold_individually( true );
            }
            $product->set_description( 'Suscripción Anima Live con prueba gratuita de 7 días.' );
            $product->save();

            $product_id = $product->get_id();
        }

        if ( $product_id > 0 ) {
            $options['live_product_id'] = $product_id;
            update_option( 'anima_engine_options', $options );
        }
    }

    /**
     * Determina si una característica está habilitada.
     */
    protected function is_feature_enabled( string $flag, bool $default = true ): bool {
        $options = wp_parse_args( get_option( 'anima_engine_options', [] ), [ $flag => $default ] );

        if ( array_key_exists( $flag, $options ) ) {
            return (bool) $options[ $flag ];
        }

        return $default;
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
