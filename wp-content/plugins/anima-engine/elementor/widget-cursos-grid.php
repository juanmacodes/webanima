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
use function anima_collect_tax_badges;
use function anima_get_course_meta;
use function anima_get_trimmed_excerpt;
use function anima_parse_csv_slugs;
use function esc_attr;
use function esc_attr__;
use function esc_html;
use function esc_html__;
use function esc_url;
use function get_permalink;
use function get_the_ID;
use function get_the_title;
use function get_terms;
use function paginate_links;
use function sanitize_text_field;
use function is_wp_error;
use function wp_reset_postdata;

/**
 * Grid de cursos para Elementor con estilo Anima.
 */
class Widget_Cursos_Grid extends Widget_Base {
    public function get_name(): string {
        return 'anima-cursos-grid';
    }

    public function get_title(): string {
        return __( 'Cursos — Grid', 'anima-engine' );
    }

    public function get_icon(): string {
        return 'eicon-gallery-grid';
    }

    public function get_categories(): array {
        return [ 'anima' ];
    }

    public function get_keywords(): array {
        return [ 'curso', 'grid', 'card', 'academy', 'anima' ];
    }

    protected function register_controls(): void {
        $this->register_query_controls();
        $this->register_card_controls();
        $this->register_style_controls();
    }

