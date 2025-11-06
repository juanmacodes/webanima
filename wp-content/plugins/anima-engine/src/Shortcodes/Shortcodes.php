<?php
namespace Anima\Engine\Shortcodes;

use Anima\Engine\Models\Avatar as AvatarModel;
use Anima\Engine\Models\Asset as AssetModel;
use Anima\Engine\Models\Entitlement as EntitlementModel;
use Anima\Engine\Commerce\SubscriptionManager;
use Anima\Engine\Services\ServiceInterface;

use const MINUTE_IN_SECONDS;
use const FILTER_VALIDATE_BOOLEAN;
use function __;
use function absint;
use function add_query_arg;
use function apply_filters;
use function esc_attr;
use function esc_html;
use function esc_html__;
use function esc_url;
use function esc_url_raw;
use function get_option;
use function get_current_user_id;
use function get_query_var;
use function get_the_excerpt;
use function get_the_post_thumbnail;
use function get_the_title;
use function get_permalink;
use function get_transient;
use function get_post_status;
use function post_type_exists;
use function set_transient;
use function shortcode_atts;
use function sanitize_text_field;
use function sanitize_key;
use function sanitize_html_class;
use function in_array;
use function wp_kses_post;
use function wp_list_pluck;
use function wp_reset_postdata;
use function wp_script_add_data;
use function wp_register_script;
use function wp_enqueue_script;
use function wp_trim_words;
use function is_user_logged_in;
use function wp_unslash;
use function sprintf;
use function wp_json_encode;
use function wp_parse_args;
use function number_format_i18n;
use function current_time;
use function human_time_diff;
use function home_url;
use function function_exists;
use function wc_get_account_endpoint_url;
use function wp_login_url;

/**
 * Gestión de shortcodes del plugin.
 */
class Shortcodes implements ServiceInterface {
    /**
     * Opciones almacenadas del plugin.
     *
     * @var array<string, mixed>
     */
    protected array $options = [];

    /**
     * Modelo reutilizado para recuperar avatares.
     */
    protected ?AvatarModel $avatarModel = null;

    /**
     * Modelo para acceder a los assets.
     */
    protected ?AssetModel $assetModel = null;

    /**
     * Modelo para consultar licencias de usuario.
     */
    protected ?EntitlementModel $entitlementModel = null;

    /**
     * Gestor de suscripciones de usuario.
     */
    protected ?SubscriptionManager $subscriptionManager = null;

    /**
     * {@inheritDoc}
     */
    public function register(): void {
        $this->options = $this->get_options();

        add_shortcode( 'anima_gallery', [ $this, 'render_gallery' ] );
        add_shortcode( 'anima_catalog', [ $this, 'render_catalog' ] );
        add_shortcode( 'anima_entitlements', [ $this, 'render_entitlements' ] );
        add_shortcode( 'anima_subscription_status', [ $this, 'render_subscription_status' ] );

        if ( $this->is_feature_enabled( 'enable_model_viewer' ) ) {
            add_shortcode( 'anima_model', [ $this, 'render_model' ] );
            add_shortcode( 'anima_user_avatar', [ $this, 'render_user_avatar' ] );
            add_action( 'wp_enqueue_scripts', [ $this, 'register_assets' ] );
        }
    }

