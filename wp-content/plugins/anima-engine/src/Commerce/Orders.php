<?php
namespace Anima\Engine\Commerce;

use Anima\Engine\Models\Asset;
use Anima\Engine\Models\Entitlement;
use Anima\Engine\Services\ServiceInterface;
use WC_Order;
use WC_Product;

use function absint;
use function add_action;
use function function_exists;
use function bin2hex;
use function current_time;
use function do_action;
use function wc_get_order;
use function wc_get_orders;
use function wp_generate_password;
use function random_bytes;

/**
 * Servicio encargado de procesar pedidos y otorgar licencias.
 */
class Orders implements ServiceInterface
{
    protected Asset $assets;

    protected Entitlement $entitlements;

    /**
     * Constructor.
     */
    public function __construct(?Asset $assets = null, ?Entitlement $entitlements = null)
    {
        $this->assets       = $assets ?? new Asset();
        $this->entitlements = $entitlements ?? new Entitlement();
    }

    /**
     * {@inheritDoc}
     */
    public function register(): void
    {
        if (! function_exists('add_action') || ! function_exists('wc_get_order')) {
            return;
        }

        add_action('woocommerce_order_status_completed', [ $this, 'handle_order_completed' ], 10, 1);
    }

    /**
     * Acción ejecutada al completarse un pedido.
     */
    public function handle_order_completed($orderId): void
    {
        $orderId = absint($orderId);
        if ($orderId <= 0) {
            return;
        }

        $this->grant_entitlements_for_order_id($orderId);
    }

    /**
     * Procesa un pedido por su ID.
     */
    public function grant_entitlements_for_order_id(int $orderId): int
    {
        if (! function_exists('wc_get_order')) {
            return 0;
        }

        $order = wc_get_order($orderId);
        if (! $order instanceof WC_Order) {
            return 0;
        }

        return $this->grant_entitlements_for_order($order);
    }

    /**
     * Recorre los pedidos completados del usuario y vuelve a otorgar licencias.
     */
    public function rescan_user_orders(int $userId): int
    {
        if (! function_exists('wc_get_orders')) {
            return 0;
        }

        $orders = wc_get_orders([
            'customer_id' => $userId,
            'status'      => [ 'completed' ],
            'limit'       => -1,
        ]);

        if (! is_array($orders)) {
            return 0;
        }

        $granted = 0;
        foreach ($orders as $order) {
            if ($order instanceof WC_Order) {
                $granted += $this->grant_entitlements_for_order($order);
            }
        }

        return $granted;
    }

    /**
     * Otorga licencias para los assets de un pedido.
     */
    public function grant_entitlements_for_order(WC_Order $order): int
    {
        $userId = (int) $order->get_user_id();
        if ($userId <= 0) {
            return 0;
        }

        $granted = 0;

        foreach ($order->get_items() as $item) {
            $product = $item->get_product();
            if (! $product instanceof WC_Product) {
                continue;
            }

            $assetId = (int) $product->get_meta('_anima_asset_id');
            if ($assetId <= 0) {
                continue;
            }

            $asset = $this->assets->getById($assetId);
            if (! is_array($asset) || (isset($asset['active']) && (int) $asset['active'] !== 1)) {
                continue;
            }

            if ($this->grant_entitlement($userId, $asset, (int) $order->get_id())) {
                $granted++;
            }
        }

        if ($granted > 0) {
            do_action('anima_engine_entitlements_changed', $userId);
        }

        return $granted;
    }

    /**
     * Crea o actualiza la licencia para un asset específico.
     */
    protected function grant_entitlement(int $userId, array $asset, int $orderId): bool
    {
        $assetId = isset($asset['id']) ? (int) $asset['id'] : 0;
        if ($assetId <= 0) {
            return false;
        }

        $existing = $this->entitlements->findForUserAsset($userId, $assetId);
        if (is_array($existing)) {
            $data = [
                'source_order' => $orderId,
            ];

            if (empty($existing['license_key'])) {
                $data['license_key'] = $this->generate_license_key($userId, $assetId);
            }

            return $this->entitlements->update((int) $existing['id'], $data);
        }

        $license = $this->generate_license_key($userId, $assetId);

        $created = $this->entitlements->create([
            'user_id'      => $userId,
            'asset_id'     => $assetId,
            'asset_type'   => isset($asset['type']) ? (string) $asset['type'] : '',
            'license_key'  => $license,
            'source_order' => $orderId,
            'expires_at'   => null,
            'created_at'   => current_time('mysql'),
        ]);

        return $created > 0;
    }

    /**
     * Genera una clave de licencia pseudoaleatoria.
     */
    protected function generate_license_key(int $userId, int $assetId): string
    {
        try {
            return bin2hex(random_bytes(20));
        } catch (\Exception $e) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch
            // Ignoramos y usamos el fallback de WordPress.
        }

        return wp_generate_password(32, false, false);
    }
}
