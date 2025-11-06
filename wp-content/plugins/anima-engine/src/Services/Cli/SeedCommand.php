<?php
namespace Anima\Engine\Services\Cli;

use Anima\Engine\Services\ServiceInterface;
use WP_CLI;
use WP_Error;

use function absint;
use function array_key_exists;
use function defined;
use function get_option;
use function home_url;
use function is_wp_error;
use function sprintf;
use function term_exists;
use function wp_count_posts;
use function wp_insert_post;
use function wp_insert_term;
use function wp_parse_args;
use function wp_set_object_terms;

/**
 * Comando WP-CLI para sembrar contenido de demostración.
 */
class SeedCommand implements ServiceInterface {
    /**
     * {@inheritDoc}
     */
    public function register(): void {
        if ( defined( 'WP_CLI' ) && WP_CLI ) {
            WP_CLI::add_command( 'anima seed', [ $this, 'seed' ] );
        }
    }

    /**
     * Ejecuta la generación de contenido demo.
     */
    public function seed(): void {
        if ( ! $this->is_database_empty() ) {
            WP_CLI::warning( 'Ya existe contenido publicado, se omite la carga de demo.' );
            return;
        }

        $this->maybe_seed_terms();
        $this->create_courses();
        $this->create_avatars();

        if ( $this->is_feature_enabled( 'enable_slider', true ) ) {
            $this->create_slides();
        }

        WP_CLI::success( 'Contenido de demostración generado correctamente.' );
    }

    /**
     * Comprueba si existen entradas publicadas relevantes.
     */
    protected function is_database_empty(): bool {
        $post_types = [ 'curso', 'avatar', 'slide' ];

        foreach ( $post_types as $type ) {
            $counts = wp_count_posts( $type );
            if ( $counts && (int) ( $counts->publish ?? 0 ) > 0 ) {
                return false;
            }
        }

        return true;
    }

    /**
     * Asegura que existan los términos base usados en las demos.
     */
    protected function maybe_seed_terms(): void {
        $taxonomies = [
            'nivel'      => [ 'Inicial', 'Intermedio', 'Avanzado' ],
            'modalidad'  => [ 'Online', 'Híbrido', 'On-demand' ],
            'tecnologia' => [ 'Unity', 'Unreal', 'WebGL', 'XR', 'IA' ],
        ];

        foreach ( $taxonomies as $taxonomy => $terms ) {
            foreach ( $terms as $term ) {
                if ( ! term_exists( $term, $taxonomy ) ) {
                    wp_insert_term( $term, $taxonomy );
                }
            }
        }
    }

    /**
     * Crea cursos de muestra.
     */
    protected function create_courses(): void {
        $courses = [
            [
                'post_title'   => 'Curso de producción XR',
                'post_excerpt' => 'Aprende a producir experiencias inmersivas con pipelines colaborativos.',
                'post_content' => '<p>Incluye workshops en vivo, mentorías y plantillas de producción.</p>',
                'meta_input'   => [
                    'anima_instructores' => 'Equipo Anima',
                    'anima_duracion'     => '8 semanas',
                    'anima_dificultad'   => 'Intermedio',
                    'anima_kpis'         => 'Aumento del engagement en 35%, retención 80%',
                    'anima_url_demo'     => home_url( '/experiencia-inmersiva' ),
                ],
                'tax_input'    => [
                    'nivel'      => [ 'Intermedio' ],
                    'modalidad'  => [ 'Online' ],
                    'tecnologia' => [ 'XR', 'Unity' ],
                ],
            ],
            [
                'post_title'   => 'Curso de avatares realtime',
                'post_excerpt' => 'Pipeline completo para generar avatares listos para streaming.',
                'post_content' => '<p>Desde captura hasta optimización para Unreal Engine y WebGL.</p>',
                'meta_input'   => [
                    'anima_instructores' => 'Anima Studio',
                    'anima_duracion'     => '5 semanas',
                    'anima_dificultad'   => 'Avanzado',
                    'anima_kpis'         => 'Entrega de 3 avatares optimizados por estudiante',
                    'anima_url_demo'     => home_url( '/anima-live' ),
                ],
                'tax_input'    => [
                    'nivel'      => [ 'Avanzado' ],
                    'modalidad'  => [ 'Híbrido' ],
                    'tecnologia' => [ 'Unreal', 'WebGL' ],
                ],
            ],
        ];

        foreach ( $courses as $course ) {
            $data = wp_parse_args(
                $course,
                [
                    'post_status' => 'publish',
                    'post_type'   => 'curso',
                ]
            );

            $course_id = wp_insert_post( $data, true );

            if ( $course_id instanceof WP_Error ) {
                WP_CLI::warning( sprintf( 'No se pudo crear el curso: %s', $data['post_title'] ) );
                continue;
            }

            foreach ( $course['tax_input'] ?? [] as $taxonomy => $terms ) {
                wp_set_object_terms( $course_id, $terms, $taxonomy );
            }
        }
    }

