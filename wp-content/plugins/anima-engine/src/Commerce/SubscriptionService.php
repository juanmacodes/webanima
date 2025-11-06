<?php
namespace Anima\Engine\Commerce;

use Anima\Engine\Models\Entitlement;
use Anima\Engine\Services\ServiceInterface;
use WC_Customer;
use WC_Product;

use function add_action;
use function add_filter;
use function function_exists;
use function get_current_user_id;
use function is_numeric;
use function is_object;
use function is_user_logged_in;
use function method_exists;
use function wc_get_product;

/**
 * Servicio que sincroniza estados de suscripción y aplica restricciones de descarga.
 */
class SubscriptionService implements ServiceInterface
{
    protected SubscriptionManager $manager;

    protected Entitlement $entitlements;

    public function __construct(?SubscriptionManager $manager = null, ?Entitlement $entitlements = null)
    {
        $this->manager      = $manager ?? new SubscriptionManager();
        $this->entitlements = $entitlements ?? new Entitlement();
    }

    /** {@inheritDoc} */
    public function register(): void
    {
        add_filter('woocommerce_downloadable_file_permission', [ $this, 'enforce_download_permissions' ], 10, 4);

        if (function_exists('add_action') && function_exists('wcs_get_subscription')) {
            add_action('woocommerce_subscription_status_active', [ $this, 'handle_subscription_active' ], 10, 1);
            add_action('woocommerce_subscription_status_cancelled', [ $this, 'handle_subscription_inactive' ], 10, 1);
            add_action('woocommerce_subscription_status_expired', [ $this, 'handle_subscription_inactive' ], 10, 1);
        }
    }

    /**
     * Devuelve el gestor utilizado por el servicio.
     */
    public function getManager(): SubscriptionManager
    {
        return $this->manager;
    }

    /**
     * Filtro para restringir descargas según el estado de la suscripción.
     */
    public function enforce_download_permissions(bool $allow, int $productId, int $downloadId, $customer): bool
    {
        if (! $allow) {
            return false;
        }

        if (! function_exists('wc_get_product')) {
            return $allow;
        }

        $product = wc_get_product($productId);
        if (! $product instanceof WC_Product) {
            return $allow;
        }

        $assetId = (int) $product->get_meta('_anima_asset_id');
        if ($assetId <= 0) {
            return $allow;
        }

        $userId = 0;
        if ($customer instanceof WC_Customer) {
            $userId = (int) $customer->get_id();
        }

        if ($userId <= 0 && is_user_logged_in()) {
            $userId = (int) get_current_user_id();
        }

        if ($userId <= 0) {
            return false;
        }

        if ($this->manager->userHasActiveSubscription($userId)) {
            return true;
        }

        if ($this->entitlements->userHasEntitlement($userId, $assetId)) {
            return true;
        }

        return false;
    }

    /**
     * Marca la suscripción como activa.
     */
    public function handle_subscription_active($subscription): void
    {
        $object = $this->normalize_subscription($subscription);
        if (null === $object) {
            return;
        }

        $userId = (int) $object->get_user_id();
        if ($userId <= 0) {
            return;
        }

        $trialEnds = $object->get_date('trial_end');

        $this->manager->markActive($userId, 'anima_live', $trialEnds ?: null, (int) $object->get_id());
    }

    /**
     * Marca la suscripción como inactiva.
     */
    public function handle_subscription_inactive($subscription): void
    {
        $object = $this->normalize_subscription($subscription);
        if (null === $object) {
            return;
        }

        $userId = (int) $object->get_user_id();
        if ($userId <= 0) {
            return;
        }

        $this->manager->markInactive($userId, 'anima_live', (int) $object->get_id());
    }

    /**
     * Intenta convertir la entrada en una instancia de suscripción.
     */
    protected function normalize_subscription($subscription)
    {
        if (is_object($subscription) && method_exists($subscription, 'get_id')) {
            return $subscription;
        }

        if (is_numeric($subscription) && function_exists('wcs_get_subscription')) {
            $object = \wcs_get_subscription((int) $subscription);
            if ($object) {
                return $object;
            }
        }

        return null;
    }
}
