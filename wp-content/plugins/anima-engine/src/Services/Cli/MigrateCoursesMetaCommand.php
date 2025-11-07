<?php
namespace Anima\Engine\Services\Cli;

use Anima\Engine\Services\ServiceInterface;
use WP_CLI;
use WP_Query;

use function absint;
use function array_filter;
use function array_map;
use function defined;
use function floatval;
use function function_exists;
use function get_post_meta;
use function is_array;
use function preg_match;
use function sanitize_text_field;
use function sprintf;
use function str_replace;
use function update_post_meta;
use function wp_kses_post;
use function wp_json_decode;
use function wp_json_encode;

/**
 * Migración de metadatos de cursos legacy a las nuevas claves.
 */
class MigrateCoursesMetaCommand implements ServiceInterface {
    public function register(): void {
        if ( defined( 'WP_CLI' ) && WP_CLI ) {
            WP_CLI::add_command( 'anima migrate-cursos-meta', [ $this, 'migrate' ] );
        }
    }

    public function migrate(): void {
        $query = new WP_Query(
            [
                'post_type'      => 'curso',
                'posts_per_page' => -1,
                'post_status'    => 'any',
                'fields'         => 'ids',
            ]
        );

        if ( empty( $query->posts ) ) {
            WP_CLI::success( 'No se encontraron cursos para migrar.' );
            return;
        }

        $migrated = 0;

        foreach ( $query->posts as $post_id ) {
            $migrated += $this->migrate_course_meta( (int) $post_id ) ? 1 : 0;
        }

        WP_CLI::success( sprintf( 'Metadatos verificados en %d cursos.', count( $query->posts ) ) );
        if ( $migrated > 0 ) {
            WP_CLI::log( sprintf( '%d cursos actualizaron metadatos.', $migrated ) );
        }
    }

    protected function migrate_course_meta( int $post_id ): bool {
        $updated = false;

        $updated = $this->maybe_update_meta( $post_id, 'anima_duracion', 'anima_duration_hours', function ( $value ) {
            if ( is_numeric( $value ) ) {
                return absint( $value );
            }

            if ( preg_match( '/(\d+)/', (string) $value, $matches ) ) {
                return absint( $matches[1] ?? 0 );
            }

            return null;
        } ) || $updated;

        $updated = $this->maybe_update_meta( $post_id, 'anima_precio', 'anima_price', function ( $value ) {
            if ( '' === $value || null === $value ) {
                return null;
            }

            $normalized = str_replace( ',', '.', (string) $value );
            return floatval( $normalized );
        } ) || $updated;

        $updated = $this->maybe_update_meta( $post_id, 'anima_requisitos', 'anima_requirements', function ( $value ) {
            return wp_kses_post( (string) $value );
        } ) || $updated;

        $updated = $this->maybe_update_meta( $post_id, 'anima_fechas', 'anima_upcoming_dates', function ( $value ) {
            if ( is_array( $value ) ) {
                return array_filter( array_map( 'sanitize_text_field', $value ) );
            }

            $decoded = wp_json_decode( (string) $value, true );
            if ( ! is_array( $decoded ) ) {
                return null;
            }

            $dates = [];
            foreach ( $decoded as $date ) {
                $date = sanitize_text_field( (string) $date );
                if ( preg_match( '/^\d{4}-\d{2}-\d{2}$/', $date ) ) {
                    $dates[] = $date;
                }
            }

            return $dates;
        } ) || $updated;

        $updated = $this->maybe_update_meta( $post_id, 'anima_temario', 'anima_syllabus', function ( $value ) {
            if ( function_exists( 'anima_engine_sanitize_syllabus' ) ) {
                return anima_engine_sanitize_syllabus( is_string( $value ) ? $value : wp_json_encode( $value ) );
            }

            $decoded = wp_json_decode( (string) $value, true );
            return is_array( $decoded ) ? $decoded : null;
        } ) || $updated;

        $updated = $this->maybe_update_meta( $post_id, 'anima_instructores', 'anima_instructors', function ( $value ) {
            if ( function_exists( 'anima_engine_sanitize_instructors' ) ) {
                if ( is_string( $value ) ) {
                    $value = wp_json_decode( $value, true );
                }

                if ( ! is_array( $value ) ) {
                    $value = [ [ 'name' => (string) $value ] ];
                }

                return anima_engine_sanitize_instructors( wp_json_encode( $value ) );
            }

            if ( is_array( $value ) ) {
                return $value;
            }

            $text = sanitize_text_field( (string) $value );
            if ( '' === $text ) {
                return null;
            }

            return [ [ 'name' => $text ] ];
        } ) || $updated;

        return $updated;
    }

    /**
     * Copia un meta legacy si no existe el nuevo.
     */
    protected function maybe_update_meta( int $post_id, string $legacy_key, string $new_key, callable $transform ): bool {
        $new_value = get_post_meta( $post_id, $new_key, true );
        if ( ! empty( $new_value ) ) {
            return false;
        }

        $legacy_value = get_post_meta( $post_id, $legacy_key, true );
        if ( empty( $legacy_value ) ) {
            return false;
        }

        $transformed = $transform( $legacy_value );
        if ( null === $transformed || '' === $transformed || ( is_array( $transformed ) && empty( $transformed ) ) ) {
            return false;
        }

        update_post_meta( $post_id, $new_key, $transformed );
        WP_CLI::log( sprintf( 'Curso %d: migrado %s -> %s', $post_id, $legacy_key, $new_key ) );
        return true;
    }
}
