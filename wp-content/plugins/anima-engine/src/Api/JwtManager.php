<?php
namespace Anima\Engine\Api;

use WP_Error;
use WP_REST_Request;
use WP_User;

use const DAY_IN_SECONDS;
use const MINUTE_IN_SECONDS;

use function __;
use function apply_filters;
use function array_filter;
use function array_map;
use function array_values;
use function get_user_by;
use function get_user_meta;
use function hash_equals;
use function is_array;
use function is_user_logged_in;
use function json_decode;
use function preg_match;
use function sanitize_text_field;
use function time;
use function update_user_meta;
use function wp_get_current_user;
use function wp_json_encode;
use function wp_parse_args;
use function wp_set_current_user;
use function wp_unslash;

use const JSON_THROW_ON_ERROR;

/**
 * Gestor de tokens JWT para la API.
 */
class JwtManager {
    protected const META_KEY = '_anima_refresh_tokens';

    /**
     * Llave secreta utilizada para firmar los tokens.
     */
    protected ?string $secret;

    /**
     * Constructor.
     */
    public function __construct( ?string $secret = null ) {
        if ( null === $secret && defined( 'ANIMA_JWT_SECRET' ) ) {
            $secret = (string) ANIMA_JWT_SECRET;
        }

        $this->secret = $secret ? trim( $secret ) : null;
    }

    /**
     * Indica si existe una clave configurada.
     */
    public function has_secret(): bool {
        return ! empty( $this->secret );
    }

    /**
     * Genera un token de acceso para el usuario dado.
     */
    public function create_access_token( WP_User $user ) {
        $ttl = (int) apply_filters( 'anima_engine_auth_access_token_ttl', 15 * MINUTE_IN_SECONDS );
        if ( $ttl <= 0 ) {
            $ttl = 15 * MINUTE_IN_SECONDS;
        }

        $issued_at = time();
        $payload   = [
            'sub'           => (int) $user->ID,
            'type'          => 'access',
            'iat'           => $issued_at,
            'exp'           => $issued_at + $ttl,
            'email'         => $user->user_email,
            'display_name'  => $user->display_name,
        ];

        return $this->encode_token( $payload );
    }

    /**
     * Genera un token de refresco y lo registra.
     */
    public function create_refresh_token( WP_User $user ) {
        $ttl = (int) apply_filters( 'anima_engine_auth_refresh_token_ttl', 30 * DAY_IN_SECONDS );
        if ( $ttl <= 0 ) {
            $ttl = 30 * DAY_IN_SECONDS;
        }

        $issued_at = time();
        $payload   = [
            'sub'  => (int) $user->ID,
            'type' => 'refresh',
            'iat'  => $issued_at,
            'exp'  => $issued_at + $ttl,
            'jti'  => bin2hex( random_bytes( 16 ) ),
        ];

        $token = $this->encode_token( $payload );

        if ( is_wp_error( $token ) ) {
            return $token;
        }

        $this->store_refresh_token( (int) $user->ID, $token, (int) $payload['exp'], (string) $payload['jti'] );

        return $token;
    }

    /**
     * Valida un token de acceso.
     */
    public function validate_access_token( string $token ) {
        $payload = $this->decode_token( $token );

        if ( is_wp_error( $payload ) ) {
            return $payload;
        }

        if ( ( $payload['type'] ?? '' ) !== 'access' ) {
            return new WP_Error( 'anima_engine_invalid_token_type', __( 'El token de acceso es inválido.', 'anima-engine' ), [ 'status' => 401 ] );
        }

        return $payload;
    }

    /**
     * Valida un token de refresco y verifica que exista en la whitelist del usuario.
     */
    public function validate_refresh_token( string $token ) {
        $payload = $this->decode_token( $token );

        if ( is_wp_error( $payload ) ) {
            return $payload;
        }

        if ( ( $payload['type'] ?? '' ) !== 'refresh' ) {
            return new WP_Error( 'anima_engine_invalid_refresh', __( 'El token de refresco es inválido.', 'anima-engine' ), [ 'status' => 401 ] );
        }

        $user_id = isset( $payload['sub'] ) ? (int) $payload['sub'] : 0;
        if ( $user_id <= 0 ) {
            return new WP_Error( 'anima_engine_invalid_refresh_subject', __( 'El token de refresco no tiene usuario asociado.', 'anima-engine' ), [ 'status' => 401 ] );
        }

        $this->prune_refresh_tokens( $user_id );

        $stored = $this->get_refresh_token_records( $user_id );
        $hash   = $this->hash_refresh_token( $token );

        foreach ( $stored as $record ) {
            if ( empty( $record['hash'] ) ) {
                continue;
            }

            if ( hash_equals( $record['hash'], $hash ) ) {
                return [
                    'payload' => $payload,
                    'user_id' => $user_id,
                    'jti'     => $record['jti'] ?? '',
                ];
            }
        }

        return new WP_Error( 'anima_engine_refresh_not_found', __( 'El token de refresco no está autorizado.', 'anima-engine' ), [ 'status' => 401 ] );
    }

