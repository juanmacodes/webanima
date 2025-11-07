<?php
namespace Anima\Engine\Api;

use Anima\Engine\Models\Avatar as AvatarModel;
use WP_Error;
use WP_REST_Request;
use WP_REST_Server;
use const MINUTE_IN_SECONDS;

use function __;
use function apply_filters;
use function esc_url_raw;
use function is_array;
use function is_wp_error;
use function register_rest_route;
use function rest_ensure_response;
use function sanitize_file_name;
use function sanitize_text_field;
use function wp_handle_upload;
use function wp_upload_bits;
use function current_time;
use function base64_decode;
use function wp_generate_password;

/**
 * Controlador REST para los avatares del usuario autenticado.
 */
class AvatarController {
    protected JwtManager $jwt;

    protected AvatarModel $avatars;

    /**
     * Constructor.
     */
    public function __construct( ?JwtManager $jwt = null, ?AvatarModel $avatars = null ) {
        $this->jwt     = $jwt ?? new JwtManager();
        $this->avatars = $avatars ?? new AvatarModel();
    }

    /**
     * Registra las rutas del controlador.
     */
    public function register_routes(): void {
        register_rest_route(
            'anima/v' . ANIMA_ENGINE_API_VERSION,
            '/avatar',
            [
                [
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => [ $this, 'get_avatar' ],
                    'permission_callback' => '__return_true',
                ],
                [
                    'methods'             => WP_REST_Server::CREATABLE,
                    'callback'            => [ $this, 'save_avatar' ],
                    'permission_callback' => '__return_true',
                    'args'                => [
                        'glb_url'    => [
                            'required'          => true,
                            'sanitize_callback' => 'esc_url_raw',
                        ],
                        'poster_url' => [
                            'required'          => false,
                            'sanitize_callback' => 'esc_url_raw',
                        ],
                    ],
                ],
            ]
        );

        register_rest_route(
            'anima/v' . ANIMA_ENGINE_API_VERSION,
            '/avatar/upload-url',
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'get_upload_url' ],
                'permission_callback' => '__return_true',
                'args'                => [
                    'filename' => [
                        'required'          => false,
                        'sanitize_callback' => 'sanitize_file_name',
                    ],
                    'mime'     => [
                        'required'          => false,
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
            ]
        );
    }

    /**
     * Devuelve el avatar del usuario autenticado.
     */
    public function get_avatar( WP_REST_Request $request ) {
        $user = $this->jwt->authenticate_request( $request );
        if ( is_wp_error( $user ) ) {
            return $user;
        }

        $data = $this->avatars->getByUserId( (int) $user->ID );

        if ( ! is_array( $data ) ) {
            $response = [
                'glb_url'    => '',
                'poster_url' => '',
                'updated_at' => null,
            ];
        } else {
            $response = [
                'glb_url'    => (string) ( $data['glb_url'] ?? '' ),
                'poster_url' => (string) ( $data['poster_url'] ?? '' ),
                'updated_at' => $data['updated_at'] ?? null,
            ];
        }

        return rest_ensure_response( $response );
    }

    /**
     * Guarda o actualiza el avatar del usuario autenticado.
     */
    public function save_avatar( WP_REST_Request $request ) {
        $check = RateLimiter::check( $request, 'avatar_save', 10, MINUTE_IN_SECONDS * 5 );
        if ( is_wp_error( $check ) ) {
            return $check;
        }

        $user = $this->jwt->authenticate_request( $request );
        if ( is_wp_error( $user ) ) {
            return $user;
        }

        $glb_url    = esc_url_raw( $request->get_param( 'glb_url' ) ?? '' );
        $poster_url = esc_url_raw( $request->get_param( 'poster_url' ) ?? '' );

        if ( empty( $glb_url ) ) {
            return new WP_Error( 'anima_engine_invalid_avatar', __( 'Debes indicar la URL del modelo GLB.', 'anima-engine' ), [ 'status' => 400 ] );
        }

        $timestamp = current_time( 'mysql' );

        $saved = $this->avatars->saveForUser(
            (int) $user->ID,
            [
                'glb_url'    => $glb_url,
                'poster_url' => $poster_url,
                'updated_at' => $timestamp,
            ]
        );

        if ( ! $saved ) {
            return new WP_Error( 'anima_engine_avatar_save_failed', __( 'No se pudo guardar el avatar.', 'anima-engine' ), [ 'status' => 500 ] );
        }

        $response = rest_ensure_response(
            [
                'glb_url'    => $glb_url,
                'poster_url' => $poster_url,
                'updated_at' => $timestamp,
            ]
        );

        RateLimiter::reset( $request, 'avatar_save' );

        return $response;
    }

