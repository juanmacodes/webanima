<?php
namespace Anima\Engine\Elementor\Widgets;

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;
use Elementor\Widget_Base;

use function __;
use function anima_get_course_meta;
use function anima_normalize_terms;
use function esc_attr;
use function esc_html;
use function get_the_post_thumbnail_url;
use function get_the_ID;
use function get_the_title;
use function wp_get_post_terms;

class Widget_Course_Hero extends Widget_Base {
    public function get_name(): string {
        return 'anima-course-hero';
    }

    public function get_title(): string {
        return __( 'Curso — Hero', 'anima-engine' );
    }

    public function get_icon(): string {
        return 'eicon-slider-device';
    }

    public function get_categories(): array {
        return [ 'anima' ];
    }

    public function get_keywords(): array {
        return [ 'curso', 'hero', 'encabezado' ];
    }

    protected function register_controls(): void {
        $this->start_controls_section(
            'section_content',
            [ 'label' => __( 'Contenido', 'anima-engine' ) ]
        );

        $this->add_control(
            'show_price',
            [
                'label'        => __( 'Mostrar precio', 'anima-engine' ),
                'type'         => Controls_Manager::SWITCHER,
                'default'      => 'yes',
                'return_value' => 'yes',
            ]
        );

        $this->add_control(
            'show_hours',
            [
                'label'        => __( 'Mostrar horas', 'anima-engine' ),
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

        $meta      = anima_get_course_meta( $post_id );
        $image_url = get_the_post_thumbnail_url( $post_id, 'full' );

        $attributes = [ 'class' => 'an-course-hero' ];
        if ( $image_url ) {
            $attributes['style'] = '--an-course-hero-image: url(' . $image_url . ');';
        }

        echo '<section';
        foreach ( $attributes as $attr => $value ) {
            echo sprintf( ' %s="%s"', esc_attr( $attr ), esc_attr( $value ) );
        }
        echo '>';

        echo '<div class="an-course-hero__meta">';
        $level_terms = anima_normalize_terms( wp_get_post_terms( $post_id, 'nivel' ) );
        foreach ( $level_terms as $term ) {
            echo '<span class="an-badge">' . esc_html( $term['name'] ) . '</span>';
        }
        echo '</div>';

        echo '<h1 class="an-course-hero__title">' . esc_html( get_the_title() ) . '</h1>';

        $chips = [];
        foreach ( [ 'modalidad', 'tecnologia' ] as $taxonomy ) {
            $terms = anima_normalize_terms( wp_get_post_terms( $post_id, $taxonomy ) );
            foreach ( $terms as $term ) {
                $chips[] = $term['name'];
            }
        }

        if ( ! empty( $chips ) ) {
            echo '<div class="an-course-hero__chips">';
            foreach ( $chips as $chip ) {
                echo '<span class="an-chip">' . esc_html( $chip ) . '</span>';
            }
            echo '</div>';
        }

        $badges = [];
        if ( 'yes' === $this->get_settings( 'show_price' ) && $meta['price'] ) {
            $badges[] = '<span class="an-badge an-badge--success">' . esc_html( $meta['price'] ) . '</span>';
        }
        if ( 'yes' === $this->get_settings( 'show_hours' ) && $meta['hours'] ) {
            $badges[] = '<span class="an-chip an-chip--hours">' . esc_html( $meta['hours'] ) . '</span>';
        }

        if ( ! empty( $badges ) ) {
            echo '<div class="an-course-hero__badges">' . implode( '', $badges ) . '</div>';
        }

        echo '</section>';
    }
}
