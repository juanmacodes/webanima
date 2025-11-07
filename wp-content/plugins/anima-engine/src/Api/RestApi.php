<?php
namespace Anima\Engine\Api;

use Anima\Engine\Services\ServiceInterface;
use Anima\Engine\Elementor\Projects\ProjectCardRenderer;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;
use WP_Query;

use const MINUTE_IN_SECONDS;

use function __;
use function absint;
use function add_action;
use function apply_filters;
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
use function is_wp_error;
use function register_rest_route;
use function sanitize_email;
use function sanitize_text_field;
use function sanitize_key;
use function sanitize_textarea_field;
use function set_transient;
use function wp_mail;
use function wp_remote_post;
use function wp_remote_retrieve_body;
use function wp_remote_retrieve_response_code;
use function wp_reset_postdata;
use function wp_trim_words;
use function wp_unslash;
use function wp_verify_nonce;
use function wp_json_encode;
use function wp_list_pluck;
use function sprintf;
use function json_decode;

/**
 * Endpoints REST personalizados del plugin.
 */
class RestApi implements ServiceInterface {
    protected AuthController $authController;

    protected AvatarController $avatarController;

    protected CatalogController $catalogController;

    protected EntitlementsController $entitlementsController;

    protected SubscriptionController $subscriptionController;

    protected WebhooksController $webhooksController;

    public function __construct( ?AuthController $authController = null, ?AvatarController $avatarController = null, ?CatalogController $catalogController = null, ?EntitlementsController $entitlementsController = null, ?SubscriptionController $subscriptionController = null, ?WebhooksController $webhooksController = null ) {
        $this->authController         = $authController ?? new AuthController();
        $this->avatarController       = $avatarController ?? new AvatarController();
        $this->catalogController      = $catalogController ?? new CatalogController();
        $this->entitlementsController = $entitlementsController ?? new EntitlementsController();
        $this->subscriptionController = $subscriptionController ?? new SubscriptionController();
        $this->webhooksController     = $webhooksController ?? new WebhooksController();
    }