    protected function register_query_controls(): void {
        $this->start_controls_section(
            'section_query',
            [
                'label' => __( 'Consulta', 'anima-engine' ),
            ]
        );

        $this->add_control(
            'posts_per_page',
            [
                'label'   => __( 'Entradas por página', 'anima-engine' ),
                'type'    => Controls_Manager::NUMBER,
                'min'     => 1,
                'max'     => 24,
                'default' => 8,
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
                    'modified'   => __( 'Fecha de modificación', 'anima-engine' ),
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
            'nivel_terms',
            [
                'label'       => __( 'Nivel', 'anima-engine' ),
                'type'        => Controls_Manager::SELECT2,
                'options'     => $this->get_taxonomy_options( 'nivel' ),
                'multiple'    => true,
                'label_block' => true,
            ]
        );

        $this->add_control(
            'modalidad_terms',
            [
                'label'       => __( 'Modalidad', 'anima-engine' ),
                'type'        => Controls_Manager::SELECT2,
                'options'     => $this->get_taxonomy_options( 'modalidad' ),
                'multiple'    => true,
                'label_block' => true,
            ]
        );

        $this->add_control(
            'tecnologia_terms',
            [
                'label'       => __( 'Tecnología', 'anima-engine' ),
                'type'        => Controls_Manager::SELECT2,
                'options'     => $this->get_taxonomy_options( 'tecnologia' ),
                'multiple'    => true,
                'label_block' => true,
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

    protected function register_card_controls(): void {
        $this->start_controls_section(
            'section_card',
            [
                'label' => __( 'Contenido de la tarjeta', 'anima-engine' ),
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
                'label'       => __( 'Texto botón hover', 'anima-engine' ),
                'type'        => Controls_Manager::TEXT,
                'default'     => __( 'Ver curso', 'anima-engine' ),
                'label_block' => true,
            ]
        );

        $this->end_controls_section();
    }

    protected function register_style_controls(): void {
        $this->start_controls_section(
            'section_layout_style',
            [
                'label' => __( 'Diseño', 'anima-engine' ),
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
                'tablet_default' => [ 'size' => 2 ],
                'mobile_default' => [ 'size' => 1 ],
                'selectors'      => [
                    '{{WRAPPER}} .an-grid' => 'grid-template-columns: repeat({{SIZE}}, minmax(0, 1fr));',
                ],
            ]
        );

        $this->add_control(
            'card_radius',
            [
                'label'      => __( 'Radio de borde', 'anima-engine' ),
                'type'       => Controls_Manager::SLIDER,
                'range'      => [
                    'px' => [
                        'min' => 0,
                        'max' => 40,
                    ],
                ],
                'default'    => [ 'size' => 16 ],
                'selectors'  => [
                    '{{WRAPPER}} .an-card' => '--an-card-radius: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .an-avatar-card' => '--an-card-radius: {{SIZE}}{{UNIT}};',
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
            Group_Control_Box_Shadow::get_type(),
            [
                'name'     => 'card_shadow_hover',
                'label'    => __( 'Sombra al pasar', 'anima-engine' ),
                'selector' => '{{WRAPPER}} .an-card:hover',
            ]
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name'     => 'overlay_background',
                'label'    => __( 'Overlay', 'anima-engine' ),
                'selector' => '{{WRAPPER}} .an-card__overlay',
                'types'    => [ 'gradient' ],
            ]
        );

        $this->add_control(
            'text_color',
            [
                'label'     => __( 'Color de texto', 'anima-engine' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .an-card' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'muted_color',
            [
                'label'     => __( 'Color secundario', 'anima-engine' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .an-card__excerpt' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'accent_color',
            [
                'label'     => __( 'Color acento', 'anima-engine' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .an-chip' => 'background: rgba(61, 214, 255, 0.12); border-color: rgba(61, 214, 255, 0.25); color: {{VALUE}};',
                    '{{WRAPPER}} .an-card__overlay-button' => 'background: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function render(): void {
        $settings = $this->get_settings_for_display();

        $paged_var = 'anima_page_' . $this->get_id();
        $paged     = isset( $_GET[ $paged_var ] ) ? max( 1, absint( $_GET[ $paged_var ] ) ) : 1; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

        $query_args = [
            'post_type'      => 'curso',
            'post_status'    => 'publish',
            'posts_per_page' => absint( $settings['posts_per_page'] ?? 8 ),
            'orderby'        => sanitize_text_field( $settings['orderby'] ?? 'date' ),
            'order'          => sanitize_text_field( $settings['order'] ?? 'DESC' ),
            'paged'          => ( 'yes' === ( $settings['enable_pagination'] ?? '' ) ) ? $paged : 1,
        ];

        $tax_query = [];
        foreach ( [
            'nivel'      => $settings['nivel_terms'] ?? [],
            'modalidad'  => $settings['modalidad_terms'] ?? [],
            'tecnologia' => $settings['tecnologia_terms'] ?? [],
        ] as $taxonomy => $values ) {
            $terms = anima_parse_csv_slugs( $values );
            if ( empty( $terms ) ) {
                continue;
            }

            $tax_query[] = [
                'taxonomy' => $taxonomy,
                'field'    => 'slug',
                'terms'    => $terms,
            ];
        }

        if ( ! empty( $tax_query ) ) {
            $query_args['tax_query'] = $tax_query;
        }

        $query = new WP_Query( $query_args );

        if ( ! $query->have_posts() ) {
            echo '<div class="an-grid anima-grid-empty">' . esc_html__( 'No hay cursos disponibles en este momento.', 'anima-engine' ) . '</div>';
            return;
        }

        $show_image   = 'yes' === ( $settings['show_image'] ?? 'yes' );
        $show_excerpt = 'yes' === ( $settings['show_excerpt'] ?? 'yes' );
        $show_price   = 'yes' === ( $settings['show_price'] ?? 'yes' );
        $show_hours   = 'yes' === ( $settings['show_hours'] ?? 'yes' );
        $show_badges  = 'yes' === ( $settings['show_badges'] ?? 'yes' );
        $button_text  = $settings['button_text'] ?: __( 'Ver curso', 'anima-engine' );

        echo '<div class="an-grid an-grid--courses">';

        while ( $query->have_posts() ) {
            $query->the_post();
            $post_id  = get_the_ID();
            $meta     = anima_get_course_meta( $post_id );
            $badges   = $show_badges ? anima_collect_tax_badges( $post_id, [ 'nivel', 'modalidad', 'tecnologia' ] ) : [];
            $excerpt  = $show_excerpt ? anima_get_trimmed_excerpt( $post_id, 20 ) : '';
            $permalink = get_permalink( $post_id );

            echo '<article class="an-card" data-anima-reveal>';

            if ( $show_image ) {
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
            if ( $show_price && '' !== $meta['price'] ) {
                $chips[] = '<span class="an-chip an-chip--price">' . esc_html( $meta['price'] ) . '</span>';
            }
            if ( $show_hours && '' !== $meta['hours'] ) {
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
                echo '<nav class="an-pagination" aria-label="' . esc_attr__( 'Cursos', 'anima-engine' ) . '">';
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