    /**
     * Renderiza un catálogo de assets personalizados.
     */
    public function render_catalog( array $atts ): string {
        $atts = shortcode_atts(
            [
                'type'   => 'all',
                'limit'  => '12',
                'search' => '',
                'page'   => '1',
            ],
            $atts,
            'anima_catalog'
        );

        $type       = sanitize_key( $atts['type'] );
        $limit      = absint( $atts['limit'] );
        $search     = sanitize_text_field( $atts['search'] );
        $page_param = absint( $atts['page'] );

        $requested_type = isset( $_GET['anima_type'] ) ? sanitize_key( wp_unslash( (string) $_GET['anima_type'] ) ) : '';
        if ( '' !== $requested_type ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            $type = $requested_type;
        }

        $requested_search = isset( $_GET['anima_search'] ) ? sanitize_text_field( wp_unslash( (string) $_GET['anima_search'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        if ( '' !== $requested_search ) {
            $search = $requested_search;
        }

        $page = $page_param > 0 ? $page_param : absint( isset( $_GET['anima_page'] ) ? wp_unslash( (string) $_GET['anima_page'] ) : 1 ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        if ( $page <= 0 ) {
            $page = 1;
        }

        if ( 'all' === $type ) {
            $type = '';
        }

        if ( '' !== $type && ! in_array( $type, [ 'skin', 'environment' ], true ) ) {
            $type = 'skin';
        }

        $per_page = $limit > 0 ? $limit : 12;

        if ( null === $this->assetModel ) {
            $this->assetModel = new AssetModel();
        }

        $query_args = [
            'type'     => '' !== $type ? $type : null,
            'search'   => '' !== $search ? $search : null,
            'page'     => $page,
            'per_page' => $per_page,
        ];

        $cache_key = $this->build_cache_key( 'catalog_shortcode', $query_args );
        $use_cache = $this->is_feature_enabled( 'cache_catalog', true );

        if ( $use_cache ) {
            $cached = $this->cache_get( $cache_key, 'catalog_shortcode' );
            if ( false !== $cached && is_string( $cached ) ) {
                return $cached;
            }
        }

        $results = $this->assetModel->getCatalog( $query_args );
        $items   = $results['items'] ?? [];
        $total   = isset( $results['total'] ) ? (int) $results['total'] : 0;

        if ( empty( $items ) ) {
            $empty = '<p>' . esc_html__( 'No hay assets disponibles por ahora.', 'anima-engine' ) . '</p>';
            if ( $use_cache ) {
                $this->cache_set( $cache_key, $empty, MINUTE_IN_SECONDS * 5, 'catalog_shortcode' );
            }

            return $empty;
        }

        $output  = '<div class="anima-catalog">';
        $output .= '<div class="anima-catalog-grid">';

        foreach ( $items as $item ) {
            $title     = esc_html( $item['title'] ?? '' );
            $media_url = esc_url( $item['media_url'] ?? '' );
            $price     = isset( $item['price'] ) ? (float) $item['price'] : 0.0;
            $price_fmt = $price > 0 ? sprintf( '€ %s', number_format_i18n( $price, 2 ) ) : esc_html__( 'Incluido con tu plan', 'anima-engine' );

            $output .= '<article class="anima-catalog-item">';
            if ( $media_url ) {
                $output .= '<figure class="anima-catalog-thumb"><img src="' . $media_url . '" alt="' . esc_attr( $title ) . '" loading="lazy" /></figure>';
            }
            $output .= '<div class="anima-catalog-body">';
            $output .= '<h3 class="anima-catalog-title">' . $title . '</h3>';
            $output .= '<p class="anima-catalog-price">' . esc_html( $price_fmt ) . '</p>';
            $output .= '</div>';
            $output .= '</article>';
        }

        $output .= '</div>';

        $total_pages = (int) ceil( $total / $per_page );
        if ( $total_pages > 1 ) {
            $output .= '<nav class="anima-catalog-pagination" aria-label="' . esc_attr__( 'Paginación del catálogo', 'anima-engine' ) . '">';
            $output .= $this->render_pagination( $page, $total_pages );
            $output .= '</nav>';
        }

        $output .= '</div>';

        if ( $use_cache ) {
            $this->cache_set( $cache_key, $output, MINUTE_IN_SECONDS * 5, 'catalog_shortcode' );
        }

        return $output;
    }

    /**
     * Renderiza la lista de licencias del usuario.
     */
    public function render_entitlements(): string {
        if ( ! is_user_logged_in() ) {
            return '<p>' . esc_html__( 'Debes iniciar sesión para ver tus descargas disponibles.', 'anima-engine' ) . '</p>';
        }

        if ( null === $this->entitlementModel ) {
            $this->entitlementModel = new EntitlementModel();
        }

        $user_id = get_current_user_id();
        $cache_key = 'anima_engine_entitlements_user_' . (int) $user_id;
        $use_cache = $this->is_feature_enabled( 'cache_entitlements', true );

        if ( $use_cache ) {
            $cached = $this->cache_get( $cache_key, 'entitlements' );
            if ( false !== $cached && is_string( $cached ) ) {
                return $cached;
            }
        }

        $records = $this->entitlementModel->getWithAssetsForUser( (int) $user_id );

        if ( empty( $records ) ) {
            return '<p>' . esc_html__( 'Todavía no tienes activos asignados.', 'anima-engine' ) . '</p>';
        }

        $output = '<ul class="anima-entitlements-list">';
        foreach ( $records as $record ) {
            $title     = esc_html( $record['title'] ?? '' );
            $media_url = esc_url( $record['media_url'] ?? '' );
            $type      = esc_html( $record['asset_type'] ?? '' );
            $output   .= '<li class="anima-entitlement">';
            $output   .= '<span class="anima-entitlement-title">' . $title . '</span>';
            if ( $type ) {
                $output .= ' <span class="anima-entitlement-type">(' . $type . ')</span>';
            }
            if ( $media_url ) {
                $output .= ' <a href="' . $media_url . '" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Descargar', 'anima-engine' ) . '</a>';
            }
            $output .= '</li>';
        }
        $output .= '</ul>';

        if ( $use_cache ) {
            $this->cache_set( $cache_key, $output, MINUTE_IN_SECONDS * 2, 'entitlements' );
        }

        return $output;
    }

    /**
     * Muestra el estado actual de la suscripción del usuario.
     */
    public function render_subscription_status( array $atts = [] ): string {
        $atts = shortcode_atts(
            [
                'show_plan' => 'true',
            ],
            $atts,
            'anima_subscription_status'
        );

        if ( ! is_user_logged_in() ) {
            $login_url = wp_login_url();

            return '<p>' . sprintf(
                esc_html__( 'Inicia sesión para revisar tu suscripción. %s', 'anima-engine' ),
                '<a href="' . esc_url( $login_url ) . '">' . esc_html__( 'Acceder', 'anima-engine' ) . '</a>'
            ) . '</p>';
        }

        $user_id  = get_current_user_id();
        $status   = $this->get_subscription_manager()->getStatus( (int) $user_id );
        $is_active = ! empty( $status['active'] );

        $plan       = isset( $status['plan'] ) ? trim( (string) $status['plan'] ) : '';
        $trial_ends = isset( $status['trial_ends_at'] ) ? (string) $status['trial_ends_at'] : '';
        $trial_msg  = '';

        if ( '' !== $trial_ends ) {
            $trial_timestamp = strtotime( $trial_ends );
            if ( $trial_timestamp && $trial_timestamp > current_time( 'timestamp' ) ) {
                $trial_msg = sprintf(
                    /* translators: %s: human readable time. */
                    esc_html__( 'Tu periodo de prueba finaliza en %s.', 'anima-engine' ),
                    human_time_diff( current_time( 'timestamp' ), $trial_timestamp )
                );
            } elseif ( $trial_timestamp ) {
                $trial_msg = esc_html__( 'Tu periodo de prueba ha finalizado.', 'anima-engine' );
            }
        }

        $summary = $is_active
            ? esc_html__( 'Tu suscripción está activa. Puedes acceder a todos los assets disponibles.', 'anima-engine' )
            : esc_html__( 'No tienes una suscripción activa. Actívala para desbloquear el catálogo completo.', 'anima-engine' );

        $classes = [ 'anima-subscription-card' ];
        $classes[] = $is_active ? 'anima-subscription-card--active' : 'anima-subscription-card--inactive';

        $html  = '<div class="' . esc_attr( implode( ' ', $classes ) ) . '">';
        $html .= '<h3>' . esc_html( $is_active ? __( 'Suscripción activa', 'anima-engine' ) : __( 'Suscripción inactiva', 'anima-engine' ) ) . '</h3>';
        $html .= '<p class="anima-subscription-summary">' . esc_html( $summary ) . '</p>';

        $show_plan = filter_var( $atts['show_plan'], FILTER_VALIDATE_BOOLEAN );
        $meta_items = [];

        if ( $show_plan && '' !== $plan ) {
            $meta_items[] = sprintf( esc_html__( 'Plan: %s', 'anima-engine' ), $plan );
        }

        if ( '' !== $trial_msg ) {
            $meta_items[] = $trial_msg;
        }

        if ( ! empty( $meta_items ) ) {
            $html .= '<ul class="anima-subscription-meta">';
            foreach ( $meta_items as $item ) {
                $html .= '<li>' . esc_html( $item ) . '</li>';
            }
            $html .= '</ul>';
        }

        $cta_url   = $is_active ? $this->get_subscription_management_url() : $this->get_subscription_purchase_url();
        $cta_label = $is_active ? esc_html__( 'Gestionar suscripción', 'anima-engine' ) : esc_html__( 'Activar suscripción', 'anima-engine' );

        if ( $cta_url ) {
            $html .= '<p class="anima-subscription-cta"><a class="button" href="' . esc_url( $cta_url ) . '">' . esc_html( $cta_label ) . '</a></p>';
        }

        $html .= '</div>';

        return $html;
    }

    /**
     * Registra scripts condicionales.
     */
    public function register_assets(): void {
        wp_register_script(
            'anima-model-viewer',
            'https://unpkg.com/@google/model-viewer/dist/model-viewer.min.js',
            [],
            '1.12.1',
            true
        );
        wp_script_add_data( 'anima-model-viewer', 'defer', true );
        wp_script_add_data( 'anima-model-viewer', 'type', 'module' );

        $script_path = ANIMA_ENGINE_PATH . 'assets/js/model-viewer.js';
        $script_url  = ANIMA_ENGINE_URL . 'assets/js/model-viewer.js';

        wp_register_script(
            'anima-model-viewer-enhancements',
            $script_url,
            [],
            file_exists( $script_path ) ? (string) filemtime( $script_path ) : ANIMA_ENGINE_VERSION,
            true
        );
        wp_script_add_data( 'anima-model-viewer-enhancements', 'defer', true );
    }

    /**
     * Recupera un valor de la cache aplicando filtros.
     */
    protected function cache_get( string $key, string $group = 'default' ) {
        $cached = apply_filters( 'anima_engine_cache_get', null, $key, $group );

        if ( null !== $cached ) {
            return $cached;
        }

        return get_transient( $key );
    }

    /**
     * Almacena un valor en cache permitiendo extensiones.
     */
    protected function cache_set( string $key, $value, int $expiration, string $group = 'default' ): void {
        $handled = apply_filters( 'anima_engine_cache_set', false, $key, $value, $expiration, $group );

        if ( true === $handled ) {
            return;
        }

        set_transient( $key, $value, $expiration );
    }

    /**
     * Genera una clave de cache consistente para el contexto dado.
     */
    protected function build_cache_key( string $context, array $arguments ): string {
        $hash = md5( wp_json_encode( $arguments ) );

        /**
         * Permite modificar la clave de cache generada para un contexto.
         */
        return apply_filters( 'anima_engine_cache_key', 'anima_engine_' . $context . '_' . $hash, $context, $arguments );
    }

    /**
     * Renderiza la galería de contenidos.
     */
    public function render_gallery( array $atts ): string {
        $atts = shortcode_atts(
            [
                'type'  => 'avatar',
                'limit' => '6',
            ],
            $atts,
            'anima_gallery'
        );

        $post_type = sanitize_key( $atts['type'] );
        $limit     = absint( $atts['limit'] );

        if ( ! post_type_exists( $post_type ) ) {
            return '<p>' . esc_html__( 'El tipo de contenido no existe.', 'anima-engine' ) . '</p>';
        }

        $paged = 1;
        $request_page = isset( $_GET['anima_page'] ) ? absint( wp_unslash( $_GET['anima_page'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        if ( $request_page > 0 ) {
            $paged = $request_page;
        } elseif ( get_query_var( 'paged' ) ) {
            $paged = absint( get_query_var( 'paged' ) );
        }

        $posts_per_page = $limit;
        if ( 0 === $limit ) {
            $posts_per_page = absint( get_option( 'posts_per_page', 12 ) );
        }

        $query_args = [
            'post_type'      => $post_type,
            'posts_per_page' => $posts_per_page,
            'post_status'    => 'publish',
            'paged'          => $limit === 0 ? max( 1, $paged ) : 1,
            'orderby'        => 'date',
            'order'          => 'DESC',
            'no_found_rows'  => 0 === $limit ? false : true,
        ];

        /**
         * Permite modificar los argumentos de la galería.
         */
        $query_args = apply_filters( 'anima_engine_gallery_query_args', $query_args, $atts );

        $cache_key = $this->build_cache_key( 'gallery', $query_args );
        $posts     = $this->cache_get( $cache_key, 'gallery' );

        if ( false === $posts ) {
            $query = new \WP_Query( $query_args );
            $posts = [
                'ids'        => wp_list_pluck( $query->posts, 'ID' ),
                'max_pages'  => $query->max_num_pages,
            ];
            $this->cache_set( $cache_key, $posts, MINUTE_IN_SECONDS * 10, 'gallery' );
            wp_reset_postdata();
        }

        if ( empty( $posts['ids'] ) ) {
            return '<p>' . esc_html__( 'No hay elementos disponibles por el momento.', 'anima-engine' ) . '</p>';
        }

        $items = [];
        foreach ( $posts['ids'] as $post_id ) {
            $title = get_the_title( $post_id );
            $link  = get_permalink( $post_id );
            $thumb = get_the_post_thumbnail( $post_id, 'medium', [ 'loading' => 'lazy' ] );

            $excerpt = wp_trim_words( get_the_excerpt( $post_id ), 18 );

            $item_markup  = '<article class="card">';
            $item_markup .= $thumb ? $thumb : '';
            $item_markup .= '<header><h3><a href="' . esc_url( $link ) . '">' . esc_html( $title ) . '</a></h3></header>';
            $item_markup .= '<p>' . esc_html( $excerpt ) . '</p>';
            $item_markup .= '</article>';

            /**
             * Filtro para modificar cada item de la galería.
             */
            $items[] = apply_filters( 'anima_engine_gallery_item_markup', $item_markup, $post_id, $atts );
        }

        $output  = '<div class="anima-engine-gallery">';
        $output .= '<div class="post-grid">' . implode( '', array_map( 'wp_kses_post', $items ) ) . '</div>';

        if ( 0 === $limit && ! empty( $posts['max_pages'] ) && $posts['max_pages'] > 1 ) {
            $output .= '<nav class="pagination" aria-label="' . esc_attr__( 'Paginación de la galería', 'anima-engine' ) . '">';
            $output .= $this->render_pagination( $paged, (int) $posts['max_pages'] );
            $output .= '</nav>';
        }

        $output .= '</div>';

        return $output;
    }

    /**
     * Renderiza el shortcode del visor de modelos.
     */
    public function render_model( array $atts ): string {
        if ( ! $this->is_feature_enabled( 'enable_model_viewer' ) ) {
            return '';
        }

        $atts = shortcode_atts(
            [
                'src'              => '',
                'alt'              => __( 'Avatar 3D', 'anima-engine' ),
                'poster'           => '',
                'video'            => '',
                'ar'               => 'true',
                'auto_rotate'      => 'true',
                'camera_controls'  => 'true',
                'exposure'         => '1',
                'shadow_intensity' => '0.6',
                'reveal'           => 'interaction',
                'loading'          => 'lazy',
                'ios_src'          => '',
                'draco_decoder'    => '',
                'ktx2_transcoder'  => '',
                'height'           => '480px',
                'class'            => '',
            ],
            $atts,
            'anima_model'
        );

        if ( empty( $atts['src'] ) ) {
            return '';
        }

        wp_enqueue_script( 'anima-model-viewer' );
        wp_enqueue_script( 'anima-model-viewer-enhancements' );

        $bool_attrs = [ 'ar', 'auto_rotate', 'camera_controls' ];
        foreach ( $bool_attrs as $attr ) {
            $atts[ $attr ] = filter_var( $atts[ $attr ], \FILTER_VALIDATE_BOOLEAN ) ? 'true' : 'false';
        }

        $height = trim( sanitize_text_field( $atts['height'] ) );
        if ( '' === $height ) {
            $height = '480px';
        }

        $ios_src = $atts['ios_src'] ?: 'https://modelviewer.dev/shared-assets/models/Astronaut.usdz';

        $attributes = [
            'src'              => esc_url( $atts['src'] ),
            'alt'              => esc_attr( $atts['alt'] ),
            'ar'               => esc_attr( $atts['ar'] ),
            'ar-modes'         => 'scene-viewer quick-look webxr',
            'ios-src'          => esc_url( $ios_src ),
            'auto-rotate'      => esc_attr( $atts['auto_rotate'] ),
            'camera-controls'  => esc_attr( $atts['camera_controls'] ),
            'exposure'         => esc_attr( $atts['exposure'] ),
            'shadow-intensity' => esc_attr( $atts['shadow_intensity'] ),
            'reveal'           => esc_attr( sanitize_text_field( $atts['reveal'] ) ),
            'loading'          => esc_attr( sanitize_text_field( $atts['loading'] ) ),
        ];

        if ( ! empty( $atts['poster'] ) ) {
            $attributes['poster'] = esc_url( $atts['poster'] );
        }

        if ( ! empty( $atts['draco_decoder'] ) ) {
            $attributes['draco-decoder'] = esc_url_raw( $atts['draco_decoder'] );
        }

        if ( ! empty( $atts['ktx2_transcoder'] ) ) {
            $attributes['ktx2-transcoder'] = esc_url_raw( $atts['ktx2_transcoder'] );
        }

        $viewer_classes = [ 'anima-model-viewer__element' ];
        if ( ! empty( $atts['class'] ) ) {
            $viewer_classes[] = sanitize_html_class( $atts['class'] );
        }

        $config = [
            'attributes' => $attributes,
            'classes'    => $viewer_classes,
            'style'      => sprintf(
                'width:100%%;height:%s;border-radius:18px;background:radial-gradient(circle,#0f0f20,#04040a);',
                esc_attr( $height )
            ),
        ];

        $wrapper_classes = [ 'anima-model-viewer' ];
        if ( ! empty( $atts['video'] ) ) {
            $wrapper_classes[] = 'anima-model-viewer--has-fallback';
        }

        $fallback = '';
        if ( ! empty( $atts['video'] ) ) {
            $fallback  = '<div class="anima-model-viewer__fallback" data-model-fallback>';
            $fallback .= '<video loop muted playsinline preload="metadata"';
            if ( ! empty( $atts['poster'] ) ) {
                $fallback .= ' poster="' . esc_url( $atts['poster'] ) . '"';
            }
            $fallback .= '>';
            $fallback .= '<source src="' . esc_url( $atts['video'] ) . '" type="video/mp4" />';
            $fallback .= '</video>';
            $fallback .= '<button type="button" class="button button--ghost" data-activate-model>' . esc_html__( 'Activar 3D', 'anima-engine' ) . '</button>';
            $fallback .= '</div>';
        }

        $html  = '<div class="' . esc_attr( implode( ' ', $wrapper_classes ) ) . '" data-anima-model="true" data-model-config="' . esc_attr( wp_json_encode( $config ) ) . '">';
        if ( $fallback ) {
            $html .= $fallback;
        }
        $html .= '<div class="anima-model-viewer__stage" data-model-stage></div>';
        $html .= '</div>';

        if ( ! empty( $atts['poster'] ) ) {
            $html .= '<noscript><img src="' . esc_url( $atts['poster'] ) . '" alt="' . esc_attr( $atts['alt'] ) . '" /></noscript>';
        }

        /**
         * Permite modificar el marcado final del visor 3D.
         */
        return apply_filters( 'anima_engine_model_viewer_markup', $html, $atts, $config );
    }

    /**
     * Renderiza el avatar del usuario autenticado.
     */
    public function render_user_avatar( array $atts = [] ): string {
        if ( ! $this->is_feature_enabled( 'enable_model_viewer' ) ) {
            return '';
        }

        if ( ! is_user_logged_in() ) {
            return '<p>' . esc_html__( 'Debes iniciar sesión para ver tu avatar.', 'anima-engine' ) . '</p>';
        }

        $user_id = get_current_user_id();
        if ( $user_id <= 0 ) {
            return '';
        }

        $avatar = $this->get_avatar_model()->getByUserId( $user_id );

        if ( empty( $avatar ) || empty( $avatar['glb_url'] ) ) {
            return '<p>' . esc_html__( 'Aún no has configurado tu avatar.', 'anima-engine' ) . '</p>';
        }

        wp_enqueue_script( 'anima-model-viewer' );
        wp_enqueue_script( 'anima-model-viewer-enhancements' );

        $atts = shortcode_atts(
            [
                'alt'             => __( 'Mi avatar 3D', 'anima-engine' ),
                'auto_rotate'     => 'true',
                'camera_controls' => 'true',
                'shadow_intensity'=> '0.5',
                'exposure'        => '1',
                'class'           => 'anima-user-avatar',
            ],
            $atts,
            'anima_user_avatar'
        );

        $bool_attrs = [ 'auto_rotate', 'camera_controls' ];
        foreach ( $bool_attrs as $attr ) {
            $atts[ $attr ] = filter_var( $atts[ $attr ], \FILTER_VALIDATE_BOOLEAN ) ? 'true' : 'false';
        }

        $glb    = esc_url( (string) $avatar['glb_url'] );
        $poster = empty( $avatar['poster_url'] ) ? '' : esc_url( (string) $avatar['poster_url'] );

        $attributes = [
            'src'              => $glb,
            'alt'              => esc_attr( $atts['alt'] ),
            'auto-rotate'      => esc_attr( $atts['auto_rotate'] ),
            'camera-controls'  => esc_attr( $atts['camera_controls'] ),
            'shadow-intensity' => esc_attr( $atts['shadow_intensity'] ),
            'exposure'         => esc_attr( $atts['exposure'] ),
            'loading'          => 'lazy',
            'ar'               => 'true',
            'ar-modes'         => 'scene-viewer quick-look webxr',
        ];

        if ( $poster ) {
            $attributes['poster'] = $poster;
        }

        $attributes = apply_filters( 'anima_engine_user_avatar_attributes', $attributes, $avatar, $atts );

        $additional_classes = array_filter(
            array_map( 'sanitize_html_class', preg_split( '/\s+/', (string) $atts['class'] ) ?: [] )
        );

        $classes = array_merge( [ 'anima-model-viewer__element' ], $additional_classes );

        $html  = '<model-viewer';
        foreach ( $attributes as $name => $value ) {
            $html .= ' ' . esc_attr( $name ) . '="' . esc_attr( $value ) . '"';
        }
        $html .= ' class="' . esc_attr( implode( ' ', $classes ) ) . '"';
        $html .= '>';
        $html .= '</model-viewer>';

        return apply_filters( 'anima_engine_user_avatar_markup', $html, $avatar, $atts );
    }

    /**
     * Obtiene el modelo reutilizable para los avatares.
     */
    protected function get_avatar_model(): AvatarModel {
        if ( null === $this->avatarModel ) {
            $this->avatarModel = new AvatarModel();
        }

        return $this->avatarModel;
    }

    /**
     * Devuelve el gestor de suscripciones reutilizable.
     */
    protected function get_subscription_manager(): SubscriptionManager {
        if ( null === $this->subscriptionManager ) {
            $this->subscriptionManager = new SubscriptionManager();
        }

        return $this->subscriptionManager;
    }

    /**
     * URL para gestionar la suscripción activa.
     */
    protected function get_subscription_management_url(): string {
        if ( function_exists( 'wc_get_account_endpoint_url' ) ) {
            $url = wc_get_account_endpoint_url( 'subscriptions' );
            if ( $url ) {
                return $url;
            }
        }

        return home_url( '/mi-cuenta/' );
    }

    /**
     * URL para comprar o renovar la suscripción.
     */
    protected function get_subscription_purchase_url(): string {
        $options   = $this->get_options();
        $product_id = isset( $options['subscription_product_id'] ) ? (int) $options['subscription_product_id'] : 0;

        if ( $product_id <= 0 && isset( $options['live_product_id'] ) ) {
            $product_id = (int) $options['live_product_id'];
        }

        if ( $product_id > 0 && get_post_status( $product_id ) ) {
            $link = get_permalink( $product_id );
            if ( $link ) {
                return $link;
            }
        }

        return home_url( '/tienda/' );
    }

    /**
     * Genera paginación para la galería.
     */
    protected function render_pagination( int $current_page, int $max_pages ): string {
        $links = [];
        for ( $i = 1; $i <= $max_pages; $i++ ) {
            $class = $i === $current_page ? ' class="pill pill-gradient"' : ' class="pill"';
            $url   = esc_url( add_query_arg( 'anima_page', (string) $i ) );
            $links[] = '<a' . $class . ' href="' . $url . '">' . esc_html( (string) $i ) . '</a>';
        }

        return '<div class="anima-gallery-pagination">' . implode( '', $links ) . '</div>';
    }

    /**
     * Recupera las opciones del plugin con valores por defecto.
     */
    protected function get_options(): array {
        if ( empty( $this->options ) ) {
            $defaults      = [
                'enable_slider'       => true,
                'enable_model_viewer' => true,
                'enable_cache'        => true,
                'cache_catalog'       => true,
                'cache_entitlements'  => true,
            ];
            $this->options = wp_parse_args( get_option( 'anima_engine_options', [] ), $defaults );
        }

        return $this->options;
    }

    /**
     * Comprueba si una característica está habilitada.
     */
    protected function is_feature_enabled( string $feature, bool $default = true ): bool {
        $options = $this->get_options();

        if ( array_key_exists( $feature, $options ) ) {
            return (bool) $options[ $feature ];
        }

        return $default;
    }
}
