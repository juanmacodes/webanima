<?php
namespace Anima\Engine\Elementor;

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;
use Elementor\Widget_Base;

use function __;
use function absint;
use function esc_html;
use function esc_html__;
use function get_post_meta;
use function get_post_type;
use function get_the_ID;
use function is_array;
use function json_decode;
use function sprintf;

/**
 * Acordeón del temario de un curso.
 */
class Widget_Curso_Syllabus extends Widget_Base {
    public function get_name(): string {
        return 'anima-curso-syllabus';
    }

    public function get_title(): string {
        return __( 'Temario de curso', 'anima-engine' );
    }

    public function get_icon(): string {
        return 'eicon-accordion';
    }

    public function get_categories(): array {
        return [ 'anima' ];
    }

    public function get_keywords(): array {
        return [ 'curso', 'temario', 'acordeon', 'anima' ];
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
            'expand_first',
            [
                'label'   => __( 'Expandir primer módulo', 'anima-engine' ),
                'type'    => Controls_Manager::SWITCHER,
                'default' => 'yes',
            ]
        );

        $this->end_controls_section();
    }

    protected function render(): void {
        $settings = $this->get_settings_for_display();
        $post_id  = ! empty( $settings['post_id'] ) ? absint( $settings['post_id'] ) : get_the_ID();

        if ( ! $post_id || 'curso' !== get_post_type( $post_id ) ) {
            echo '<div class="anima-curso-syllabus anima-curso-syllabus--empty">' . esc_html__( 'Selecciona un curso válido.', 'anima-engine' ) . '</div>';
            return;
        }

        $syllabus = get_post_meta( $post_id, 'anima_syllabus', true );
        if ( ! is_array( $syllabus ) ) {
            $decoded = json_decode( (string) $syllabus, true );
            $syllabus = is_array( $decoded ) ? $decoded : [];
        }

        if ( empty( $syllabus ) ) {
            echo '<div class="anima-curso-syllabus anima-curso-syllabus--empty">' . esc_html__( 'No hay temario cargado.', 'anima-engine' ) . '</div>';
            return;
        }

        $index = 0;
        echo '<div class="anima-curso-syllabus" role="tablist">';
        foreach ( $syllabus as $module ) {
            if ( ! is_array( $module ) ) {
                continue;
            }

            $title   = isset( $module['title'] ) ? esc_html( (string) $module['title'] ) : sprintf( __( 'Módulo %d', 'anima-engine' ), $index + 1 );
            $lessons = isset( $module['lessons'] ) && is_array( $module['lessons'] ) ? $module['lessons'] : [];
            $open    = ( 0 === $index && 'yes' === ( $settings['expand_first'] ?? '' ) ) ? ' open' : '';

            echo '<details class="anima-curso-syllabus__module"' . $open . '>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            echo '<summary class="anima-curso-syllabus__title">' . $title . '</summary>';

            if ( ! empty( $lessons ) ) {
                echo '<ul class="anima-curso-syllabus__lessons">';
                foreach ( $lessons as $lesson ) {
                    echo '<li class="anima-curso-syllabus__lesson">' . esc_html( (string) $lesson ) . '</li>';
                }
                echo '</ul>';
            }

            echo '</details>';
            $index++;
        }
        echo '</div>';
    }
}