    /**
     * Reemplaza un token de refresco por uno nuevo y entrega un nuevo access token.
     */
    public function rotate_refresh_token( string $token ) {
        $validation = $this->validate_refresh_token( $token );
        if ( is_wp_error( $validation ) ) {
            return $validation;
        }

        $user = get_user_by( 'id', (int) $validation['user_id'] );
        if ( ! $user instanceof WP_User ) {
            return new WP_Error( 'anima_engine_user_not_found', __( 'El usuario asociado al token no existe.', 'anima-engine' ), [ 'status' => 401 ] );
        }

        $this->remove_refresh_token( (int) $user->ID, $this->hash_refresh_token( $token ) );

        $access_token  = $this->create_access_token( $user );
        $refresh_token = $this->create_refresh_token( $user );

        if ( is_wp_error( $access_token ) ) {
            return $access_token;
        }

        if ( is_wp_error( $refresh_token ) ) {
            return $refresh_token;
        }

        return [
            'access_token'  => $access_token,
            'refresh_token' => $refresh_token,
            'user_id'       => (int) $user->ID,
        ];
    }

    /**
     * Revoca un token de refresco.
     */
    public function revoke_refresh_token( string $token ): bool {
        $payload = $this->validate_refresh_token( $token );

        if ( is_wp_error( $payload ) ) {
            return false;
        }

        $this->remove_refresh_token( (int) $payload['user_id'], $this->hash_refresh_token( $token ) );

        return true;
    }

    /**
     * Intenta autenticar la petición usando encabezados Bearer o la sesión actual.
     */
    public function authenticate_request( WP_REST_Request $request ) {
        if ( is_user_logged_in() ) {
            return wp_get_current_user();
        }

        $token = $this->extract_bearer_token( $request );
        if ( '' === $token ) {
            return new WP_Error( 'anima_engine_missing_token', __( 'Se requiere un token de acceso.', 'anima-engine' ), [ 'status' => 401 ] );
        }

        $payload = $this->validate_access_token( $token );
        if ( is_wp_error( $payload ) ) {
            return $payload;
        }

        $user_id = isset( $payload['sub'] ) ? (int) $payload['sub'] : 0;
        if ( $user_id <= 0 ) {
            return new WP_Error( 'anima_engine_invalid_token_subject', __( 'El token de acceso no contiene un usuario válido.', 'anima-engine' ), [ 'status' => 401 ] );
        }

        $user = get_user_by( 'id', $user_id );
        if ( ! $user instanceof WP_User ) {
            return new WP_Error( 'anima_engine_user_not_found', __( 'El usuario asociado al token no existe.', 'anima-engine' ), [ 'status' => 401 ] );
        }

        wp_set_current_user( $user_id );

        return $user;
    }

    /**
     * Elimina tokens vencidos de la whitelist de un usuario.
     */
    public function prune_refresh_tokens( int $user_id ): void {
        $stored = $this->get_refresh_token_records( $user_id );
        if ( empty( $stored ) ) {
            return;
        }

        $now    = time();
        $pruned = array_values( array_filter(
            $stored,
            static function ( array $record ) use ( $now ): bool {
                if ( empty( $record['expires_at'] ) ) {
                    return false;
                }

                return (int) $record['expires_at'] >= $now;
            }
        ) );

        if ( count( $pruned ) !== count( $stored ) ) {
            update_user_meta( $user_id, self::META_KEY, $pruned );
        }
    }

    /**
     * Firma y serializa un payload en formato JWT.
     */
    protected function encode_token( array $payload ) {
        $secret = $this->get_secret();
        if ( is_wp_error( $secret ) ) {
            return $secret;
        }

        $header = [ 'alg' => 'HS256', 'typ' => 'JWT' ];

        try {
            $segments = [
                $this->base64url_encode( wp_json_encode( $header, JSON_THROW_ON_ERROR ) ),
                $this->base64url_encode( wp_json_encode( $payload, JSON_THROW_ON_ERROR ) ),
            ];
        } catch ( \JsonException $e ) {
            return new WP_Error( 'anima_engine_token_encoding_error', $e->getMessage(), [ 'status' => 500 ] );
        }

        $signature = hash_hmac( 'sha256', implode( '.', $segments ), $secret, true );
        $segments[] = $this->base64url_encode( $signature );

        return implode( '.', $segments );
    }

