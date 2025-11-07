<?php
namespace Anima\Engine\Elementor\Widgets;

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Widget_Base;
use WP_Query;

use function __;
use function anima_get_trimmed_excerpt;
use function esc_html;
use function esc_html__;
use function esc_url;
use function get_the_date;
use function get_the_ID;
use function get_the_permalink;
use function get_the_title;
use function has_post_thumbnail;
use function the_post_thumbnail;
use function wp_reset_postdata;
use function sanitize_text_field;

class Widget_Posts_Grid extends Widget_Base {
    public function get_name(): string {
        return 'anima-posts-grid';
    }

    public function get_title(): string {
        return __( 'Actualidad — Entradas', 'anima-engine' );
    }

    public function get_icon(): string {
        return 'eicon-posts-grid';
    }

    public function get_categories(): array {
        return [ 'anima' ];
    }

    public function get_keywords(): array {
        return [ 'blog', 'posts', 'grid', 'actualidad' ];
    }

    protected function register_controls(): void {
        $this->start_controls_section(
            'section_query',
            [ 'label' => __( 'Consulta', 'anima-engine' ) ]
        );

        $this->add_control(
            'categories',
            [
                'label'       => __( 'Categorías', 'anima-engine' ),
                'type'        => Controls_Manager::TEXT,
                'description' => __( 'Introduce slugs separados por coma.', 'anima-engine' ),
            ]
        );

        $this->add_control(
            'posts_per_page',
            [
                'label'   => __( 'Entradas a mostrar', 'anima-engine' ),
                'type'    => Controls_Manager::NUMBER,
                'min'     => 1,
                'max'     => 12,
                'default' => 6,
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'section_style',
            [ 'label' => __( 'Estilos', 'anima-engine' ), 'tab' => Controls_Manager::TAB_STYLE ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [ 'name' => 'title_typography', 'selector' => '{{WRAPPER}} .an-post-card__title' ]
        );

        $this->end_controls_section();
    }

    protected function render(): void {
        $settings = $this->get_settings_for_display();

        $args = [
            'post_type'      => 'post',
            'post_status'    => 'publish',
            'posts_per_page' => $settings['posts_per_page'] ?? 6,
        ];

        if ( ! empty( $settings['categories'] ) ) {
            $args['category_name'] = sanitize_text_field( $settings['categories'] );
        }

        $query = new WP_Query( $args );

        if ( ! $query->have_posts() ) {
            echo '<div class="an-empty" role="status">' . esc_html__( 'Sin entradas por ahora.', 'anima-engine' ) . '</div>';
            return;
        }

        $this->add_render_attribute(
            'grid',
            [
                'class'            => 'an-grid',
                'data-cols-desktop'=> '3',
                'data-cols-tablet' => '2',
                'data-cols-mobile' => '1',
            ]
        );

        echo '<div ' . $this->get_render_attribute_string( 'grid' ) . '>';

        while ( $query->have_posts() ) {
            $query->the_post();
            echo '<article class="an-post-card" data-anima-reveal>';
            if ( has_post_thumbnail() ) {
                echo '<figure class="an-post-card__media">';
                the_post_thumbnail( 'an_card_16x10', [ 'alt' => get_the_title(), 'loading' => 'lazy' ] );
                echo '</figure>';
            }
            echo '<div class="an-post-card__body">';
            echo '<h3 class="an-post-card__title"><a href="' . esc_url( get_the_permalink() ) . '">' . esc_html( get_the_title() ) . '</a></h3>';
            echo '<p class="an-post-card__meta">' . esc_html( get_the_date() ) . '</p>';
            echo '<p class="an-card__excerpt">' . esc_html( anima_get_trimmed_excerpt( get_the_ID(), 26 ) ) . '</p>';
            echo '<p class="an-post-card__cta"><a href="' . esc_url( get_the_permalink() ) . '">' . esc_html__( 'Leer', 'anima-engine' ) . '</a></p>';
            echo '</div>';
            echo '</article>';
        }

        echo '</div>';

        wp_reset_postdata();
    }
}
