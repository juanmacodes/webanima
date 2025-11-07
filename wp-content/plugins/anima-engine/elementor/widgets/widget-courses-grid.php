<?php
namespace Anima\Engine\Elementor\Widgets;

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Typography;
use Elementor\Widget_Base;
use WP_Query;

use function __;
use function anima_get_course_meta;
use function anima_get_trimmed_excerpt;
use function anima_normalize_terms;
use function esc_html;
use function esc_html__;
use function esc_url;
use function get_the_ID;
use function get_the_permalink;
use function get_the_title;
use function has_post_thumbnail;
use function the_post_thumbnail;
use function get_terms;
use function is_wp_error;
use function wp_get_post_terms;
use function wp_reset_postdata;

class Widget_Courses_Grid extends Widget_Base {
    public function get_name(): string {
        return 'anima-courses-grid';
    }

    public function get_title(): string {
        return __( 'Cursos — Grid', 'anima-engine' );
    }

    public function get_icon(): string {
        return 'eicon-library-opened';
    }

    public function get_categories(): array {
        return [ 'anima' ];
    }

    public function get_keywords(): array {
        return [ 'cursos', 'academy', 'grid', 'cards' ];
    }

    protected function register_controls(): void {
        $this->register_query_controls();
        $this->register_content_controls();
        $this->register_layout_controls();
        $this->register_style_controls();
    }

