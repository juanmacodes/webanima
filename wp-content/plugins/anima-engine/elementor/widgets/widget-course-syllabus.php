<?php
namespace Anima\Engine\Elementor\Widgets;

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;
use Elementor\Widget_Base;

use function __;
use function anima_get_course_meta;
use function esc_attr;
use function esc_html;
use function esc_html__;
use function get_the_ID;

class Widget_Course_Syllabus extends Widget_Base {
    public function get_name(): string {
        return 'anima-course-syllabus';
    }

    public function get_title(): string {
        return __( 'Curso — Temario', 'anima-engine' );
    }

    public function get_icon(): string {
        return 'eicon-toggle';
    }

    public function get_categories(): array {
        return [ 'anima' ];
    }

    public function get_keywords(): array {
        return [ 'curso', 'temario', 'syllabus', 'acordeon' ];
    }

    protected function register_controls(): void {
        $this->start_controls_section(
            'section_content',
            [ 'label' => __( 'Contenido', 'anima-engine' ) ]
        );

        $this->add_control(
            'show_numbers',
            [
                'label'        => __( 'Mostrar numeración', 'anima-engine' ),
                'type'         => Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default'      => 'yes',
            ]
        );

        $this->end_controls_section();
    }

    protected function render(): void {
        $post_id = get_the_ID();
        if ( ! $post_id ) {
            return;
        }

        $meta = anima_get_course_meta( $post_id );
        $modules = $meta['syllabus'];

        if ( empty( $modules ) ) {
            echo '<div class="an-empty" role="status">' . esc_html__( 'El temario estará disponible próximamente.', 'anima-engine' ) . '</div>';
            return;
        }

        $show_numbers = 'yes' === $this->get_settings( 'show_numbers' );

        echo '<div class="an-course-syllabus" role="list">';
        foreach ( $modules as $index => $module ) {
            $title   = $module['title'] ?? sprintf( __( 'Módulo %d', 'anima-engine' ), $index + 1 );
            $lessons = $module['lessons'] ?? [];
            $panel_id = 'an-course-syllabus-' . $post_id . '-' . $index;

            echo '<div class="an-course-syllabus__item" role="listitem">';
            echo '<button class="an-course-syllabus__button" type="button" aria-expanded="false" aria-controls="' . esc_attr( $panel_id ) . '">';
            if ( $show_numbers ) {
                echo '<span class="an-chip">' . esc_html( sprintf( '%02d', $index + 1 ) ) . '</span>';
            }
            echo '<span>' . esc_html( $title ) . '</span>';
            echo '<span aria-hidden="true">+</span>';
            echo '</button>';

            echo '<div id="' . esc_attr( $panel_id ) . '" class="an-course-syllabus__panel" hidden>'; 
            if ( ! empty( $lessons ) ) {
                echo '<ol class="an-course-syllabus__lessons">';
                foreach ( $lessons as $lesson ) {
                    echo '<li>' . esc_html( $lesson ) . '</li>';
                }
                echo '</ol>';
            }
            echo '</div>';
            echo '</div>';
        }
        echo '</div>';
    }
}
