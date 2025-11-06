<?php
namespace Anima\Engine\Api;

use Anima\Engine\Commerce\SubscriptionManager;
use WP_Error;
use WP_REST_Request;
use WP_REST_Server;

use function register_rest_route;
use function rest_ensure_response;

/**
 * Controlador REST para exponer el estado de suscripción del usuario.
 */
class SubscriptionController
{
    protected JwtManager $jwt;

    protected SubscriptionManager $subscriptions;

    public function __construct(?JwtManager $jwt = null, ?SubscriptionManager $subscriptions = null)
    {
        $this->jwt           = $jwt ?? new JwtManager();
        $this->subscriptions = $subscriptions ?? new SubscriptionManager();
    }

    public function register_routes(): void
    {
        register_rest_route(
            'anima/v' . ANIMA_ENGINE_API_VERSION,
            '/subscription',
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_subscription' ],
                'permission_callback' => '__return_true',
            ]
        );
    }

    public function get_subscription(WP_REST_Request $request)
    {
        $user = $this->jwt->authenticate_request($request);
        if ($user instanceof WP_Error) {
            return $user;
        }

        $status = $this->subscriptions->getStatus((int) $user->ID);

        return rest_ensure_response([
            'active'        => ! empty($status['active']),
            'plan'          => (string) ($status['plan'] ?? ''),
            'trial_ends_at' => $status['trial_ends_at'] ?? null,
        ]);
    }
}
