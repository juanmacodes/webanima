<?php
namespace Anima\Engine\Elementor;

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;
use Elementor\Widget_Base;
use WP_Query;

use function __;
use function absint;
use function esc_attr;
use function esc_html;
use function esc_html__;
use function esc_url;
use function get_permalink;
use function get_post_meta;
use function get_the_excerpt;
use function get_the_ID;
use function get_the_post_thumbnail_url;
use function get_the_title;
use function number_format_i18n;
use function sanitize_title;
use function wp_reset_postdata;
use function wp_trim_words;

/**
 * Widget Elementor para mostrar un grid de cursos.
 */
class Widget_Cursos_Grid extends Widget_Base {
    public function get_name(): string {
        return 'anima-cursos-grid';
    }

    public function get_title(): string {
        return __( 'Grid de cursos', 'anima-engine' );
    }

    public function get_icon(): string {
        return 'eicon-posts-grid';
    }

    public function get_categories(): array {
        return [ 'anima' ];
    }

    public function get_keywords(): array {
        return [ 'curso', 'grid', 'anima', 'academy' ];
    }

    protected function register_controls(): void {
        $this->start_controls_section(
            'section_query',
            [
                'label' => __( 'Consulta', 'anima-engine' ),
            ]
        );

        $this->add_control(
            'posts_per_page',
            [
                'label'   => __( 'Número de cursos', 'anima-engine' ),
                'type'    => Controls_Manager::NUMBER,
                'default' => 6,
                'min'     => 1,
                'max'     => 20,
            ]
        );

        $this->add_control(
            'orderby',
            [
                'label'   => __( 'Ordenar por', 'anima-engine' ),
                'type'    => Controls_Manager::SELECT,
                'default' => 'date',
                'options' => [
                    'date'          => __( 'Fecha', 'anima-engine' ),
                    'title'         => __( 'Título', 'anima-engine' ),
                    'menu_order'    => __( 'Orden del menú', 'anima-engine' ),
                    'modified'      => __( 'Fecha de modificación', 'anima-engine' ),
                ],
            ]
        );

        $this->add_control(
            'order',
            [
                'label'   => __( 'Dirección', 'anima-engine' ),
                'type'    => Controls_Manager::SELECT,
                'default' => 'DESC',
                'options' => [
                    'ASC'  => __( 'Ascendente', 'anima-engine' ),
                    'DESC' => __( 'Descendente', 'anima-engine' ),
                ],
            ]
        );

        $this->add_control(
            'nivel_terms',
            [
                'label'       => __( 'Niveles (slugs separados por coma)', 'anima-engine' ),
                'type'        => Controls_Manager::TEXT,
                'placeholder' => 'inicial,intermedio',
            ]
        );

        $this->add_control(
            'modalidad_terms',
            [
                'label'       => __( 'Modalidades (slugs separados por coma)', 'anima-engine' ),
                'type'        => Controls_Manager::TEXT,
            ]
        );

        $this->add_control(
            'tecnologia_terms',
            [
                'label'       => __( 'Tecnologías (slugs separados por coma)', 'anima-engine' ),
                'type'        => Controls_Manager::TEXT,
            ]
        );

        $this->add_control(
            'button_text',
            [
                'label'       => __( 'Texto del botón', 'anima-engine' ),
                'type'        => Controls_Manager::TEXT,
                'default'     => __( 'Ver curso', 'anima-engine' ),
                'dynamic'     => [ 'active' => true ],
            ]
        );

        $this->end_controls_section();
    }

    protected function render(): void {
        $settings = $this->get_settings_for_display();

        $query_args = [
            'post_type'      => 'curso',
            'post_status'    => 'publish',
            'posts_per_page' => (int) ( $settings['posts_per_page'] ?? 6 ),
            'orderby'        => $settings['orderby'] ?? 'date',
            'order'          => $settings['order'] ?? 'DESC',
        ];

        $tax_query = [];
        foreach ( [
            'nivel'      => $settings['nivel_terms'] ?? '',
            'modalidad'  => $settings['modalidad_terms'] ?? '',
            'tecnologia' => $settings['tecnologia_terms'] ?? '',
        ] as $taxonomy => $slugs ) {
            if ( empty( $slugs ) ) {
                continue;
            }

            $terms = array_filter( array_map( 'trim', explode( ',', (string) $slugs ) ) );
            if ( empty( $terms ) ) {
                continue;
            }

            $tax_query[] = [
                'taxonomy' => $taxonomy,
                'field'    => 'slug',
                'terms'    => array_map( static fn ( $term ) => sanitize_title( $term ), $terms ),
            ];
        }

        if ( ! empty( $tax_query ) ) {
            $query_args['tax_query'] = $tax_query;
        }

        $query = new WP_Query( $query_args );

        if ( ! $query->have_posts() ) {
            echo '<div class="anima-cursos-grid anima-cursos-grid--empty">' . esc_html__( 'No hay cursos disponibles en este momento.', 'anima-engine' ) . '</div>';
            return;
        }

        echo '<div class="anima-cursos-grid">';

        while ( $query->have_posts() ) {
            $query->the_post();
            $post_id   = get_the_ID();
            $permalink = get_permalink( $post_id );
            $title     = get_the_title( $post_id );
            $excerpt   = wp_trim_words( get_the_excerpt( $post_id ), 25 );
            $thumbnail = get_the_post_thumbnail_url( $post_id, 'medium' );
            $price     = get_post_meta( $post_id, 'anima_price', true );
            $hours     = get_post_meta( $post_id, 'anima_duration_hours', true );

            echo '<article class="anima-cursos-grid__item">';
            if ( $thumbnail ) {
                echo '<a class="anima-cursos-grid__thumb" href="' . esc_url( $permalink ) . '">';
                echo '<img src="' . esc_url( $thumbnail ) . '" alt="' . esc_attr( $title ) . '" loading="lazy" />';
                echo '</a>';
            }

            echo '<div class="anima-cursos-grid__content">';
            echo '<h3 class="anima-cursos-grid__title"><a href="' . esc_url( $permalink ) . '">' . esc_html( $title ) . '</a></h3>';
            if ( ! empty( $excerpt ) ) {
                echo '<p class="anima-cursos-grid__excerpt">' . esc_html( $excerpt ) . '</p>';
            }

            $chips = [];
            if ( '' !== $price && null !== $price ) {
                $chips[] = '<span class="anima-course-chip anima-course-chip--price">€' . esc_html( number_format_i18n( (float) $price, 2 ) ) . '</span>';
            }
            if ( '' !== $hours && null !== $hours ) {
                $chips[] = '<span class="anima-course-chip anima-course-chip--hours">' . esc_html( absint( $hours ) ) . 'h</span>';
            }

            if ( ! empty( $chips ) ) {
                echo '<div class="anima-cursos-grid__chips">' . implode( '', $chips ) . '</div>';
            }

            $button_text = $settings['button_text'] ?: __( 'Ver curso', 'anima-engine' );
            echo '<a class="anima-cursos-grid__button" href="' . esc_url( $permalink ) . '">' . esc_html( $button_text ) . '</a>';
            echo '</div>';
            echo '</article>';
        }

        echo '</div>';

        wp_reset_postdata();
    }
}