    /**
     * Genera avatares de ejemplo.
     */
    protected function create_avatars(): void {
        $avatars = [
            [
                'post_title'   => 'Avatar volumétrico Aurora',
                'post_excerpt' => 'Avatar holográfico optimizado para eventos híbridos.',
                'post_content' => '<p>Incluye blendshapes, sincronización labial y presets de iluminación.</p>',
                'meta_input'   => [
                    'anima_kpis'     => 'Tiempo de interacción promedio: 12 minutos',
                    'anima_url_demo' => 'https://example.com/aurora.glb',
                ],
                'tax_input'    => [
                    'tecnologia' => [ 'WebGL', 'XR' ],
                ],
            ],
            [
                'post_title'   => 'Avatar IA Atlas',
                'post_excerpt' => 'Companion virtual entrenado con asistentes conversacionales.',
                'post_content' => '<p>Disponible para integraciones en Unreal Engine y aplicaciones WebXR.</p>',
                'meta_input'   => [
                    'anima_kpis'     => 'Satisfacción usuaria 4.8/5 en experiencias piloto',
                    'anima_url_demo' => 'https://example.com/atlas.glb',
                ],
                'tax_input'    => [
                    'tecnologia' => [ 'IA', 'Unreal' ],
                ],
            ],
        ];

        foreach ( $avatars as $avatar ) {
            $data = wp_parse_args(
                $avatar,
                [
                    'post_status' => 'publish',
                    'post_type'   => 'avatar',
                ]
            );

            $avatar_id = wp_insert_post( $data, true );

            if ( $avatar_id instanceof WP_Error ) {
                WP_CLI::warning( sprintf( 'No se pudo crear el avatar: %s', $data['post_title'] ) );
                continue;
            }

            foreach ( $avatar['tax_input'] ?? [] as $taxonomy => $terms ) {
                wp_set_object_terms( $avatar_id, $terms, $taxonomy );
            }
        }
    }

    /**
     * Crea slides iniciales cuando el slider está habilitado.
     */
    protected function create_slides(): void {
        for ( $i = 1; $i <= 3; $i++ ) {
            $slide_id = wp_insert_post(
                [
                    'post_title'   => sprintf( 'Slide demo %d', $i ),
                    'post_content' => '<p>Slide creada desde el comando wp anima seed.</p>',
                    'post_status'  => 'publish',
                    'post_type'    => 'slide',
                    'meta_input'   => [
                        'anima_slide_url'         => home_url( '/contacto' ),
                        'anima_slide_placeholder' => sprintf( 'https://picsum.photos/1600/900?image=%d', absint( $i + 20 ) ),
                    ],
                ],
                true
            );

            if ( $slide_id instanceof WP_Error ) {
                WP_CLI::warning( sprintf( 'No se pudo crear el slide demo #%d', $i ) );
            }
        }
    }

    /**
     * Comprueba si un flag de características está activo.
     */
    protected function is_feature_enabled( string $flag, bool $default = true ): bool {
        $options = wp_parse_args(
            get_option( 'anima_engine_options', [] ),
            [ $flag => $default ]
        );

        if ( array_key_exists( $flag, $options ) ) {
            return (bool) $options[ $flag ];
        }

        return $default;
    }
}
