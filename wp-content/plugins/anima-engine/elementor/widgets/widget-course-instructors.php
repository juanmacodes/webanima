<?php
namespace Anima\Engine\Elementor\Widgets;

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;
use Elementor\Widget_Base;

use function __;
use function anima_get_course_meta;
use function esc_html;
use function esc_html__;
use function esc_url;
use function get_the_ID;
use function wp_strip_all_tags;

class Widget_Course_Instructors extends Widget_Base {
    public function get_name(): string {
        return 'anima-course-instructors';
    }

    public function get_title(): string {
        return __( 'Curso — Instructores', 'anima-engine' );
    }

    public function get_icon(): string {
        return 'eicon-person';
    }

    public function get_categories(): array {
        return [ 'anima' ];
    }

    public function get_keywords(): array {
        return [ 'curso', 'instructores', 'equipo' ];
    }

    protected function register_controls(): void {
        $this->start_controls_section(
            'section_content',
            [ 'label' => __( 'Contenido', 'anima-engine' ) ]
        );

        $this->add_control(
            'columns',
            [
                'label'   => __( 'Columnas', 'anima-engine' ),
                'type'    => Controls_Manager::NUMBER,
                'min'     => 1,
                'max'     => 4,
                'default' => 3,
            ]
        );

        $this->add_control(
            'show_photo',
            [
                'label'        => __( 'Mostrar foto', 'anima-engine' ),
                'type'         => Controls_Manager::SWITCHER,
                'default'      => 'yes',
                'return_value' => 'yes',
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
        $instructors = $meta['instructors'];

        if ( empty( $instructors ) ) {
            echo '<div class="an-empty" role="status">' . esc_html__( 'Pronto anunciaremos a los instructores.', 'anima-engine' ) . '</div>';
            return;
        }

        $columns = max( 1, (int) $this->get_settings( 'columns' ) );
        $show_photo = 'yes' === $this->get_settings( 'show_photo' );

        echo '<section class="an-course-instructors">';
        echo '<div class="an-course-instructors__grid" style="--an-columns-desktop:' . esc_html( $columns ) . ';">';

        foreach ( $instructors as $instructor ) {
            $name  = $instructor['name'] ?? '';
            $bio   = $instructor['bio'] ?? '';
            $photo = $instructor['avatar_url'] ?? '';

            if ( ! $name ) {
                continue;
            }

            echo '<article class="an-course-instructor">';
            if ( $show_photo && $photo ) {
                echo '<figure class="an-course-instructor__avatar"><img src="' . esc_url( $photo ) . '" alt="' . esc_html( $name ) . '" loading="lazy" /></figure>';
            }
            echo '<h3 class="an-course-instructor__name">' . esc_html( $name ) . '</h3>';
            if ( $bio ) {
                echo '<p class="an-course-instructor__bio">' . esc_html( wp_strip_all_tags( $bio ) ) . '</p>';
            }
            echo '</article>';
        }

        echo '</div>';
        echo '</section>';
    }
}
