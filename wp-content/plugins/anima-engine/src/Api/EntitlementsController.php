<?php
namespace Anima\Engine\Api;

use Anima\Engine\Commerce\Orders;
use Anima\Engine\Models\Entitlement;
use WP_Error;
use WP_REST_Request;
use WP_REST_Server;

use const MINUTE_IN_SECONDS;

use function absint;
use function array_map;
use function delete_transient;
use function current_user_can;
use function function_exists;
use function get_option;
use function get_transient;
use function is_array;
use function register_rest_route;
use function rest_ensure_response;
use function set_transient;
use function wc_get_order;
use function __;

/**
 * Controlador REST para gestionar los entitlements del usuario.
 */
class EntitlementsController
{
    protected JwtManager $jwt;

    protected Entitlement $entitlements;

    protected Orders $orders;

    public function __construct(?JwtManager $jwt = null, ?Entitlement $entitlements = null, ?Orders $orders = null)
    {
        $this->jwt          = $jwt ?? new JwtManager();
        $this->entitlements = $entitlements ?? new Entitlement();
        $this->orders       = $orders ?? new Orders();
    }

    public function register_routes(): void
    {
        register_rest_route(
            'anima/v' . ANIMA_ENGINE_API_VERSION,
            '/entitlements',
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'list_entitlements' ],
                'permission_callback' => '__return_true',
            ]
        );

        register_rest_route(
            'anima/v' . ANIMA_ENGINE_API_VERSION,
            '/entitlements/claim',
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'claim_entitlements' ],
                'permission_callback' => '__return_true',
                'args'                => [
                    'order_id' => [
                        'required'          => false,
                        'sanitize_callback' => 'absint',
                    ],
                ],
            ]
        );
    }

    /**
     * Devuelve la lista de entitlements del usuario autenticado.
     */
    public function list_entitlements(WP_REST_Request $request)
    {
        $check = RateLimiter::check($request, 'entitlements_list', 12, MINUTE_IN_SECONDS * 2);
        if ($check instanceof WP_Error) {
            return $check;
        }

        $user = $this->jwt->authenticate_request($request);
        if ($user instanceof WP_Error) {
            return $user;
        }

        $cacheKey = $this->build_cache_key((int) $user->ID);
        if ($this->is_cache_enabled()) {
            $cached = get_transient($cacheKey);
            if (false !== $cached && is_array($cached)) {
                $response = rest_ensure_response($cached);
                RateLimiter::reset($request, 'entitlements_list');

                return $response;
            }
        }

        $records = $this->entitlements->getWithAssetsForUser((int) $user->ID);

        $items = array_map(
            static function (array $row): array {
                return [
                    'asset_id'   => isset($row['asset_id']) ? (int) $row['asset_id'] : 0,
                    'asset_type' => isset($row['asset_type']) ? (string) $row['asset_type'] : '',
                    'title'      => isset($row['title']) ? (string) $row['title'] : '',
                    'media_url'  => isset($row['media_url']) ? (string) $row['media_url'] : '',
                    'expires_at' => $row['expires_at'] ?? null,
                ];
            },
            is_array($records) ? $records : []
        );

        if ($this->is_cache_enabled()) {
            set_transient($cacheKey, $items, $this->get_cache_ttl());
        }

        $response = rest_ensure_response($items);
        RateLimiter::reset($request, 'entitlements_list');

        return $response;
    }

    /**
     * Fuerza la reasignación de licencias para el usuario actual.
     */
    public function claim_entitlements(WP_REST_Request $request)
    {
        $check = RateLimiter::check($request, 'entitlements_claim', 5, MINUTE_IN_SECONDS * 5);
        if ($check instanceof WP_Error) {
            return $check;
        }

        $user = $this->jwt->authenticate_request($request);
        if ($user instanceof WP_Error) {
            return $user;
        }

        if (! function_exists('wc_get_order')) {
            return new WP_Error('anima_engine_woocommerce_missing', __('WooCommerce no está disponible en el sitio.', 'anima-engine'), [ 'status' => 501 ]);
        }

        $orderId = absint($request->get_param('order_id') ?? 0);
        $granted = 0;

        if ($orderId > 0) {
            $order = wc_get_order($orderId);
            if (! $order) {
                return new WP_Error('anima_engine_order_not_found', __('El pedido indicado no existe.', 'anima-engine'), [ 'status' => 404 ]);
            }

            if ((int) $order->get_user_id() !== (int) $user->ID && ! current_user_can('manage_woocommerce')) {
                return new WP_Error('anima_engine_forbidden_order', __('No tienes permiso para reclamar este pedido.', 'anima-engine'), [ 'status' => 403 ]);
            }

            $granted = $this->orders->grant_entitlements_for_order($order);
        } else {
            $granted = $this->orders->rescan_user_orders((int) $user->ID);
        }

        if ($granted > 0) {
            delete_transient($this->build_cache_key((int) $user->ID));
        }

        $response = rest_ensure_response([
            'granted'  => (int) $granted,
            'order_id' => $orderId > 0 ? $orderId : null,
        ]);

        RateLimiter::reset($request, 'entitlements_claim');

        return $response;
    }

    protected function build_cache_key(int $userId): string
    {
        return 'anima_engine_entitlements_api_' . $userId;
    }

    protected function is_cache_enabled(): bool
    {
        $options = get_option('anima_engine_options', []);
        if (array_key_exists('cache_entitlements', $options)) {
            return (bool) $options['cache_entitlements'];
        }

        return true;
    }

    protected function get_cache_ttl(): int
    {
        return MINUTE_IN_SECONDS * 2;
    }
}
