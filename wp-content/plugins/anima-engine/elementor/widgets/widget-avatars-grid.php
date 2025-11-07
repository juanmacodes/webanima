<?php
namespace Anima\Engine\Elementor\Widgets;

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Typography;
use Elementor\Widget_Base;
use WP_Query;

use function __;
use function anima_get_avatar_meta;
use function anima_normalize_terms;
use function esc_attr;
use function esc_attr__;
use function esc_html;
use function esc_html__;
use function esc_url;
use function get_the_ID;
use function get_the_permalink;
use function get_the_title;
use function get_terms;
use function wp_get_post_terms;
use function wp_get_attachment_image_url;
use function wp_list_pluck;
use function wp_reset_postdata;

class Widget_Avatars_Grid extends Widget_Base {
    public function get_name(): string {
        return 'anima-avatars-grid';
    }

    public function get_title(): string {
        return __( 'Avatares — Grid', 'anima-engine' );
    }

    public function get_icon(): string {
        return 'eicon-person';
    }

    public function get_categories(): array {
        return [ 'anima' ];
    }

    public function get_keywords(): array {
        return [ 'avatar', 'grid', 'galería', 'anima' ];
    }

    protected function register_controls(): void {
        $this->register_query_controls();
        $this->register_layout_controls();
        $this->register_style_controls();
    }

