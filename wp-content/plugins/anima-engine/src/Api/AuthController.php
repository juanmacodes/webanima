<?php
namespace Anima\Engine\Api;

use WP_Error;
use WP_REST_Request;
use WP_REST_Server;
use WP_User;

use const MINUTE_IN_SECONDS;

use function __;
use function add_filter;
use function apply_filters;
use function home_url;
use function is_scalar;
use function in_array;
use function is_wp_error;
use function is_array;
use function register_rest_route;
use function rest_ensure_response;
use function sanitize_text_field;
use function wp_authenticate;
use function wp_set_current_user;
use function wp_unslash;

/**
 * Controlador responsable de la autenticación JWT.
 */
class AuthController {
    protected JwtManager $jwt;

    /**
     * Constructor.
     */
    public function __construct( ?JwtManager $jwt = null ) {
        $this->jwt = $jwt ?? new JwtManager();

        add_filter( 'rest_pre_serve_request', [ $this, 'maybe_add_cors_headers' ], 15, 3 );
    }

    /**
     * Registra las rutas del controlador.
     */
    public function register_routes(): void {
        register_rest_route(
            'anima/v' . ANIMA_ENGINE_API_VERSION,
            '/auth/login',
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'handle_login' ],
                'permission_callback' => '__return_true',
                'args'                => [
                    'username' => [
                        'required'          => true,
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                    'password' => [
                        'required'          => true,
                        'sanitize_callback' => [ $this, 'sanitize_password_field' ],
                    ],
                ],
            ]
        );

        register_rest_route(
            'anima/v' . ANIMA_ENGINE_API_VERSION,
            '/auth/refresh',
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'handle_refresh' ],
                'permission_callback' => '__return_true',
                'args'                => [
                    'refresh_token' => [
                        'required'          => true,
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
            ]
        );

