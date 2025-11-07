<?php
namespace Anima\Engine\Config;

use Anima\Engine\Services\ServiceInterface;

use const DAY_IN_SECONDS;
use const MINUTE_IN_SECONDS;

use function add_action;
use function add_filter;
use function define;
use function defined;
use function get_option;
use function wp_parse_args;

/**
 * Exposes persisted configuration as runtime constants and filters.
 */
class Settings implements ServiceInterface {
    /**
     * {@inheritDoc}
     */
    public function register(): void {
        add_action( 'plugins_loaded', [ $this, 'bootstrap_constants' ], 5 );
        add_filter( 'anima_engine_auth_access_token_ttl', [ $this, 'filter_access_ttl' ] );
        add_filter( 'anima_engine_auth_refresh_token_ttl', [ $this, 'filter_refresh_ttl' ] );
    }

    /**
     * Defines runtime constants based on stored options.
     */
    public function bootstrap_constants(): void {
        $options = $this->get_options();

        if ( ! defined( 'ANIMA_JWT_SECRET' ) && ! empty( $options['jwt_secret'] ) ) {
            define( 'ANIMA_JWT_SECRET', $options['jwt_secret'] );
        }
    }

    /**
     * Adjust access token TTL using saved settings.
     */
    public function filter_access_ttl( int $ttl ): int {
        $options = $this->get_options();

        if ( ! empty( $options['jwt_access_ttl'] ) ) {
            $value = (int) $options['jwt_access_ttl'];
            if ( $value >= 300 && $value <= DAY_IN_SECONDS ) {
                return $value;
            }
        }

        return $ttl;
    }

    /**
     * Adjust refresh token TTL using saved settings.
     */
    public function filter_refresh_ttl( int $ttl ): int {
        $options = $this->get_options();

        if ( ! empty( $options['jwt_refresh_ttl'] ) ) {
            $value = (int) $options['jwt_refresh_ttl'];
            if ( $value >= DAY_IN_SECONDS && $value <= 90 * DAY_IN_SECONDS ) {
                return $value;
            }
        }

        return $ttl;
    }

    /**
     * Retrieves plugin options with defaults.
     */
    protected function get_options(): array {
        $defaults = [
            'jwt_secret'       => '',
            'jwt_access_ttl'   => 15 * MINUTE_IN_SECONDS,
            'jwt_refresh_ttl'  => 30 * DAY_IN_SECONDS,
        ];

        return wp_parse_args( get_option( 'anima_engine_options', [] ), $defaults );
    }
}