    /**
     * Decodifica y valida un token JWT.
     */
    protected function decode_token( string $token ) {
        $secret = $this->get_secret();
        if ( is_wp_error( $secret ) ) {
            return $secret;
        }

        $parts = explode( '.', $token );
        if ( 3 !== count( $parts ) ) {
            return new WP_Error( 'anima_engine_malformed_token', __( 'El token proporcionado es inválido.', 'anima-engine' ), [ 'status' => 401 ] );
        }

        $header_json = $this->base64url_decode( $parts[0] );
        $payload_json = $this->base64url_decode( $parts[1] );
        $signature    = $this->base64url_decode( $parts[2] );

        if ( false === $header_json || false === $payload_json || false === $signature ) {
            return new WP_Error( 'anima_engine_token_decoding_error', __( 'No fue posible decodificar el token.', 'anima-engine' ), [ 'status' => 401 ] );
        }

        $expected = hash_hmac( 'sha256', $parts[0] . '.' . $parts[1], $secret, true );
        if ( ! hash_equals( $expected, $signature ) ) {
            return new WP_Error( 'anima_engine_invalid_signature', __( 'La firma del token es inválida.', 'anima-engine' ), [ 'status' => 401 ] );
        }

        $payload = json_decode( $payload_json, true );
        if ( ! is_array( $payload ) ) {
            return new WP_Error( 'anima_engine_invalid_payload', __( 'El token contiene un payload inválido.', 'anima-engine' ), [ 'status' => 401 ] );
        }

        if ( isset( $payload['exp'] ) && time() >= (int) $payload['exp'] ) {
            return new WP_Error( 'anima_engine_token_expired', __( 'El token ha expirado.', 'anima-engine' ), [ 'status' => 401 ] );
        }

        return $payload;
    }

    /**
     * Obtiene la clave secreta o devuelve un error si no está configurada.
     */
    protected function get_secret() {
        if ( $this->has_secret() ) {
            return $this->secret;
        }

        return new WP_Error( 'anima_engine_missing_secret', __( 'No se ha configurado la clave JWT.', 'anima-engine' ), [ 'status' => 500 ] );
    }

    /**
     * Registra un token de refresco en la lista blanca de un usuario.
     */
    protected function store_refresh_token( int $user_id, string $token, int $expires_at, string $jti ): void {
        $this->prune_refresh_tokens( $user_id );

        $tokens = $this->get_refresh_token_records( $user_id );
        $tokens[] = [
            'hash'       => $this->hash_refresh_token( $token ),
            'expires_at' => $expires_at,
            'issued_at'  => time(),
            'jti'        => $jti,
        ];

        $max_tokens = (int) apply_filters( 'anima_engine_auth_max_refresh_tokens', 10, $user_id );
        if ( $max_tokens > 0 && count( $tokens ) > $max_tokens ) {
            $tokens = array_slice( $tokens, - $max_tokens );
        }

        update_user_meta( $user_id, self::META_KEY, $tokens );
    }

    /**
     * Recupera los tokens almacenados de un usuario.
     */
    protected function get_refresh_token_records( int $user_id ): array {
        $stored = get_user_meta( $user_id, self::META_KEY, true );
        if ( ! is_array( $stored ) ) {
            return [];
        }

        return array_values(
            array_map(
                static function ( $record ): array {
                    $defaults = [
                        'hash'       => '',
                        'expires_at' => 0,
                        'issued_at'  => 0,
                        'jti'        => '',
                    ];

                    if ( ! is_array( $record ) ) {
                        $record = [];
                    }

                    $record = wp_parse_args( $record, $defaults );
                    $record['hash']       = (string) $record['hash'];
                    $record['expires_at'] = (int) $record['expires_at'];
                    $record['issued_at']  = (int) $record['issued_at'];
                    $record['jti']        = (string) $record['jti'];

                    return $record;
                },
                $stored
            )
        );
    }

    /**
     * Elimina un token del listado del usuario.
     */
    protected function remove_refresh_token( int $user_id, string $hash ): void {
        $stored = $this->get_refresh_token_records( $user_id );
        if ( empty( $stored ) ) {
            return;
        }

        $updated = array_values(
            array_filter(
                $stored,
                static function ( array $record ) use ( $hash ): bool {
                    if ( empty( $record['hash'] ) ) {
                        return false;
                    }

                    return ! hash_equals( $record['hash'], $hash );
                }
            )
        );

        update_user_meta( $user_id, self::META_KEY, $updated );
    }

    /**
     * Obtiene el token Bearer del encabezado Authorization.
     */
    protected function extract_bearer_token( WP_REST_Request $request ): string {
        $header = $request->get_header( 'Authorization' );

        if ( empty( $header ) && isset( $_SERVER['HTTP_AUTHORIZATION'] ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
            $header = sanitize_text_field( wp_unslash( $_SERVER['HTTP_AUTHORIZATION'] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
        }

        if ( empty( $header ) ) {
            return '';
        }

        if ( ! preg_match( '/Bearer\s+(.*)$/i', $header, $matches ) ) {
            return '';
        }

        return trim( (string) $matches[1] );
    }

    /**
     * Calcula el hash de un token de refresco.
     */
    protected function hash_refresh_token( string $token ): string {
        return hash( 'sha256', $token );
    }

    /**
     * Codifica información en formato base64 url-safe.
     */
    protected function base64url_encode( string $data ): string {
        $encoded = base64_encode( $data );
        return rtrim( strtr( $encoded, '+/', '-_' ), '=' );
    }

    /**
     * Decodifica una cadena en formato base64 url-safe.
     */
    protected function base64url_decode( string $data ) {
        $remainder = strlen( $data ) % 4;
        if ( $remainder > 0 ) {
            $data .= str_repeat( '=', 4 - $remainder );
        }

        return base64_decode( strtr( $data, '-_', '+/' ) );
    }
}
