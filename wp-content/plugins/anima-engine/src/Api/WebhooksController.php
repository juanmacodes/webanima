<?php
namespace Anima\Engine\Api;

use Anima\Engine\Commerce\Orders;
use Anima\Engine\Commerce\SubscriptionManager;
use WP_Error;
use WP_REST_Request;
use WP_REST_Server;

use function absint;
use function hash_equals;
use function register_rest_route;
use function rest_ensure_response;
use function sanitize_text_field;
use function get_option;
use function hash_hmac;
use function function_exists;
use function is_array;
use function __;

/**
 * Controlador REST para procesar webhooks de WooCommerce.
 */
class WebhooksController
{
    protected Orders $orders;

    protected SubscriptionManager $subscriptions;

    protected ?string $secret;

    public function __construct(?Orders $orders = null, ?SubscriptionManager $subscriptions = null)
    {
        $this->orders         = $orders ?? new Orders();
        $this->subscriptions  = $subscriptions ?? new SubscriptionManager();
        $options              = get_option('anima_engine_options', []);
        $this->secret         = isset($options['webhook_secret']) && '' !== $options['webhook_secret'] ? (string) $options['webhook_secret'] : null;
    }

    public function register_routes(): void
    {
        register_rest_route(
            'anima/v' . ANIMA_ENGINE_API_VERSION,
            '/webhooks/woocommerce',
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'handle_webhook' ],
                'permission_callback' => '__return_true',
            ]
        );
    }

    public function handle_webhook(WP_REST_Request $request)
    {
        $body = $request->get_body();
        if ($this->secret) {
            $signature = $request->get_header('X-Anima-Signature');
            if (! $this->is_valid_signature($signature, $body)) {
                return new WP_Error('anima_engine_invalid_signature', __('La firma del webhook no es válida.', 'anima-engine'), [ 'status' => 401 ]);
            }
        }

        $event   = sanitize_text_field($request->get_param('event') ?? '');
        $payload = $request->get_json_params();
        if (! is_array($payload)) {
            $payload = [];
        }

        switch ($event) {
            case 'order.completed':
                $this->process_order_completed($payload);
                break;
            case 'subscription.activated':
                $this->process_subscription_update($payload, true);
                break;
            case 'subscription.cancelled':
            case 'subscription.expired':
                $this->process_subscription_update($payload, false);
                break;
            default:
                return new WP_Error('anima_engine_unknown_event', __('Evento de webhook no reconocido.', 'anima-engine'), [ 'status' => 400 ]);
        }

        return rest_ensure_response([
            'received' => true,
            'event'    => $event,
        ]);
    }

    protected function process_order_completed(array $payload): void
    {
        $orderId = isset($payload['order_id']) ? absint($payload['order_id']) : 0;
        if ($orderId <= 0) {
            return;
        }

        $this->orders->grant_entitlements_for_order_id($orderId);
    }

    protected function process_subscription_update(array $payload, bool $active): void
    {
        $subscriptionId = isset($payload['subscription_id']) ? absint($payload['subscription_id']) : 0;
        $userId         = isset($payload['user_id']) ? absint($payload['user_id']) : 0;
        $plan           = isset($payload['plan']) ? sanitize_text_field($payload['plan']) : 'anima_live';
        $trialEnds      = isset($payload['trial_ends_at']) ? sanitize_text_field($payload['trial_ends_at']) : null;

        if ($subscriptionId > 0 && function_exists('wcs_get_subscription')) {
            $subscription = \wcs_get_subscription($subscriptionId);
            if ($subscription) {
                $userId    = (int) $subscription->get_user_id();
                $trialEnds = $subscription->get_date('trial_end') ?: $trialEnds;
            }
        }

        if ($userId <= 0) {
            return;
        }

        if ($active) {
            $this->subscriptions->markActive($userId, $plan, $trialEnds, $subscriptionId > 0 ? $subscriptionId : null);
        } else {
            $this->subscriptions->markInactive($userId, $plan, $subscriptionId > 0 ? $subscriptionId : null);
        }
    }

    protected function is_valid_signature(?string $header, string $body): bool
    {
        if (null === $this->secret || '' === $this->secret) {
            return true;
        }

        if (null === $header || '' === $header) {
            return false;
        }

        $expected = hash_hmac('sha256', $body, $this->secret ?? '');

        return hash_equals($expected, $header);
    }
}