        register_rest_route(
            'anima/v' . ANIMA_ENGINE_API_VERSION,
            '/auth/logout',
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'handle_logout' ],
                'permission_callback' => '__return_true',
                'args'                => [
                    'refresh_token' => [
                        'required'          => true,
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
            ]
        );
    }

    /**
     * Procesa el endpoint de login.
     */
    public function handle_login( WP_REST_Request $request ) {
        $check = RateLimiter::check( $request, 'auth_login', 5, MINUTE_IN_SECONDS );
        if ( is_wp_error( $check ) ) {
            return $check;
        }

        $username = sanitize_text_field( wp_unslash( (string) ( $request->get_param( 'username' ) ?? '' ) ) );
        $password = (string) ( $request->get_param( 'password' ) ?? '' );
        $password = wp_unslash( $password );

        if ( '' === $username || '' === $password ) {
            return new WP_Error( 'anima_engine_missing_credentials', __( 'Debes proporcionar usuario y contraseña.', 'anima-engine' ), [ 'status' => 400 ] );
        }

        $user = wp_authenticate( $username, $password );

        if ( is_wp_error( $user ) || ! $user instanceof WP_User ) {
            return new WP_Error( 'anima_engine_invalid_credentials', __( 'Las credenciales proporcionadas no son válidas.', 'anima-engine' ), [ 'status' => 401 ] );
        }

        if ( ! $this->jwt->has_secret() ) {
            return new WP_Error( 'anima_engine_missing_secret', __( 'No se ha configurado la clave JWT.', 'anima-engine' ), [ 'status' => 500 ] );
        }

        wp_set_current_user( $user->ID );

        $access_token  = $this->jwt->create_access_token( $user );
        $refresh_token = $this->jwt->create_refresh_token( $user );

        if ( is_wp_error( $access_token ) ) {
            return $access_token;
        }

        if ( is_wp_error( $refresh_token ) ) {
            return $refresh_token;
        }

        RateLimiter::reset( $request, 'auth_login' );

        return rest_ensure_response(
            [
                'access_token'  => $access_token,
                'refresh_token' => $refresh_token,
                'user'          => [
                    'id'           => (int) $user->ID,
                    'email'        => $user->user_email,
                    'display_name' => $user->display_name,
                ],
            ]
        );
    }

    /**
     * Endpoint para refrescar tokens.
     */
    public function handle_refresh( WP_REST_Request $request ) {
        $check = RateLimiter::check( $request, 'auth_refresh', 10, MINUTE_IN_SECONDS * 5 );
        if ( is_wp_error( $check ) ) {
            return $check;
        }

        $refresh_token = sanitize_text_field( $request->get_param( 'refresh_token' ) ?? '' );
        if ( '' === $refresh_token ) {
            return new WP_Error( 'anima_engine_missing_refresh', __( 'Debes enviar un token de refresco válido.', 'anima-engine' ), [ 'status' => 400 ] );
        }

        if ( ! $this->jwt->has_secret() ) {
            return new WP_Error( 'anima_engine_missing_secret', __( 'No se ha configurado la clave JWT.', 'anima-engine' ), [ 'status' => 500 ] );
        }

        $result = $this->jwt->rotate_refresh_token( $refresh_token );
        if ( is_wp_error( $result ) ) {
            return $result;
        }

        RateLimiter::reset( $request, 'auth_refresh' );

        return rest_ensure_response(
            [
                'access_token'  => $result['access_token'],
                'refresh_token' => $result['refresh_token'],
            ]
        );
    }

    /**
     * Endpoint para cerrar sesión.
     */
    public function handle_logout( WP_REST_Request $request ) {
        $check = RateLimiter::check( $request, 'auth_logout', 20, MINUTE_IN_SECONDS * 5 );
        if ( is_wp_error( $check ) ) {
            return $check;
        }

        $refresh_token = sanitize_text_field( $request->get_param( 'refresh_token' ) ?? '' );
        if ( '' === $refresh_token ) {
            return new WP_Error( 'anima_engine_missing_refresh', __( 'Debes enviar un token de refresco válido.', 'anima-engine' ), [ 'status' => 400 ] );
        }

        if ( $this->jwt->has_secret() ) {
            $this->jwt->revoke_refresh_token( $refresh_token );
        }

        RateLimiter::reset( $request, 'auth_logout' );

        return rest_ensure_response( [ 'ok' => true ] );
    }

    /**
     * Sanitiza la contraseña sin alterar su contenido.
     */
    public function sanitize_password_field( $value ): string {
        if ( is_string( $value ) ) {
            return $value;
        }

        if ( is_scalar( $value ) ) {
            return (string) $value;
        }

        return '';
    }

    /**
     * Añade cabeceras CORS para orígenes permitidos.
     */
    public function maybe_add_cors_headers( $served, $result, WP_REST_Request $request ) {
        if ( ! $this->is_auth_route( $request->get_route() ) ) {
            return $served;
        }

        $origin = $this->get_request_origin();
        if ( '' === $origin ) {
            return $served;
        }

        $allowed = $this->get_allowed_origins();
        if ( ! in_array( $origin, $allowed, true ) ) {
            return $served;
        }

        header( 'Access-Control-Allow-Origin: ' . $origin );
        header( 'Access-Control-Allow-Credentials: true' );
        header( 'Access-Control-Allow-Methods: POST, OPTIONS' );
        header( 'Access-Control-Allow-Headers: Authorization, Content-Type' );
        header( 'Vary: Origin' );

        return $served;
    }

    /**
     * Determina si la ruta pertenece al conjunto de auth.
     */
    protected function is_auth_route( string $route ): bool {
        return str_starts_with( $route, '/anima/v' . ANIMA_ENGINE_API_VERSION . '/auth' );
    }

    /**
     * Recupera la lista de orígenes permitidos.
     */
    protected function get_allowed_origins(): array {
        $default = [];
        $home    = home_url();
        if ( $home ) {
            $default[] = untrailingslashit( $home );
        }

        $origins = apply_filters( 'anima_engine_auth_allowed_origins', $default );
        if ( ! is_array( $origins ) ) {
            $origins = $default;
        }

        return array_values( array_unique( array_filter( array_map( 'untrailingslashit', $origins ) ) ) );
    }

    /**
     * Obtiene el origen de la petición.
     */
    protected function get_request_origin(): string {
        if ( isset( $_SERVER['HTTP_ORIGIN'] ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
            return sanitize_text_field( wp_unslash( $_SERVER['HTTP_ORIGIN'] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
        }

        return '';
    }
}
