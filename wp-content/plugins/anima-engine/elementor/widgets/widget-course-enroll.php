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
use function esc_url;
use function get_the_ID;
use function wp_create_nonce;

class Widget_Course_Enroll extends Widget_Base {
    public function get_name(): string {
        return 'anima-course-enroll';
    }

    public function get_title(): string {
        return __( 'Curso — Inscripción', 'anima-engine' );
    }

    public function get_icon(): string {
        return 'eicon-form-horizontal';
    }

    public function get_categories(): array {
        return [ 'anima' ];
    }

    public function get_keywords(): array {
        return [ 'curso', 'formulario', 'inscripción' ];
    }

    protected function register_controls(): void {
        $this->start_controls_section(
            'section_content',
            [ 'label' => __( 'Contenido', 'anima-engine' ) ]
        );

        $this->add_control(
            'title',
            [
                'label'   => __( 'Título', 'anima-engine' ),
                'type'    => Controls_Manager::TEXT,
                'default' => __( 'Reserva tu plaza', 'anima-engine' ),
            ]
        );

        $this->add_control(
            'description',
            [
                'label'   => __( 'Descripción', 'anima-engine' ),
                'type'    => Controls_Manager::TEXTAREA,
                'default' => __( 'Déjanos tus datos y te contactaremos con los siguientes pasos.', 'anima-engine' ),
            ]
        );

        $this->add_control(
            'privacy_label',
            [
                'label'   => __( 'Texto privacidad', 'anima-engine' ),
                'type'    => Controls_Manager::TEXT,
                'default' => __( 'Acepto la política de privacidad.', 'anima-engine' ),
            ]
        );

        $this->end_controls_section();
    }

    protected function render(): void {
        $post_id = get_the_ID();
        if ( ! $post_id ) {
            return;
        }

        $meta    = anima_get_course_meta( $post_id );
        $target  = $meta['target'] ?? 'waitlist';
        $enroll_url = $meta['enroll_url'] ?? '';

        $form_attributes = [
            'class'       => 'an-course-enroll__form',
            'data-course' => (string) $post_id,
            'data-target' => $target,
        ];

        if ( 'url' === $target && $enroll_url ) {
            $form_attributes['data-url'] = $enroll_url;
        }

        echo '<section class="an-course-enroll" id="anima-course-enroll">';
        if ( $this->get_settings( 'title' ) ) {
            echo '<h2>' . esc_html( $this->get_settings( 'title' ) ) . '</h2>';
        }
        if ( $this->get_settings( 'description' ) ) {
            echo '<p>' . esc_html( $this->get_settings( 'description' ) ) . '</p>';
        }

        echo '<form method="post"';
        foreach ( $form_attributes as $attr => $value ) {
            echo sprintf( ' %s="%s"', esc_attr( $attr ), esc_attr( $value ) );
        }
        echo ' novalidate>';

        echo '<div class="an-course-enroll__field">';
        echo '<label class="an-course-enroll__label" for="anima-name">' . esc_html__( 'Nombre', 'anima-engine' ) . '</label>';
        echo '<input class="an-course-enroll__input" type="text" id="anima-name" name="anima_name" required autocomplete="name" />';
        echo '</div>';

        echo '<div class="an-course-enroll__field">';
        echo '<label class="an-course-enroll__label" for="anima-email">' . esc_html__( 'Email', 'anima-engine' ) . '</label>';
        echo '<input class="an-course-enroll__input" type="email" id="anima-email" name="anima_email" required autocomplete="email" />';
        echo '</div>';

        echo '<div class="an-course-enroll__field">';
        echo '<label class="an-course-enroll__label" for="anima-country">' . esc_html__( 'País', 'anima-engine' ) . '</label>';
        echo '<input class="an-course-enroll__input" type="text" id="anima-country" name="anima_country" autocomplete="country-name" />';
        echo '</div>';

        echo '<div class="an-course-enroll__field">';
        echo '<label class="an-course-enroll__label">';
        echo '<input type="checkbox" name="anima_consent" value="1" required /> ' . esc_html( $this->get_settings( 'privacy_label' ) );
        echo '</label>';
        echo '</div>';

        echo '<div class="an-course-enroll__field" aria-hidden="true" style="position:absolute;left:-999em;">';
        echo '<label for="anima-hp">' . esc_html__( 'Deja este campo vacío', 'anima-engine' ) . '</label>';
        echo '<input type="text" id="anima-hp" name="anima_hp" tabindex="-1" autocomplete="off" />';
        echo '</div>';

        $nonce = wp_create_nonce( 'wp_rest' );
        echo '<input type="hidden" name="anima_waitlist_nonce" value="' . esc_attr( $nonce ) . '" />';

        echo '<div class="an-course-enroll__messages" aria-live="polite"></div>';

        echo '<button type="submit" class="an-course-enroll__submit">' . esc_html__( 'Enviar', 'anima-engine' ) . '</button>';

        echo '</form>';
        echo '</section>';
    }
}