    /**
     * Entrega una URL de subida para el avatar.
     */
    public function get_upload_url( WP_REST_Request $request ) {
        $check = RateLimiter::check( $request, 'avatar_upload', 5, MINUTE_IN_SECONDS * 5 );
        if ( is_wp_error( $check ) ) {
            return $check;
        }

        $user = $this->jwt->authenticate_request( $request );
        if ( is_wp_error( $user ) ) {
            return $user;
        }

        $filename = sanitize_file_name( $request->get_param( 'filename' ) ?? '' );
        $mime     = sanitize_text_field( $request->get_param( 'mime' ) ?? '' );

        $provider = apply_filters( 'anima_engine_avatar_upload_provider', null, $user, [
            'filename' => $filename,
            'mime'     => $mime,
        ] );

        if ( is_callable( $provider ) ) {
            $result = call_user_func( $provider, $filename, $mime, $user );
            if ( is_array( $result ) && isset( $result['upload_url'], $result['public_url'] ) ) {
                $response = rest_ensure_response(
                    [
                        'upload_url' => (string) $result['upload_url'],
                        'public_url' => (string) $result['public_url'],
                    ]
                );

                RateLimiter::reset( $request, 'avatar_upload' );

                return $response;
            }
        }

        $upload = $this->handle_local_upload( $request, $filename, $mime );
        if ( is_wp_error( $upload ) ) {
            return $upload;
        }

        $response = rest_ensure_response(
            [
                'upload_url' => $upload,
                'public_url' => $upload,
            ]
        );

        RateLimiter::reset( $request, 'avatar_upload' );

        return $response;
    }

    /**
     * Maneja la subida usando la biblioteca de medios de WordPress.
     */
    protected function handle_local_upload( WP_REST_Request $request, string $filename, string $mime ) {
        if ( ! empty( $_FILES['file'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
            require_once ABSPATH . 'wp-admin/includes/file.php';

            $file = $_FILES['file']; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

            $result = wp_handle_upload(
                $file,
                [
                    'test_form' => false,
                ]
            );

            if ( isset( $result['error'] ) ) {
                return new WP_Error( 'anima_engine_upload_failed', $result['error'], [ 'status' => 400 ] );
            }

            return (string) $result['url'];
        }

        $content = $request->get_param( 'content' );
        if ( ! empty( $content ) ) {
            require_once ABSPATH . 'wp-admin/includes/file.php';

            $data = base64_decode( (string) $content );
            if ( false === $data ) {
                return new WP_Error( 'anima_engine_invalid_upload', __( 'El contenido enviado no es válido.', 'anima-engine' ), [ 'status' => 400 ] );
            }

            if ( empty( $filename ) ) {
                $filename = 'avatar-' . wp_generate_password( 12, false ) . '.glb';
            }

            $upload = wp_upload_bits( $filename, null, $data );
            if ( ! empty( $upload['error'] ) ) {
                return new WP_Error( 'anima_engine_upload_failed', $upload['error'], [ 'status' => 400 ] );
            }

            return (string) $upload['url'];
        }

        return new WP_Error( 'anima_engine_upload_provider_missing', __( 'No se encontró un proveedor de subida y no se envió un archivo.', 'anima-engine' ), [ 'status' => 400 ] );
    }
}
