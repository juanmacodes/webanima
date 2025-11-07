<?php
namespace Anima\Engine\Services;

use function add_action;
use function delete_transient;
use function do_action;
use function get_post_type;
use function in_array;
use function is_multisite;

/**
 * Limpia transients y caches asociados a los contenidos.
 */
class CacheInvalidator implements ServiceInterface {
    /**
     * {@inheritDoc}
     */
    public function register(): void {
        add_action( 'save_post', [ $this, 'purge_on_change' ], 20, 2 );
        add_action( 'deleted_post', [ $this, 'purge_on_change' ], 20 );
        add_action( 'set_object_terms', [ $this, 'purge_on_term_change' ] );
        add_action( 'anima_engine_assets_changed', [ $this, 'purge_catalog_cache' ] );
        add_action( 'anima_engine_entitlements_changed', [ $this, 'purge_entitlement_cache' ], 10, 1 );
    }

    /**
     * Elimina caches cuando un contenido cambia.
     */
    public function purge_on_change( int $post_id ): void {
        $post_type = get_post_type( $post_id );
        if ( ! $post_type ) {
            return;
        }

        $types = [ 'curso', 'avatar', 'proyecto', 'experiencia', 'slide' ];
        if ( in_array( $post_type, $types, true ) ) {
            $this->delete_transients_with_prefix( 'anima_engine_gallery_' );
            $this->delete_transients_with_prefix( 'anima_engine_rest_avatares_' );
            delete_transient( 'animaavatar_home_cursos' );
            delete_transient( 'animaavatar_home_avatares' );
        }
    }

    /**
     * Limpia caches relacionadas al catálogo personalizado.
     */
    public function purge_catalog_cache(): void {
        $this->delete_transients_with_prefix( 'anima_engine_catalog_' );
    }

    /**
     * Elimina cache de licencias para el usuario indicado.
     */
    public function purge_entitlement_cache( int $user_id ): void {
        if ( $user_id <= 0 ) {
            return;
        }

        delete_transient( 'anima_engine_entitlements_user_' . $user_id );
        delete_transient( 'anima_engine_entitlements_api_' . $user_id );
    }

    /**
     * Limpia caches cuando cambian taxonomías.
     */
    public function purge_on_term_change(): void {
        $this->delete_transients_with_prefix( 'anima_engine_rest_avatares_' );
    }

    /**
     * Elimina transients según un prefijo.
     */
    protected function delete_transients_with_prefix( string $prefix ): void {
        global $wpdb;

        do_action( 'anima_engine_cache_purge', $prefix );

        $like = $wpdb->esc_like( '_transient_' . $prefix ) . '%';
        $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s", $like ) );

        $timeout_like = $wpdb->esc_like( '_transient_timeout_' . $prefix ) . '%';
        $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s", $timeout_like ) );

        if ( is_multisite() ) {
            $site_like = $wpdb->esc_like( '_site_transient_' . $prefix ) . '%';
            $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->sitemeta} WHERE meta_key LIKE %s", $site_like ) );
            $site_timeout = $wpdb->esc_like( '_site_transient_timeout_' . $prefix ) . '%';
            $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->sitemeta} WHERE meta_key LIKE %s", $site_timeout ) );
        }
    }
}
