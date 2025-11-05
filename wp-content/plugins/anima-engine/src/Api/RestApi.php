<?php
namespace Anima\Engine\Api;

use Anima\Engine\Services\ServiceInterface;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

use const MINUTE_IN_SECONDS;

use function __;
use function absint;
use function add_action;
use function esc_url;
use function get_option;
use function get_the_excerpt;
use function get_the_ID;
use function get_the_post_thumbnail_url;
use function get_the_terms;
use function get_the_title;
use function get_transient;
use function get_permalink;
use function is_email;
use function register_rest_route;
use function sanitize_email;
use function sanitize_text_field;
use function sanitize_textarea_field;
use function set_transient;
use function wp_mail;
use function wp_reset_postdata;
use function wp_trim_words;
use function wp_unslash;
use function wp_verify_nonce;
use function wp_json_encode;
use function wp_list_pluck;
use function sprintf;

/**
 * Endpoints REST personalizados del plugin.
 */
class RestApi implements ServiceInterface {
    /**
     * {@inheritDoc}
     */
    public function register(): void {
        add_action( 'rest_api_init', [ $this, 'register_routes' ] );
    }

    /**
     * Registra las rutas personalizadas.
     */
    public function register_routes(): void {
        register_rest_route(
            'anima/v' . ANIMA_ENGINE_API_VERSION,
            '/contacto',
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'handle_contact' ],
                'permission_callback' => [ $this, 'validate_contact_permission' ],
                'args'                => [
                    'nombre'  => [
                        'required' => true,
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                    'email'   => [
                        'required' => true,
                        'sanitize_callback' => 'sanitize_email',
                    ],
                    'consulta'=> [
                        'required' => true,
                        'sanitize_callback' => 'sanitize_textarea_field',
                    ],
                ],
            ]
        );

        register_rest_route(
            'anima/v' . ANIMA_ENGINE_API_VERSION,
            '/avatares',
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'handle_avatars' ],
                'permission_callback' => '__return_true',
                'args'                => [
                    'page'       => [
                        'sanitize_callback' => 'absint',
                    ],
                    'per_page'   => [
                        'sanitize_callback' => 'absint',
                    ],
                    'tecnologia' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
            ]
        );
    }

    /**
     * Valida la solicitud de contacto.
     */
    public function validate_contact_permission( WP_REST_Request $request ) {
        $nonce = $request->get_header( 'X-WP-Nonce' );
        if ( $nonce && ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
            return new WP_Error( 'anima_engine_invalid_nonce', __( 'Nonce inválido.', 'anima-engine' ), [ 'status' => 403 ] );
        }

        $ip_address = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : 'anon';
        $limit_key  = 'anima_engine_contact_' . md5( $ip_address );
        $hits       = (int) get_transient( $limit_key );

        if ( $hits >= 5 ) {
            return new WP_Error( 'anima_engine_rate_limited', __( 'Has alcanzado el límite de envíos temporales. Inténtalo más tarde.', 'anima-engine' ), [ 'status' => 429 ] );
        }

        set_transient( $limit_key, $hits + 1, MINUTE_IN_SECONDS * 10 );

        return true;
    }

    /**
     * Procesa el endpoint de contacto.
     */
    public function handle_contact( WP_REST_Request $request ): WP_REST_Response {
        $nombre   = sanitize_text_field( $request['nombre'] ?? '' );
        $email    = sanitize_email( $request['email'] ?? '' );
        $consulta = sanitize_textarea_field( $request['consulta'] ?? '' );

        if ( empty( $nombre ) || empty( $consulta ) || ! is_email( $email ) ) {
            return new WP_REST_Response(
                [ 'error' => __( 'Datos incompletos o correo inválido.', 'anima-engine' ) ],
                400
            );
        }

        $admin_email = get_option( 'admin_email' );
        $subject     = sprintf( __( 'Nuevo mensaje de %s', 'anima-engine' ), $nombre );
        $body        = sprintf(
            "Nombre: %s\nEmail: %s\nConsulta:\n%s",
            $nombre,
            $email,
            $consulta
        );

        $sent = wp_mail( $admin_email, $subject, $body );

        if ( ! $sent ) {
            return new WP_REST_Response(
                [ 'error' => __( 'No se pudo enviar el correo en este momento.', 'anima-engine' ) ],
                500
            );
        }

        return new WP_REST_Response(
            [
                'message' => __( 'Gracias por tu mensaje, te contactaremos muy pronto.', 'anima-engine' ),
            ],
            200
        );
    }

    /**
     * Devuelve una lista de avatares con paginación.
     */
    public function handle_avatars( WP_REST_Request $request ): WP_REST_Response {
        $page       = max( 1, absint( $request->get_param( 'page' ) ?? 1 ) );
        $per_page   = absint( $request->get_param( 'per_page' ) ?? 6 );
        $per_page   = max( 1, min( 20, $per_page ) );
        $tecnologia = sanitize_text_field( $request->get_param( 'tecnologia' ) ?? '' );

        $args = [
            'post_type'      => 'avatar',
            'post_status'    => 'publish',
            'posts_per_page' => $per_page,
            'paged'          => $page,
            'orderby'        => 'date',
            'order'          => 'DESC',
        ];

        if ( ! empty( $tecnologia ) ) {
            $args['tax_query'] = [
                [
                    'taxonomy' => 'tecnologia',
                    'field'    => 'slug',
                    'terms'    => $tecnologia,
                ],
            ];
        }

        $cache_key = 'anima_engine_rest_avatares_' . md5( wp_json_encode( $args ) );
        $cached    = get_transient( $cache_key );

        if ( false === $cached ) {
            $query  = new \WP_Query( $args );
            $items  = [];
            if ( $query->have_posts() ) {
                while ( $query->have_posts() ) {
                    $query->the_post();
                    $items[] = [
                        'id'          => get_the_ID(),
                        'title'       => get_the_title(),
                        'excerpt'     => wp_trim_words( get_the_excerpt(), 24 ),
                        'link'        => get_permalink(),
                        'image'       => get_the_post_thumbnail_url( get_the_ID(), 'medium' ),
                        'tecnologias' => wp_list_pluck( get_the_terms( get_the_ID(), 'tecnologia' ) ?: [], 'name' ),
                    ];
                }
                wp_reset_postdata();
            }

            $cached = [
                'items'      => $items,
                'total'      => (int) ( $query->found_posts ?? 0 ),
                'totalPages' => (int) ( $query->max_num_pages ?? 0 ),
                'page'       => $page,
            ];

            set_transient( $cache_key, $cached, MINUTE_IN_SECONDS * 5 );
        }

        return new WP_REST_Response( $cached, 200 );
    }
}
