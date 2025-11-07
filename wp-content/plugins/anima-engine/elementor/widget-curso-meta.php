<?php
namespace Anima\Engine\Elementor;

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;
use Elementor\Widget_Base;

use function __;
use function absint;
use function esc_attr;
use function esc_html;
use function esc_html__;
use function get_post_meta;
use function get_post_type;
use function get_the_ID;
use function get_the_terms;
use function is_wp_error;
use function number_format_i18n;

/**
 * Muestra chips con metadatos de un curso.
 */
class Widget_Curso_Meta extends Widget_Base {
    public function get_name(): string {
        return 'anima-curso-meta';
    }

    public function get_title(): string {
        return __( 'Meta de curso', 'anima-engine' );
    }

    public function get_icon(): string {
        return 'eicon-meta-data';
    }

    public function get_categories(): array {
        return [ 'anima' ];
    }

    public function get_keywords(): array {
        return [ 'curso', 'meta', 'chips', 'anima' ];
    }

    protected function register_controls(): void {
        $this->start_controls_section(
            'section_content',
            [
                'label' => __( 'Contenido', 'anima-engine' ),
            ]
        );

        $this->add_control(
            'post_id',
            [
                'label'       => __( 'ID del curso', 'anima-engine' ),
                'type'        => Controls_Manager::NUMBER,
                'description' => __( 'Déjalo vacío para usar el curso actual.', 'anima-engine' ),
            ]
        );

        $this->add_control(
            'show_price',
            [
                'label'   => __( 'Mostrar precio', 'anima-engine' ),
                'type'    => Controls_Manager::SWITCHER,
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'show_hours',
            [
                'label'   => __( 'Mostrar horas', 'anima-engine' ),
                'type'    => Controls_Manager::SWITCHER,
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'show_nivel',
            [
                'label'   => __( 'Mostrar niveles', 'anima-engine' ),
                'type'    => Controls_Manager::SWITCHER,
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'show_modalidad',
            [
                'label'   => __( 'Mostrar modalidades', 'anima-engine' ),
                'type'    => Controls_Manager::SWITCHER,
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'show_tecnologia',
            [
                'label'   => __( 'Mostrar tecnologías', 'anima-engine' ),
                'type'    => Controls_Manager::SWITCHER,
                'default' => 'yes',
            ]
        );

        $this->end_controls_section();
    }

    protected function render(): void {
        $settings = $this->get_settings_for_display();

        $post_id = ! empty( $settings['post_id'] ) ? absint( $settings['post_id'] ) : get_the_ID();

        if ( ! $post_id || 'curso' !== get_post_type( $post_id ) ) {
            echo '<div class="anima-curso-meta anima-curso-meta--empty">' . esc_html__( 'Selecciona un curso válido.', 'anima-engine' ) . '</div>';
            return;
        }

        $chips = [];

        if ( 'yes' === ( $settings['show_price'] ?? '' ) ) {
            $price = get_post_meta( $post_id, 'anima_price', true );
            if ( '' !== $price && null !== $price ) {
                $chips[] = '<span class="anima-course-chip anima-course-chip--price">€' . esc_html( number_format_i18n( (float) $price, 2 ) ) . '</span>';
            }
        }

        if ( 'yes' === ( $settings['show_hours'] ?? '' ) ) {
            $hours = get_post_meta( $post_id, 'anima_duration_hours', true );
            if ( '' !== $hours && null !== $hours ) {
                $chips[] = '<span class="anima-course-chip anima-course-chip--hours">' . esc_html( absint( $hours ) ) . 'h</span>';
            }
        }

        $taxonomy_settings = [
            'nivel'      => $settings['show_nivel'] ?? '',
            'modalidad'  => $settings['show_modalidad'] ?? '',
            'tecnologia' => $settings['show_tecnologia'] ?? '',
        ];

        foreach ( $taxonomy_settings as $taxonomy => $enabled ) {
            if ( 'yes' !== $enabled ) {
                continue;
            }

            $terms = get_the_terms( $post_id, $taxonomy );
            if ( empty( $terms ) || is_wp_error( $terms ) ) {
                continue;
            }

            foreach ( $terms as $term ) {
                $chips[] = '<span class="anima-course-chip anima-course-chip--taxonomy anima-course-chip--' . esc_attr( $taxonomy ) . '">' . esc_html( $term->name ) . '</span>';
            }
        }

        if ( empty( $chips ) ) {
            echo '<div class="anima-curso-meta anima-curso-meta--empty">' . esc_html__( 'No hay metadatos disponibles.', 'anima-engine' ) . '</div>';
            return;
        }

        echo '<div class="anima-curso-meta">' . implode( '', $chips ) . '</div>';
    }
}
