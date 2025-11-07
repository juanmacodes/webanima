<?php
namespace Anima\Engine\Elementor;

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Widget_Base;
use WP_Post;

use function __;
use function absint;
use function anima_collect_tax_badges;
use function anima_get_course_meta;
use function anima_get_trimmed_excerpt;
use function esc_attr;
use function esc_attr__;
use function esc_html;
use function esc_html__;
use function esc_url;
use function get_permalink;
use function get_posts;
use function get_the_title;

/**
 * Tarjeta individual de curso para Elementor.
 */
class Widget_Curso_Card extends Widget_Base {
    public function get_name(): string {
        return 'anima-curso-card';
    }

    public function get_title(): string {
        return __( 'Curso — Card', 'anima-engine' );
    }

    public function get_icon(): string {
        return 'eicon-post';
    }

    public function get_categories(): array {
        return [ 'anima' ];
    }

    protected function register_controls(): void {
        $this->start_controls_section(
            'section_content',
            [
                'label' => __( 'Contenido', 'anima-engine' ),
            ]
        );

        $this->add_control(
            'course_id',
            [
                'label'       => __( 'Curso específico', 'anima-engine' ),
                'type'        => Controls_Manager::SELECT2,
                'options'     => $this->get_courses_options(),
                'label_block' => true,
                'multiple'    => false,
                'description' => __( 'Si se deja vacío se usará el curso actual del loop.', 'anima-engine' ),
            ]
        );

        $this->add_control(
            'show_image',
            [
                'label'        => __( 'Mostrar imagen', 'anima-engine' ),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => __( 'Sí', 'anima-engine' ),
                'label_off'    => __( 'No', 'anima-engine' ),
                'return_value' => 'yes',
                'default'      => 'yes',
            ]
        );

        $this->add_control(
            'show_excerpt',
            [
                'label'        => __( 'Mostrar resumen', 'anima-engine' ),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => __( 'Sí', 'anima-engine' ),
                'label_off'    => __( 'No', 'anima-engine' ),
                'return_value' => 'yes',
                'default'      => 'yes',
            ]
        );

        $this->add_control(
            'show_price',
            [
                'label'        => __( 'Mostrar precio', 'anima-engine' ),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => __( 'Sí', 'anima-engine' ),
                'label_off'    => __( 'No', 'anima-engine' ),
                'return_value' => 'yes',
                'default'      => 'yes',
            ]
        );

        $this->add_control(
            'show_hours',
            [
                'label'        => __( 'Mostrar horas', 'anima-engine' ),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => __( 'Sí', 'anima-engine' ),
                'label_off'    => __( 'No', 'anima-engine' ),
                'return_value' => 'yes',
                'default'      => 'yes',
            ]
        );

        $this->add_control(
            'show_badges',
            [
                'label'        => __( 'Mostrar taxonomías', 'anima-engine' ),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => __( 'Sí', 'anima-engine' ),
                'label_off'    => __( 'No', 'anima-engine' ),
                'return_value' => 'yes',
                'default'      => 'yes',
            ]
        );

        $this->add_control(
            'button_text',
            [
                'label'       => __( 'Texto botón', 'anima-engine' ),
                'type'        => Controls_Manager::TEXT,
                'default'     => __( 'Ver curso', 'anima-engine' ),
                'label_block' => true,
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'section_style',
            [
                'label' => __( 'Estilo', 'anima-engine' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'card_radius',
            [
                'label'     => __( 'Radio', 'anima-engine' ),
                'type'      => Controls_Manager::SLIDER,
                'range'     => [
                    'px' => [
                        'min' => 0,
                        'max' => 40,
                    ],
                ],
                'default'   => [ 'size' => 16 ],
                'selectors' => [
                    '{{WRAPPER}} .an-card' => '--an-card-radius: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name'     => 'card_shadow',
                'selector' => '{{WRAPPER}} .an-card',
            ]
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name'     => 'overlay_gradient',
                'label'    => __( 'Overlay', 'anima-engine' ),
                'types'    => [ 'gradient' ],
                'selector' => '{{WRAPPER}} .an-card__overlay',
            ]
        );

        $this->end_controls_section();
    }

    protected function render(): void {
        $settings = $this->get_settings_for_display();

        $post_id = $this->resolve_course_id( $settings );
        if ( ! $post_id ) {
            echo '<div class="an-card">' . esc_html__( 'Selecciona un curso para mostrar.', 'anima-engine' ) . '</div>';
            return;
        }

        $meta    = anima_get_course_meta( $post_id );
        $badges  = 'yes' === ( $settings['show_badges'] ?? 'yes' ) ? anima_collect_tax_badges( $post_id, [ 'nivel', 'modalidad', 'tecnologia' ] ) : [];
        $excerpt = 'yes' === ( $settings['show_excerpt'] ?? 'yes' ) ? anima_get_trimmed_excerpt( $post_id, 20 ) : '';

        $permalink   = get_permalink( $post_id );
        $button_text = $settings['button_text'] ?: __( 'Ver curso', 'anima-engine' );

        echo '<article class="an-card" data-anima-reveal>';

        if ( 'yes' === ( $settings['show_image'] ?? 'yes' ) ) {
            $thumbnail = get_the_post_thumbnail(
                $post_id,
                'anima_course_card',
                [
                    'class'    => 'an-card__image',
                    'loading'  => 'lazy',
                    'decoding' => 'async',
                ]
            );

            if ( $thumbnail ) {
                echo '<div class="an-card__media">' . $thumbnail;
                echo '<a class="an-card__overlay" href="' . esc_url( $permalink ) . '" aria-label="' . esc_attr( sprintf( __( 'Ver curso: %s', 'anima-engine' ), get_the_title( $post_id ) ) ) . '">';
                echo '<span class="an-card__overlay-button">' . esc_html( $button_text ) . '</span>';
                echo '</a>';
                echo '</div>';
            }
        }

        echo '<div class="an-card__body">';
        echo '<h3 class="an-card__title"><a href="' . esc_url( $permalink ) . '">' . esc_html( get_the_title( $post_id ) ) . '</a></h3>';

        if ( $excerpt ) {
            echo '<p class="an-card__excerpt">' . esc_html( $excerpt ) . '</p>';
        }

        $chips = [];
        if ( 'yes' === ( $settings['show_price'] ?? 'yes' ) && '' !== $meta['price'] ) {
            $chips[] = '<span class="an-chip an-chip--price">' . esc_html( $meta['price'] ) . '</span>';
        }
        if ( 'yes' === ( $settings['show_hours'] ?? 'yes' ) && '' !== $meta['hours'] ) {
            $chips[] = '<span class="an-chip an-chip--hours">' . esc_html( $meta['hours'] ) . '</span>';
        }

        if ( ! empty( $chips ) ) {
            echo '<div class="an-card__chips">' . implode( '', $chips ) . '</div>';
        }

        if ( ! empty( $badges ) ) {
            echo '<div class="an-card__badges">';
            foreach ( $badges as $badge ) {
                echo '<span class="an-badge" data-taxonomy="' . esc_attr( $badge['taxonomy'] ) . '" data-term="' . esc_attr( $badge['slug'] ) . '">' . esc_html( $badge['name'] ) . '</span>';
            }
            echo '</div>';
        }

        echo '</div>';
        echo '</article>';
    }

    private function resolve_course_id( array $settings ): int {
        $selected = isset( $settings['course_id'] ) ? absint( $settings['course_id'] ) : 0;
        if ( $selected ) {
            return $selected;
        }

        global $post;
        if ( $post instanceof WP_Post && 'curso' === $post->post_type ) {
            return (int) $post->ID;
        }

        $fallback = get_posts(
            [
                'post_type'      => 'curso',
                'post_status'    => 'publish',
                'posts_per_page' => 1,
                'orderby'        => 'date',
                'order'          => 'DESC',
            ]
        );

        return ! empty( $fallback ) ? (int) $fallback[0]->ID : 0;
    }

    private function get_courses_options(): array {
        $posts = get_posts(
            [
                'post_type'      => 'curso',
                'post_status'    => 'publish',
                'posts_per_page' => 50,
                'orderby'        => 'title',
                'order'          => 'ASC',
            ]
        );

        if ( empty( $posts ) ) {
            return [];
        }

        $options = [];
        foreach ( $posts as $course ) {
            $options[ $course->ID ] = $course->post_title;
        }

        return $options;
    }
}