    protected function register_query_controls(): void {
        $this->start_controls_section(
            'section_query',
            [ 'label' => __( 'Consulta', 'anima-engine' ) ]
        );

        $this->add_control(
            'categories',
            [
                'label'       => __( 'Categorías', 'anima-engine' ),
                'type'        => Controls_Manager::SELECT2,
                'multiple'    => true,
                'label_block' => true,
                'options'     => $this->get_taxonomy_options( 'avatar_categoria' ),
            ]
        );

        $this->add_control(
            'posts_per_page',
            [
                'label'   => __( 'Avatares a mostrar', 'anima-engine' ),
                'type'    => Controls_Manager::NUMBER,
                'min'     => 1,
                'max'     => 24,
                'default' => 8,
            ]
        );

        $this->add_control(
            'order',
            [
                'label'   => __( 'Orden', 'anima-engine' ),
                'type'    => Controls_Manager::SELECT,
                'default' => 'DESC',
                'options' => [
                    'DESC' => __( 'Descendente', 'anima-engine' ),
                    'ASC'  => __( 'Ascendente', 'anima-engine' ),
                ],
            ]
        );

        $this->add_control(
            'show_filters',
            [
                'label'        => __( 'Mostrar filtros por categoría', 'anima-engine' ),
                'type'         => Controls_Manager::SWITCHER,
                'return_value' => 'yes',
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
                'max'            => 6,
                'desktop_default'=> 4,
                'tablet_default' => 2,
                'mobile_default' => 1,
            ]
        );

        $this->add_responsive_control(
            'gap',
            [
                'label'     => __( 'Espaciado', 'anima-engine' ),
                'type'      => Controls_Manager::SLIDER,
                'range'     => [ 'px' => [ 'min' => 8, 'max' => 64 ] ],
                'size_units'=> [ 'px' ],
                'default'   => [ 'size' => 28 ],
                'selectors' => [
                    '{{WRAPPER}} .an-grid' => 'gap: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'hover_effect',
            [
                'label'   => __( 'Hover', 'anima-engine' ),
                'type'    => Controls_Manager::SELECT,
                'default' => 'lift',
                'options' => [
                    'lift'   => __( 'Elevación', 'anima-engine' ),
                    'static' => __( 'Estático', 'anima-engine' ),
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function register_style_controls(): void {
        $this->start_controls_section(
            'section_style',
            [
                'label' => __( 'Estilos', 'anima-engine' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'title_typography',
                'selector' => '{{WRAPPER}} .an-avatar-card__title',
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name'     => 'card_shadow',
                'selector' => '{{WRAPPER}} .an-avatar-card',
            ]
        );

        $this->end_controls_section();
    }

    protected function render(): void {
        $settings = $this->get_settings_for_display();

        $args = [
            'post_type'      => 'avatar',
            'post_status'    => 'publish',
            'posts_per_page' => $settings['posts_per_page'] ?? 8,
            'order'          => $settings['order'] ?? 'DESC',
        ];

        $tax_query = [];
        $selected  = $settings['categories'] ?? [];
        if ( ! empty( $selected ) ) {
            $tax_query[] = [
                'taxonomy' => 'avatar_categoria',
                'field'    => 'term_id',
                'terms'    => array_map( 'absint', $selected ),
            ];
        }

        if ( ! empty( $tax_query ) ) {
            $args['tax_query'] = $tax_query;
        }

        $query = new WP_Query( $args );

        if ( ! $query->have_posts() ) {
            echo '<div class="an-empty" role="status">' . esc_html__( 'No se encontraron avatares.', 'anima-engine' ) . '</div>';
            return;
        }

        $columns_desktop = (int) ( $settings['columns'] ?? 4 );
        $columns_tablet  = (int) ( $settings['columns_tablet'] ?? 2 );
        $columns_mobile  = (int) ( $settings['columns_mobile'] ?? 1 );

        $filters = [];
        if ( 'yes' === ( $settings['show_filters'] ?? '' ) ) {
            $filters = get_terms(
                [
                    'taxonomy'   => 'avatar_categoria',
                    'hide_empty' => true,
                ]
            );
        }

        $this->add_render_attribute(
            'grid',
            [
                'class'            => 'an-grid' . ( 'lift' === ( $settings['hover_effect'] ?? 'lift' ) ? ' an-grid--lift' : '' ),
                'data-cols-desktop'=> (string) max( 1, $columns_desktop ),
                'data-cols-tablet' => (string) max( 1, $columns_tablet ),
                'data-cols-mobile' => (string) max( 1, $columns_mobile ),
            ]
        );

        echo '<div class="an-avatars-grid">';
        if ( ! empty( $filters ) ) {
            echo '<div class="an-grid__filters" aria-label="' . esc_attr__( 'Filtrar avatares', 'anima-engine' ) . '">';
            echo '<button type="button" class="is-active" data-filter="*">' . esc_html__( 'Todos', 'anima-engine' ) . '</button>';
            foreach ( $filters as $term ) {
                echo '<button type="button" data-filter="' . esc_attr( $term->slug ) . '">' . esc_html( $term->name ) . '</button>';
            }
            echo '</div>';
        }

        echo '<div ' . $this->get_render_attribute_string( 'grid' ) . '>';

        while ( $query->have_posts() ) {
            $query->the_post();
            $meta      = anima_get_avatar_meta( get_the_ID() );
            $image_id  = $meta['thumb_id'] ?: get_post_thumbnail_id();
            $image_url = $image_id ? wp_get_attachment_image_url( $image_id, 'an_square' ) : '';
            $terms     = anima_normalize_terms( wp_get_post_terms( get_the_ID(), 'avatar_categoria' ) );

            $term_slugs = implode( ' ', wp_list_pluck( $terms, 'slug' ) );

            echo '<article class="an-avatar-card" data-anima-reveal data-category="' . esc_attr( $term_slugs ) . '">';
            echo '<figure class="an-avatar-card__media">';
            if ( $image_url ) {
                echo '<img src="' . esc_url( $image_url ) . '" alt="' . esc_attr( get_the_title() ) . '" loading="lazy" />';
            }
            echo '</figure>';
            echo '<div class="an-avatar-card__body">';
            echo '<h3 class="an-avatar-card__title">' . esc_html( get_the_title() ) . '</h3>';
            if ( ! empty( $terms ) ) {
                echo '<div class="an-avatar-card__tags" aria-label="' . esc_attr__( 'Categorías', 'anima-engine' ) . '">';
                foreach ( $terms as $term ) {
                    echo '<span class="an-chip">' . esc_html( $term['name'] ) . '</span>';
                }
                echo '</div>';
            }
            echo '<div class="an-avatar-card__actions">';
            echo '<a class="an-button" href="' . esc_url( get_the_permalink() ) . '">' . esc_html__( 'Ver detalle', 'anima-engine' ) . '</a>';
            if ( ! empty( $meta['webgl_url'] ) ) {
                echo '<a class="an-button" href="#" data-anima-lightbox="' . esc_url( $meta['webgl_url'] ) . '" data-anima-lightbox-type="iframe" aria-label="' . esc_attr( sprintf( esc_html__( 'Abrir visor 3D de %s', 'anima-engine' ), get_the_title() ) ) . '">' . esc_html__( 'Abrir visor 3D', 'anima-engine' ) . '</a>';
            }
            echo '</div>';
            echo '</div>';
            echo '</article>';
        }

        echo '</div>';
        echo '</div>';

        wp_reset_postdata();
    }

    private function get_taxonomy_options( string $taxonomy ): array {
        $terms = get_terms(
            [
                'taxonomy'   => $taxonomy,
                'hide_empty' => false,
            ]
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
