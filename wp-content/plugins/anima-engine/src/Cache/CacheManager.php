<?php
namespace Anima\Engine\Cache;

use Anima\Engine\Services\ServiceInterface;

use function add_filter;
use function array_key_exists;
use function get_option;
use function wp_parse_args;

/**
 * Gestiona los flags relacionados con la caché del plugin.
 */
class CacheManager implements ServiceInterface {
    /**
     * {@inheritDoc}
     */
    public function register(): void {
        add_filter( 'anima_engine_cache_get', [ $this, 'maybe_disable_cache_get' ], 10, 3 );
        add_filter( 'anima_engine_cache_set', [ $this, 'maybe_disable_cache_set' ], 10, 5 );
    }

    /**
     * Evita recuperar valores cacheados si la caché está deshabilitada.
     *
     * @param mixed  $value Valor actual del filtro.
     * @param string $key   Clave solicitada.
     * @param string $group Grupo de caché.
     *
     * @return mixed
     */
    public function maybe_disable_cache_get( $value, string $key, string $group ) {
        if ( ! $this->is_cache_enabled() ) {
            return false;
        }

        return $value;
    }

    /**
     * Evita guardar datos en caché cuando la característica está deshabilitada.
     *
     * @param bool   $handled    Si otro filtro ya gestionó el guardado.
     * @param string $key        Clave de caché.
     * @param mixed  $value      Valor a almacenar.
     * @param int    $expiration Tiempo de expiración.
     * @param string $group      Grupo de caché.
     */
    public function maybe_disable_cache_set( bool $handled, string $key, $value, int $expiration, string $group ): bool {
        if ( ! $this->is_cache_enabled() ) {
            return true;
        }

        return $handled;
    }

    /**
     * Comprueba si la caché está activa en la configuración.
     */
    protected function is_cache_enabled(): bool {
        $options  = wp_parse_args(
            get_option( 'anima_engine_options', [] ),
            [ 'enable_cache' => true ]
        );
        $has_flag = array_key_exists( 'enable_cache', $options );

        if ( $has_flag ) {
            return (bool) $options['enable_cache'];
        }

        return true;
    }
}