    protected function register_query_controls(): void {
        $this->start_controls_section(
            'section_query',
            [ 'label' => __( 'Consulta', 'anima-engine' ) ]
        );

        foreach ( [ 'nivel' => __( 'Nivel', 'anima-engine' ), 'modalidad' => __( 'Modalidad', 'anima-engine' ), 'tecnologia' => __( 'Tecnología', 'anima-engine' ) ] as $taxonomy => $label ) {
            $this->add_control(
                $taxonomy . '_terms',
                [
                    'label'       => $label,
                    'type'        => Controls_Manager::SELECT2,
                    'multiple'    => true,
                    'label_block' => true,
                    'options'     => $this->get_taxonomy_options( $taxonomy ),
                ]
            );
        }

        $this->add_control(
            'posts_per_page',
            [
                'label'   => __( 'Cursos por página', 'anima-engine' ),
                'type'    => Controls_Manager::NUMBER,
                'min'     => 1,
                'max'     => 24,
                'default' => 6,
            ]
        );

        $this->add_control(
            'orderby',
            [
                'label'   => __( 'Ordenar por', 'anima-engine' ),
                'type'    => Controls_Manager::SELECT,
                'default' => 'date',
                'options' => [
                    'date'       => __( 'Fecha de publicación', 'anima-engine' ),
                    'title'      => __( 'Título', 'anima-engine' ),
                    'menu_order' => __( 'Orden del menú', 'anima-engine' ),
                    'rand'       => __( 'Aleatorio', 'anima-engine' ),
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

        $this->end_controls_section();
    }

    protected function register_content_controls(): void {
        $this->start_controls_section(
            'section_content',
            [ 'label' => __( 'Contenido', 'anima-engine' ) ]
        );

        $this->add_control(
            'show_excerpt',
            [
                'label'        => __( 'Mostrar extracto', 'anima-engine' ),
                'type'         => Controls_Manager::SWITCHER,
                'default'      => 'yes',
                'return_value' => 'yes',
            ]
        );

        $this->add_control(
            'excerpt_length',
            [
                'label'     => __( 'Largo del extracto (palabras)', 'anima-engine' ),
                'type'      => Controls_Manager::NUMBER,
                'min'       => 5,
                'max'       => 60,
                'default'   => 24,
                'condition' => [ 'show_excerpt' => 'yes' ],
            ]
        );

        $this->add_control(
            'button_text',
            [
                'label'   => __( 'Texto del botón', 'anima-engine' ),
                'type'    => Controls_Manager::TEXT,
                'default' => __( 'Ver curso', 'anima-engine' ),
            ]
        );

        $this->end_controls_section();
    }

    protected function register_layout_controls(): void {
        $this->start_controls_section(
            'section_layout',
            [ 'label' => __( 'Diseño', 'anima-engine' ) ]
        );

        $this->add_responsive_control(
            'columns',
            [
                'label'          => __( 'Columnas', 'anima-engine' ),
                'type'           => Controls_Manager::NUMBER,
                'min'            => 1,
                'max'            => 4,
                'desktop_default'=> 3,
                'tablet_default' => 2,
                'mobile_default' => 1,
            ]
        );

        $this->add_responsive_control(
            'gap',
            [
                'label'     => __( 'Espaciado', 'anima-engine' ),
                'type'      => Controls_Manager::SLIDER,
                'range'     => [ 'px' => [ 'min' => 8, 'max' => 60 ] ],
                'default'   => [ 'size' => 28 ],
                'selectors' => [ '{{WRAPPER}} .an-grid' => 'gap: {{SIZE}}{{UNIT}};' ],
            ]
        );

        $this->end_controls_section();
    }

    protected function register_style_controls(): void {
        $this->start_controls_section(
            'section_style',
            [ 'label' => __( 'Estilos', 'anima-engine' ), 'tab' => Controls_Manager::TAB_STYLE ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [ 'name' => 'title_typography', 'selector' => '{{WRAPPER}} .an-card__title' ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [ 'name' => 'card_shadow', 'selector' => '{{WRAPPER}} .an-card' ]
        );

        $this->end_controls_section();
    }

    protected function render(): void {
        $settings = $this->get_settings_for_display();

        $args = [
            'post_type'      => 'curso',
            'post_status'    => 'publish',
            'posts_per_page' => $settings['posts_per_page'] ?? 6,
            'orderby'        => $settings['orderby'] ?? 'date',
            'order'          => $settings['order'] ?? 'DESC',
        ];

        $tax_query = [];
        foreach ( [ 'nivel', 'modalidad', 'tecnologia' ] as $taxonomy ) {
            if ( empty( $settings[ $taxonomy . '_terms' ] ) ) {
                continue;
            }

            $tax_query[] = [
                'taxonomy' => $taxonomy,
                'field'    => 'term_id',
                'terms'    => array_map( 'absint', (array) $settings[ $taxonomy . '_terms' ] ),
            ];
        }

        if ( ! empty( $tax_query ) ) {
            $args['tax_query'] = $tax_query;
        }

        $query = new WP_Query( $args );

        if ( ! $query->have_posts() ) {
            echo '<div class="an-empty" role="status">' . esc_html__( 'No hay cursos disponibles.', 'anima-engine' ) . '</div>';
            return;
        }

        $columns_desktop = (int) ( $settings['columns'] ?? 3 );
        $columns_tablet  = (int) ( $settings['columns_tablet'] ?? 2 );
        $columns_mobile  = (int) ( $settings['columns_mobile'] ?? 1 );

        $this->add_render_attribute(
            'grid',
            [
                'class'            => 'an-grid',
                'data-cols-desktop'=> (string) max( 1, $columns_desktop ),
                'data-cols-tablet' => (string) max( 1, $columns_tablet ),
                'data-cols-mobile' => (string) max( 1, $columns_mobile ),
            ]
        );

        echo '<div ' . $this->get_render_attribute_string( 'grid' ) . '>';

        while ( $query->have_posts() ) {
            $query->the_post();
            $meta = anima_get_course_meta( get_the_ID() );

            echo '<article class="an-card" data-anima-reveal>';
            if ( has_post_thumbnail() ) {
                echo '<figure class="an-card__media">';
                the_post_thumbnail( 'an_card_16x10', [ 'alt' => get_the_title(), 'loading' => 'lazy' ] );
                echo '</figure>';
            }
            echo '<div class="an-card__body">';
            echo '<div class="an-card__chips">';
            foreach ( [ 'nivel', 'modalidad' ] as $taxonomy ) {
                $terms = anima_normalize_terms( wp_get_post_terms( get_the_ID(), $taxonomy ) );
                foreach ( $terms as $term ) {
                    echo '<span class="an-chip">' . esc_html( $term['name'] ) . '</span>';
                }
            }
            echo '</div>';
            echo '<h3 class="an-card__title"><a href="' . esc_url( get_the_permalink() ) . '">' . esc_html( get_the_title() ) . '</a></h3>';
            $meta_line = implode( ' · ', array_filter( [ $meta['price'], $meta['hours'] ] ) );
            if ( $meta_line ) {
                echo '<p class="an-card__meta">' . esc_html( $meta_line ) . '</p>';
            }
            if ( 'yes' === ( $settings['show_excerpt'] ?? '' ) ) {
                $words = (int) ( $settings['excerpt_length'] ?? 24 );
                echo '<p class="an-card__excerpt">' . esc_html( anima_get_trimmed_excerpt( get_the_ID(), max( 5, $words ) ) ) . '</p>';
            }
            echo '<p class="an-card__cta"><a class="an-button" href="' . esc_url( get_the_permalink() ) . '">' . esc_html( $settings['button_text'] ?? __( 'Ver curso', 'anima-engine' ) ) . '</a></p>';
            echo '</div>';
            echo '</article>';
        }

        echo '</div>';

        wp_reset_postdata();
    }

    private function get_taxonomy_options( string $taxonomy ): array {
        $terms = get_terms(
            [ 'taxonomy' => $taxonomy, 'hide_empty' => false ]
        );

        if ( empty( $terms ) || is_wp_error( $terms ) ) {
            return [];
        }

        $options = [];
        foreach ( $terms as $term ) {
            $options[ $term->term_id ] = $term->name;
        }

        return $options;
    }
}
