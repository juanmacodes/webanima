<?php
namespace Anima\Engine\Shortcodes;

use Anima\Engine\Services\ServiceInterface;

use const MINUTE_IN_SECONDS;
use function __;
use function absint;
use function add_query_arg;
use function apply_filters;
use function esc_attr;
use function esc_html;
use function esc_html__;
use function esc_url;
use function get_option;
use function get_query_var;
use function get_the_excerpt;
use function get_the_post_thumbnail;
use function get_the_title;
use function get_permalink;
use function get_transient;
use function post_type_exists;
use function set_transient;
use function shortcode_atts;
use function wp_kses_post;
use function wp_list_pluck;
use function wp_reset_postdata;
use function wp_script_add_data;
use function wp_register_script;
use function wp_enqueue_script;
use function wp_trim_words;
use function wp_unslash;
use function wp_json_encode;

/**
 * Gestión de shortcodes del plugin.
 */
class Shortcodes implements ServiceInterface {
    /**
     * {@inheritDoc}
     */
    public function register(): void {
        add_shortcode( 'anima_gallery', [ $this, 'render_gallery' ] );
        add_shortcode( 'anima_model', [ $this, 'render_model' ] );
        add_action( 'wp_enqueue_scripts', [ $this, 'register_assets' ] );
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

        $cache_key = 'anima_engine_gallery_' . md5( wp_json_encode( $query_args ) );
        $posts     = get_transient( $cache_key );

        if ( false === $posts ) {
            $query = new \WP_Query( $query_args );
            $posts = [
                'ids'        => wp_list_pluck( $query->posts, 'ID' ),
                'max_pages'  => $query->max_num_pages,
            ];
            set_transient( $cache_key, $posts, MINUTE_IN_SECONDS * 10 );
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
        $atts = shortcode_atts(
            [
                'src'              => '',
                'alt'              => __( 'Avatar 3D', 'anima-engine' ),
                'ar'               => 'true',
                'auto_rotate'      => 'true',
                'camera_controls'  => 'true',
                'exposure'         => '1',
                'shadow_intensity' => '0.6',
            ],
            $atts,
            'anima_model'
        );

        if ( empty( $atts['src'] ) ) {
            return '';
        }

        wp_enqueue_script( 'anima-model-viewer' );

        $bool_attrs = [ 'ar', 'auto_rotate', 'camera_controls' ];
        foreach ( $bool_attrs as $attr ) {
            $atts[ $attr ] = filter_var( $atts[ $attr ], \FILTER_VALIDATE_BOOLEAN ) ? 'true' : 'false';
        }

        $html  = '<model-viewer';
        $html .= ' src="' . esc_url( $atts['src'] ) . '"';
        $html .= ' alt="' . esc_attr( $atts['alt'] ) . '"';
        $html .= ' ar="' . esc_attr( $atts['ar'] ) . '"';
        $html .= ' auto-rotate="' . esc_attr( $atts['auto_rotate'] ) . '"';
        $html .= ' camera-controls="' . esc_attr( $atts['camera_controls'] ) . '"';
        $html .= ' exposure="' . esc_attr( $atts['exposure'] ) . '"';
        $html .= ' shadow-intensity="' . esc_attr( $atts['shadow_intensity'] ) . '"';
        $html .= ' style="width:100%;height:480px;border-radius:18px;background:radial-gradient(circle,#0f0f20,#04040a);"';
        $html .= '></model-viewer>';

        return $html;
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
}