    /**
     * {@inheritDoc}
     */
    public function register(): void {
        add_action( 'rest_api_init', [ $this, 'register_routes' ] );
        add_action( 'rest_api_init', [ $this->authController, 'register_routes' ] );
        add_action( 'rest_api_init', [ $this->avatarController, 'register_routes' ] );
        add_action( 'rest_api_init', [ $this->catalogController, 'register_routes' ] );
        add_action( 'rest_api_init', [ $this->entitlementsController, 'register_routes' ] );
        add_action( 'rest_api_init', [ $this->subscriptionController, 'register_routes' ] );
        add_action( 'rest_api_init', [ $this->webhooksController, 'register_routes' ] );
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
                    'recaptcha_token' => [
                        'required'          => false,
                        'sanitize_callback' => 'sanitize_text_field',
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

        register_rest_route(
            'anima/v' . ANIMA_ENGINE_API_VERSION,
            '/proyectos',
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'handle_projects' ],
                'permission_callback' => '__return_true',
                'args'                => [
                    'servicio' => [
                        'sanitize_callback' => 'sanitize_key',
                        'required'          => true,
                    ],
                    'per_page' => [
                        'sanitize_callback' => 'absint',
                    ],
                    'orderby'  => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                    'order'    => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                    'year_min' => [
                        'sanitize_callback' => 'absint',
                    ],
                    'year_max' => [
                        'sanitize_callback' => 'absint',
                    ],
                    'search'   => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                    'layout'   => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                    'card'     => [
                        'required' => false,
                    ],
                    'columns'  => [
                        'required' => false,
                    ],
                    'carousel' => [
                        'required' => false,
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

        $window = (int) apply_filters( 'anima_engine_contact_rate_limit_window', MINUTE_IN_SECONDS * 10, $request );
        if ( $window <= 0 ) {
            $window = MINUTE_IN_SECONDS * 10;
        }

        set_transient( $limit_key, $hits + 1, $window );

        return true;
    }

    /**
     * Procesa el endpoint de contacto.
     */
    public function handle_contact( WP_REST_Request $request ): WP_REST_Response {
        $nombre   = sanitize_text_field( $request['nombre'] ?? '' );
        $email    = sanitize_email( $request['email'] ?? '' );
        $consulta = sanitize_textarea_field( $request['consulta'] ?? '' );
        $token    = sanitize_text_field( $request->get_param( 'recaptcha_token' ) ?? '' );

        if ( empty( $nombre ) || empty( $consulta ) || ! is_email( $email ) ) {
            return new WP_REST_Response(
                [ 'error' => __( 'Datos incompletos o correo inválido.', 'anima-engine' ) ],
                400
            );
        }

        $options        = get_option( 'anima_engine_options', [] );
        $recaptcha_key  = $options['recaptcha_secret_key'] ?? '';
        if ( ! empty( $recaptcha_key ) ) {
            if ( empty( $token ) || ! $this->verify_recaptcha( $token, $recaptcha_key ) ) {
                return new WP_REST_Response(
                    [ 'error' => __( 'No se pudo validar el reCAPTCHA. Inténtalo nuevamente.', 'anima-engine' ) ],
                    400
                );
            }
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
     * Devuelve proyectos filtrados por servicio para el widget de tabs.
     */
    public function handle_projects( WP_REST_Request $request ): WP_REST_Response {
        $servicio = sanitize_key( (string) $request->get_param( 'servicio' ) );

        if ( '' === $servicio ) {
            return new WP_REST_Response(
                [ 'error' => __( 'Debe especificar un servicio válido.', 'anima-engine' ) ],
                400
            );
        }

        $per_page = (int) $request->get_param( 'per_page' );
        if ( $per_page <= 0 ) {
            $per_page = 6;
        }
        $per_page = max( 1, min( 24, $per_page ) );

        $layout  = $this->sanitize_layout( (string) $request->get_param( 'layout' ) );
        $orderby = $this->sanitize_orderby( (string) $request->get_param( 'orderby' ) );
        $order   = $this->sanitize_order( (string) $request->get_param( 'order' ) );

        $year_min = absint( $request->get_param( 'year_min' ) );
        $year_max = absint( $request->get_param( 'year_max' ) );
        $search   = sanitize_text_field( (string) $request->get_param( 'search' ) );

        $card_settings = ProjectCardRenderer::normalize_settings( $this->parse_json_param( $request, 'card' ) );
        $columns       = $this->parse_json_param( $request, 'columns' );
        $carousel      = $this->parse_json_param( $request, 'carousel' );

        $query_args = [
            'post_type'           => 'proyecto',
            'post_status'         => 'publish',
            'posts_per_page'      => $per_page,
            'orderby'             => $orderby,
            'order'               => $order,
            'ignore_sticky_posts' => true,
            'tax_query'           => [
                [
                    'taxonomy' => 'servicio',
                    'field'    => 'slug',
                    'terms'    => $servicio,
                ],
            ],
        ];

        if ( 'meta_value' === $orderby ) {
            $query_args['meta_key'] = 'anima_anio';
        }

        $meta_query = [];
        if ( $year_min && $year_max && $year_max >= $year_min ) {
            $meta_query[] = [
                'key'     => 'anima_anio',
                'value'   => [ $year_min, $year_max ],
                'compare' => 'BETWEEN',
                'type'    => 'NUMERIC',
            ];
        } elseif ( $year_min ) {
            $meta_query[] = [
                'key'     => 'anima_anio',
                'value'   => $year_min,
                'compare' => '>=',
                'type'    => 'NUMERIC',
            ];
        } elseif ( $year_max ) {
            $meta_query[] = [
                'key'     => 'anima_anio',
                'value'   => $year_max,
                'compare' => '<=',
                'type'    => 'NUMERIC',
            ];
        }

        if ( ! empty( $meta_query ) ) {
            $query_args['meta_query'] = $meta_query;
        }

        if ( '' !== $search ) {
            $query_args['s'] = $search;
        }

        $query = new WP_Query( $query_args );

        if ( ! $query->have_posts() ) {
            wp_reset_postdata();

            return new WP_REST_Response(
                [
                    'html'    => '<div class="an-empty" role="status">' . esc_html__( 'No hay proyectos disponibles en este servicio.', 'anima-engine' ) . '</div>',
                    'layout'  => $layout,
                    'found'   => 0,
                    'service' => $servicio,
                ],
                200
            );
        }

        $posts      = $query->posts;
        $cards_html = ProjectCardRenderer::render_cards( $posts, $card_settings );
        $html       = ProjectCardRenderer::wrap_with_layout( $layout, $cards_html, $columns, $carousel );

        wp_reset_postdata();

        return new WP_REST_Response(
            [
                'html'    => $html,
                'layout'  => $layout,
                'found'   => (int) $query->found_posts,
                'service' => $servicio,
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

        $cache_key    = 'anima_engine_rest_avatares_' . md5( wp_json_encode( $args ) );
        $pre_cache    = apply_filters( 'anima_engine_cache_get', null, $cache_key, 'rest' );
        $cached       = null !== $pre_cache ? $pre_cache : get_transient( $cache_key );

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

            $handled = apply_filters( 'anima_engine_cache_set', false, $cache_key, $cached, MINUTE_IN_SECONDS * 5, 'rest' );
            if ( true !== $handled ) {
                set_transient( $cache_key, $cached, MINUTE_IN_SECONDS * 5 );
            }
        }

        return new WP_REST_Response( $cached, 200 );
    }

    /**
     * Obtiene un parámetro JSON y lo transforma en array.
     */
    protected function parse_json_param( WP_REST_Request $request, string $param ): array {
        $value = $request->get_param( $param );

        if ( is_string( $value ) ) {
            $decoded = json_decode( $value, true );
            if ( is_array( $decoded ) ) {
                return $decoded;
            }
        }

        return is_array( $value ) ? $value : [];
    }

    /**
     * Normaliza el layout solicitado.
     */
    protected function sanitize_layout( string $layout ): string {
        $allowed = [ 'grid', 'masonry', 'carousel' ];
        $layout  = sanitize_text_field( $layout );

        return in_array( $layout, $allowed, true ) ? $layout : 'grid';
    }

    /**
     * Valida el campo orderby permitido.
     */
    protected function sanitize_orderby( string $orderby ): string {
        $allowed = [ 'date', 'title', 'meta_value', 'rand' ];
        $value   = sanitize_text_field( $orderby );

        return in_array( $value, $allowed, true ) ? $value : 'date';
    }

    /**
     * Normaliza la dirección del ordenamiento.
     */
    protected function sanitize_order( string $order ): string {
        $value = strtoupper( sanitize_text_field( $order ) );

        return in_array( $value, [ 'ASC', 'DESC' ], true ) ? $value : 'DESC';
    }

    /**
     * Valida un token de reCAPTCHA v3 con el servicio de Google.
     */
    protected function verify_recaptcha( string $token, string $secret ): bool {
        $response = wp_remote_post(
            'https://www.google.com/recaptcha/api/siteverify',
            [
                'body' => [
                    'secret'   => $secret,
                    'response' => $token,
                ],
                'timeout' => 5,
            ]
        );

        if ( is_wp_error( $response ) ) {
            return false;
        }

        if ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
            return false;
        }

        $body = json_decode( wp_remote_retrieve_body( $response ), true );

        return ! empty( $body['success'] );
    }
}
