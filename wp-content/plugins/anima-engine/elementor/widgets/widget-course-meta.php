<?php
namespace Anima\Engine\Elementor\Widgets;

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;
use Elementor\Widget_Base;

use function __;
use function anima_get_course_meta;
use function anima_normalize_terms;
use function esc_html;
use function esc_html__;
use function esc_url;
use function get_the_ID;
use function wp_get_post_terms;
use function wp_list_pluck;
use function date_i18n;
use function get_option;
use function wp_kses_post;

class Widget_Course_Meta extends Widget_Base {
    public function get_name(): string {
        return 'anima-course-meta';
    }

    public function get_title(): string {
        return __( 'Curso — Meta', 'anima-engine' );
    }

    public function get_icon(): string {
        return 'eicon-table';
    }

    public function get_categories(): array {
        return [ 'anima' ];
    }

    public function get_keywords(): array {
        return [ 'curso', 'meta', 'sidebar' ];
    }

    protected function register_controls(): void {
        $this->start_controls_section(
            'section_content',
            [ 'label' => __( 'Contenido', 'anima-engine' ) ]
        );

        $this->add_control(
            'button_text',
            [
                'label'   => __( 'Texto botón', 'anima-engine' ),
                'type'    => Controls_Manager::TEXT,
                'default' => __( 'Inscribirme', 'anima-engine' ),
            ]
        );

        $this->add_control(
            'button_anchor',
            [
                'label'       => __( 'Ancla', 'anima-engine' ),
                'type'        => Controls_Manager::TEXT,
                'default'     => '#anima-course-enroll',
                'description' => __( 'Destino del botón de inscripción.', 'anima-engine' ),
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

        echo '<aside class="an-course-meta">';
        echo '<ul class="an-course-meta__list">';

        if ( $meta['price'] ) {
            echo '<li class="an-course-meta__list-item"><span>' . esc_html__( 'Precio', 'anima-engine' ) . '</span><strong>' . esc_html( $meta['price'] ) . '</strong></li>';
        }

        if ( $meta['hours'] ) {
            echo '<li class="an-course-meta__list-item"><span>' . esc_html__( 'Duración', 'anima-engine' ) . '</span><strong>' . esc_html( $meta['hours'] ) . '</strong></li>';
        }

        $level_terms = anima_normalize_terms( wp_get_post_terms( $post_id, 'nivel' ) );
        if ( ! empty( $level_terms ) ) {
            echo '<li class="an-course-meta__list-item"><span>' . esc_html__( 'Nivel', 'anima-engine' ) . '</span><strong>' . esc_html( $level_terms[0]['name'] ) . '</strong></li>';
        }

        $modalidad = anima_normalize_terms( wp_get_post_terms( $post_id, 'modalidad' ) );
        if ( ! empty( $modalidad ) ) {
            $names = wp_list_pluck( $modalidad, 'name' );
            echo '<li class="an-course-meta__list-item"><span>' . esc_html__( 'Modalidad', 'anima-engine' ) . '</span><strong>' . esc_html( implode( ', ', $names ) ) . '</strong></li>';
        }

        $tech = anima_normalize_terms( wp_get_post_terms( $post_id, 'tecnologia' ) );
        if ( ! empty( $tech ) ) {
            $names = wp_list_pluck( $tech, 'name' );
            echo '<li class="an-course-meta__list-item"><span>' . esc_html__( 'Tecnologías', 'anima-engine' ) . '</span><strong>' . esc_html( implode( ', ', $names ) ) . '</strong></li>';
        }

        if ( ! empty( $meta['dates'] ) ) {
            echo '<li class="an-course-meta__list-item"><span>' . esc_html__( 'Próximas fechas', 'anima-engine' ) . '</span><div class="an-course-meta__dates">';
            foreach ( $meta['dates'] as $date ) {
                echo '<span>' . esc_html( date_i18n( get_option( 'date_format' ), strtotime( $date ) ) ) . '</span>';
            }
            echo '</div></li>';
        }

        echo '</ul>';

        if ( $meta['requirements'] ) {
            echo '<div class="an-course-meta__requirements">';
            echo '<h3 class="screen-reader-text">' . esc_html__( 'Requisitos', 'anima-engine' ) . '</h3>';
            echo wp_kses_post( $meta['requirements'] );
            echo '</div>';
        }

        $button_text = $this->get_settings( 'button_text' ) ?: __( 'Inscribirme', 'anima-engine' );
        $anchor      = $this->get_settings( 'button_anchor' ) ?: '#anima-course-enroll';

        echo '<a class="an-button" href="' . esc_url( $anchor ) . '">' . esc_html( $button_text ) . '</a>';
        echo '</aside>';
    }
}
