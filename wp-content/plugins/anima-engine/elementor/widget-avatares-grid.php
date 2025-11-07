<?php
namespace Anima\Engine\Elementor;

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Widget_Base;
use WP_Query;

use function __;
use function absint;
use function anima_get_avatar_meta;
use function anima_parse_csv_slugs;
use function esc_attr;
use function esc_attr__;
use function esc_html;
use function esc_html__;
use function esc_url;
use function get_post_thumbnail_id;
use function get_permalink;
use function get_terms;
use function get_the_ID;
use function get_the_title;
use function paginate_links;
use function sanitize_text_field;
use function wp_get_attachment_image_src;
use function wp_get_post_terms;
use function is_wp_error;
use function wp_reset_postdata;

/**
 * Grid de avatares para Elementor.
 */
class Widget_Avatares_Grid extends Widget_Base {
    public function get_name(): string {
        return 'anima-avatares-grid';
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

    protected function register_controls(): void {
        $this->register_query_controls();
        $this->register_display_controls();
        $this->register_style_controls();
    }

    private function register_query_controls(): void {
        $this->start_controls_section(
            'section_query',
            [
                'label' => __( 'Consulta', 'anima-engine' ),
            ]
        );

        $this->add_control(
            'posts_per_page',
            [
                'label'   => __( 'Avatares por página', 'anima-engine' ),
                'type'    => Controls_Manager::NUMBER,
                'min'     => 1,
                'max'     => 24,
                'default' => 9,
            ]
        );

        $this->add_control(
            'orderby',
            [
                'label'   => __( 'Ordenar por', 'anima-engine' ),
                'type'    => Controls_Manager::SELECT,
                'default' => 'date',
                'options' => [
                    'date'       => __( 'Fecha', 'anima-engine' ),
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

        $this->add_control(
            'avatar_tipo_terms',
            [
                'label'       => __( 'Filtrar por tipo', 'anima-engine' ),
                'type'        => Controls_Manager::SELECT2,
                'options'     => $this->get_taxonomy_options( 'avatar_tipo' ),
                'label_block' => true,
                'multiple'    => true,
            ]
        );

        $this->add_control(
            'enable_pagination',
            [
                'label'        => __( 'Paginación', 'anima-engine' ),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => __( 'Sí', 'anima-engine' ),
                'label_off'    => __( 'No', 'anima-engine' ),
                'return_value' => 'yes',
                'default'      => '',
            ]
        );

        $this->end_controls_section();
    }

    private function register_display_controls(): void {
        $this->start_controls_section(
            'section_display',
            [
                'label' => __( 'Opciones de tarjeta', 'anima-engine' ),
            ]
        );

        $this->add_control(
            'layout',
            [
                'label'   => __( 'Diseño', 'anima-engine' ),
                'type'    => Controls_Manager::SELECT,
                'default' => 'grid',
                'options' => [
                    'grid'    => __( 'Grid', 'anima-engine' ),
                    'masonry' => __( 'Masonry', 'anima-engine' ),
                ],
            ]
        );

        $this->add_control(
            'use_lightbox',
            [
                'label'        => __( 'Abrir en lightbox', 'anima-engine' ),
                'type'         => Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'label_on'     => __( 'Sí', 'anima-engine' ),
                'label_off'    => __( 'No', 'anima-engine' ),
                'default'      => '',
            ]
        );

        $this->add_control(
            'show_link',
            [
                'label'        => __( 'Enlazar a la ficha', 'anima-engine' ),
                'type'         => Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'label_on'     => __( 'Sí', 'anima-engine' ),
                'label_off'    => __( 'No', 'anima-engine' ),
                'default'      => 'yes',
                'condition'    => [ 'use_lightbox!' => 'yes' ],
            ]
        );

        $this->add_control(
            'show_tags',
            [
                'label'        => __( 'Mostrar tags/metadatos', 'anima-engine' ),
                'type'         => Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'label_on'     => __( 'Sí', 'anima-engine' ),
                'label_off'    => __( 'No', 'anima-engine' ),
                'default'      => 'yes',
            ]
        );

        $this->end_controls_section();
    }

    private function register_style_controls(): void {
        $this->start_controls_section(
            'section_style',
            [
                'label' => __( 'Estilo', 'anima-engine' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'columns',
            [
                'label'          => __( 'Columnas', 'anima-engine' ),
                'type'           => Controls_Manager::SLIDER,
                'range'          => [
                    'px' => [
                        'min' => 1,
                        'max' => 6,
                    ],
                ],
                'default'        => [ 'size' => 4 ],
                'tablet_default' => [ 'size' => 3 ],
                'mobile_default' => [ 'size' => 1 ],
                'selectors'      => [
                    '{{WRAPPER}} .an-grid' => 'grid-template-columns: repeat({{SIZE}}, minmax(0, 1fr));',
                ],
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
                    '{{WRAPPER}} .an-avatar-card' => '--an-card-radius: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name'     => 'card_shadow',
                'selector' => '{{WRAPPER}} .an-avatar-card',
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name'     => 'card_shadow_hover',
                'label'    => __( 'Sombra al pasar', 'anima-engine' ),
                'selector' => '{{WRAPPER}} .an-avatar-card:hover',
            ]
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name'     => 'overlay_background',
                'label'    => __( 'Overlay', 'anima-engine' ),
                'selector' => '{{WRAPPER}} .an-lightbox-trigger',
                'types'    => [ 'gradient' ],
            ]
        );

        $this->end_controls_section();
    }

    protected function render(): void {
        $settings = $this->get_settings_for_display();

        $paged_var = 'anima_avatar_page_' . $this->get_id();
        $paged     = isset( $_GET[ $paged_var ] ) ? max( 1, absint( $_GET[ $paged_var ] ) ) : 1; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

        $query_args = [
            'post_type'      => 'avatar',
            'post_status'    => 'publish',
            'posts_per_page' => absint( $settings['posts_per_page'] ?? 9 ),
            'orderby'        => sanitize_text_field( $settings['orderby'] ?? 'date' ),
            'order'          => sanitize_text_field( $settings['order'] ?? 'DESC' ),
            'paged'          => ( 'yes' === ( $settings['enable_pagination'] ?? '' ) ) ? $paged : 1,
        ];

        $terms = anima_parse_csv_slugs( $settings['avatar_tipo_terms'] ?? [] );
        if ( ! empty( $terms ) ) {
            $query_args['tax_query'] = [
                [
                    'taxonomy' => 'avatar_tipo',
                    'field'    => 'slug',
                    'terms'    => $terms,
                ],
            ];
        }

        $query = new WP_Query( $query_args );

        if ( ! $query->have_posts() ) {
            echo '<div class="an-grid anima-grid-empty">' . esc_html__( 'No hay avatares disponibles en este momento.', 'anima-engine' ) . '</div>';
            return;
        }

        $layout       = $settings['layout'] ?? 'grid';
        $use_lightbox = 'yes' === ( $settings['use_lightbox'] ?? '' );
        $show_link    = 'yes' === ( $settings['show_link'] ?? 'yes' );
        $show_tags    = 'yes' === ( $settings['show_tags'] ?? 'yes' );

        echo '<div class="an-grid an-grid--avatares" data-layout="' . esc_attr( $layout ) . '">';

        while ( $query->have_posts() ) {
            $query->the_post();
            $post_id = get_the_ID();
            $meta    = anima_get_avatar_meta( $post_id );

            echo '<article class="an-avatar-card" data-anima-reveal>';
            echo '<div class="an-avatar-card__media">';

            $thumbnail = get_the_post_thumbnail(
                $post_id,
                'anima_avatar_square',
                [
                    'class'    => 'an-avatar-card__image',
                    'loading'  => 'lazy',
                    'decoding' => 'async',
                ]
            );

            if ( $thumbnail ) {
                echo $thumbnail;
            }

            if ( $use_lightbox ) {
                $full = wp_get_attachment_image_src( get_post_thumbnail_id( $post_id ), 'full' );
                if ( $full ) {
                    echo '<a class="an-lightbox-trigger" href="' . esc_url( $full[0] ) . '" data-elementor-open-lightbox="yes" aria-label="' . esc_attr( sprintf( __( 'Ver avatar: %s', 'anima-engine' ), get_the_title( $post_id ) ) ) . '">' . esc_html__( 'Ver avatar', 'anima-engine' ) . '</a>';
                }
            } elseif ( $show_link ) {
                echo '<a class="an-lightbox-trigger" href="' . esc_url( get_permalink( $post_id ) ) . '" aria-label="' . esc_attr( sprintf( __( 'Ver avatar: %s', 'anima-engine' ), get_the_title( $post_id ) ) ) . '"></a>';
            }

            echo '</div>';

            echo '<div class="an-avatar-card__body">';
            $title_tag  = ( $show_link && ! $use_lightbox ) ? 'a' : 'span';
            $title_attr = ( $show_link && ! $use_lightbox ) ? ' href="' . esc_url( get_permalink( $post_id ) ) . '"' : '';
            echo '<h3 class="an-avatar-card__title"><' . $title_tag . $title_attr . '>' . esc_html( get_the_title( $post_id ) ) . '</' . $title_tag . '></h3>';

            if ( $show_tags ) {
                $chips = [];

                if ( ! empty( $meta['engine'] ) ) {
                    $chips[] = '<span class="an-badge">' . esc_html( $meta['engine'] ) . '</span>';
                }

                if ( $meta['rig'] ) {
                    $chips[] = '<span class="an-badge an-badge--success">' . esc_html__( 'Rig listo', 'anima-engine' ) . '</span>';
                }

                $tax_terms = wp_get_post_terms( $post_id, 'avatar_tipo' );
                if ( ! is_wp_error( $tax_terms ) ) {
                    foreach ( $tax_terms as $term ) {
                        $chips[] = '<span class="an-chip">' . esc_html( $term->name ) . '</span>';
                    }
                }

                if ( ! empty( $meta['tags'] ) ) {
                    foreach ( $meta['tags'] as $tag ) {
                        $chips[] = '<span class="an-chip an-chip--hours">' . esc_html( $tag ) . '</span>';
                    }
                }

                if ( ! empty( $chips ) ) {
                    echo '<div class="an-avatar-card__tags">' . implode( '', $chips ) . '</div>';
                }
            }

            echo '</div>';
            echo '</article>';
        }

        echo '</div>';

        if ( 'yes' === ( $settings['enable_pagination'] ?? '' ) && $query->max_num_pages > 1 ) {
            $pagination = paginate_links(
                [
                    'total'   => max( 1, (int) $query->max_num_pages ),
                    'current' => $paged,
                    'type'    => 'array',
                    'add_args' => [ $paged_var => '%#%' ],
                ]
            );

            if ( ! empty( $pagination ) ) {
                echo '<nav class="an-pagination" aria-label="' . esc_attr__( 'Avatares', 'anima-engine' ) . '">';
                foreach ( $pagination as $link ) {
                    echo $link;
                }
                echo '</nav>';
            }
        }

        wp_reset_postdata();
    }

    private function get_taxonomy_options( string $taxonomy ): array {
        $terms = get_terms(
            [
                'taxonomy'   => $taxonomy,
                'hide_empty' => false,
            ]
        );

        if ( is_wp_error( $terms ) || empty( $terms ) ) {
            return [];
        }

        $options = [];
        foreach ( $terms as $term ) {
            $options[ $term->slug ] = $term->name;
        }

        return $options;
    }
}
