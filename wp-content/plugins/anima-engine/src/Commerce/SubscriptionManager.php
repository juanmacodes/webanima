<?php
namespace Anima\Engine\Commerce;

use function get_user_meta;
use function is_array;
use function update_user_meta;

/**
 * Gestor sencillo para almacenar y consultar el estado de suscripciones de los usuarios.
 */
class SubscriptionManager
{
    protected const META_KEY = '_anima_subscription_status';

    /**
     * Devuelve el estado actual de la suscripción del usuario.
     *
     * @return array{active: bool, plan: string, trial_ends_at: ?string, subscription_id?: int}
     */
    public function getStatus(int $userId): array
    {
        $data = get_user_meta($userId, self::META_KEY, true);
        if (! is_array($data)) {
            $data = [];
        }

        return [
            'active'        => ! empty($data['active']),
            'plan'          => isset($data['plan']) ? (string) $data['plan'] : '',
            'trial_ends_at' => isset($data['trial_ends_at']) && '' !== $data['trial_ends_at'] ? (string) $data['trial_ends_at'] : null,
            'subscription_id' => isset($data['subscription_id']) ? (int) $data['subscription_id'] : 0,
        ];
    }

    /**
     * Marca una suscripción como activa para el usuario.
     */
    public function markActive(int $userId, string $plan, ?string $trialEndsAt = null, ?int $subscriptionId = null): void
    {
        $status = [
            'active'          => true,
            'plan'            => $plan,
            'trial_ends_at'   => $trialEndsAt,
        ];

        if (null !== $subscriptionId && $subscriptionId > 0) {
            $status['subscription_id'] = $subscriptionId;
        }

        update_user_meta($userId, self::META_KEY, $status);
    }

    /**
     * Marca la suscripción del usuario como inactiva.
     */
    public function markInactive(int $userId, string $plan = '', ?int $subscriptionId = null): void
    {
        $status = [
            'active'          => false,
            'plan'            => $plan,
            'trial_ends_at'   => null,
        ];

        if (null !== $subscriptionId && $subscriptionId > 0) {
            $status['subscription_id'] = $subscriptionId;
        }

        update_user_meta($userId, self::META_KEY, $status);
    }

    /**
     * Indica si el usuario cuenta con una suscripción activa.
     */
    public function userHasActiveSubscription(int $userId): bool
    {
        $status = $this->getStatus($userId);

        return ! empty($status['active']);
    }
}
