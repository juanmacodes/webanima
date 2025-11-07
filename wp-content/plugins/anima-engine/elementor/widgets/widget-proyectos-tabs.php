<?php
namespace Anima\Engine\Elementor;

defined( 'ABSPATH' ) || exit;

use Anima\Engine\Elementor\Projects\ProjectCardRenderer;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Typography;
use Elementor\Widget_Base;
use WP_Query;
use WP_Term;

use function __;
use function absint;
use function esc_attr;
use function esc_html;
use function esc_html__;
use function esc_url;
use function get_terms;
use function is_wp_error;
use function rest_url;
use function sanitize_text_field;
use function wp_json_encode;
use function wp_reset_postdata;

/**
 * Widget de pestañas de proyectos agrupados por servicio.
 */
class Widget_Proyectos_Tabs extends Widget_Base {
    public function get_name(): string {
        return 'anima-proyectos-tabs';
    }

    public function get_title(): string {
        return __( 'Proyectos — Tabs', 'anima-engine' );
    }

    public function get_icon(): string {
        return 'eicon-tabs';
    }

    public function get_categories(): array {
        return [ 'anima' ];
    }

    public function get_script_depends(): array {
        return [ 'anima-projects-tabs' ];
    }

    protected function register_controls(): void {
        $this->register_content_controls();
        $this->register_grid_controls();
        $this->register_carousel_controls();
        $this->register_tab_style_controls();
        $this->register_card_style_controls();
        $this->register_advanced_controls();
    }

    protected function register_content_controls(): void {
        $this->start_controls_section(
            'section_content',
            [
                'label' => __( 'Contenido', 'anima-engine' ),
            ]
        );

        $this->add_control(
            'servicio_terms',
            [
                'label'       => __( 'Servicios', 'anima-engine' ),
                'type'        => Controls_Manager::SELECT2,
                'multiple'    => true,
                'label_block' => true,
                'options'     => $this->get_taxonomy_options( 'servicio' ),
                'description' => __( 'Si no se selecciona ninguno se mostrarán los servicios con proyectos publicados.', 'anima-engine' ),
            ]
        );

        $this->add_control(
            'layout_mode',
            [
                'label'   => __( 'Diseño de pestaña', 'anima-engine' ),
                'type'    => Controls_Manager::SELECT,
                'default' => 'grid',
                'options' => [
                    'grid'     => __( 'Grid', 'anima-engine' ),
                    'masonry'  => __( 'Masonry', 'anima-engine' ),
                    'carousel' => __( 'Carrusel', 'anima-engine' ),
                ],
            ]
        );

        $this->add_control(
            'posts_per_page',
            [
                'label'   => __( 'Proyectos por pestaña', 'anima-engine' ),
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
                    'meta_value' => __( 'Valor meta (anima_anio)', 'anima-engine' ),
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
            'year_min',
            [
                'label'       => __( 'Año mínimo', 'anima-engine' ),
                'type'        => Controls_Manager::NUMBER,
                'min'         => 1900,
                'max'         => gmdate( 'Y' ),
                'description' => __( 'Filtra proyectos a partir de este año (opcional).', 'anima-engine' ),
            ]
        );

        $this->add_control(
            'year_max',
            [
                'label'       => __( 'Año máximo', 'anima-engine' ),
                'type'        => Controls_Manager::NUMBER,
                'min'         => 1900,
                'max'         => gmdate( 'Y' ) + 2,
                'description' => __( 'Limita proyectos hasta este año (opcional).', 'anima-engine' ),
            ]
        );

        $this->add_control(
            'search_keyword',
            [
                'label'       => __( 'Búsqueda', 'anima-engine' ),
                'type'        => Controls_Manager::TEXT,
                'placeholder' => __( 'Ej: holográficos, IA…', 'anima-engine' ),
            ]
        );

        $this->add_control(
            'show_image',
            [
                'label'        => __( 'Mostrar imagen destacada', 'anima-engine' ),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => __( 'Sí', 'anima-engine' ),
                'label_off'    => __( 'No', 'anima-engine' ),
                'return_value' => 'yes',
                'default'      => 'yes',
            ]
        );

        $this->add_control(
            'show_client',
            [
                'label'        => __( 'Mostrar cliente', 'anima-engine' ),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => __( 'Sí', 'anima-engine' ),
                'label_off'    => __( 'No', 'anima-engine' ),
                'return_value' => 'yes',
                'default'      => 'yes',
            ]
        );

        $this->add_control(
            'show_year',
            [
                'label'        => __( 'Mostrar año', 'anima-engine' ),
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
            'show_stack',
            [
                'label'        => __( 'Mostrar stack (chips)', 'anima-engine' ),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => __( 'Sí', 'anima-engine' ),
                'label_off'    => __( 'No', 'anima-engine' ),
                'return_value' => 'yes',
                'default'      => 'yes',
            ]
        );

        $this->add_control(
            'show_kpis',
            [
                'label'        => __( 'Mostrar KPIs', 'anima-engine' ),
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
                'label'       => __( 'Texto del botón', 'anima-engine' ),
                'type'        => Controls_Manager::TEXT,
                'default'     => __( 'Ver caso', 'anima-engine' ),
                'placeholder' => __( 'Ver caso', 'anima-engine' ),
            ]
        );

        $this->add_control(
            'excerpt_length',
            [
                'label'   => __( 'Largo del resumen (palabras)', 'anima-engine' ),
                'type'    => Controls_Manager::NUMBER,
                'min'     => 8,
                'max'     => 80,
                'default' => 26,
            ]
        );

        $this->add_control(
            'kpi_limit',
            [
                'label'   => __( 'Máximo de KPIs', 'anima-engine' ),
                'type'    => Controls_Manager::NUMBER,
                'min'     => 0,
                'max'     => 3,
                'default' => 3,
            ]
        );

        $this->end_controls_section();
    }

    protected function register_grid_controls(): void {
        $this->start_controls_section(
            'section_grid',
            [
                'label'     => __( 'Grid y Masonry', 'anima-engine' ),
                'condition' => [ 'layout_mode!' => 'carousel' ],
            ]
        );

        $this->add_responsive_control(
            'columns_desktop',
            [
                'label'          => __( 'Columnas', 'anima-engine' ),
                'type'           => Controls_Manager::NUMBER,
                'min'            => 1,
                'max'            => 6,
                'desktop_default'=> 3,
                'tablet_default' => 2,
                'mobile_default' => 1,
            ]
        );

        $this->add_responsive_control(
            'grid_gap',
            [
                'label'      => __( 'Separación', 'anima-engine' ),
                'type'       => Controls_Manager::SLIDER,
                'range'      => [
                    'px' => [
                        'min' => 0,
                        'max' => 80,
                    ],
                ],
                'size_units' => [ 'px', 'rem' ],
                'default'    => [
                    'size' => 28,
                    'unit' => 'px',
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function register_carousel_controls(): void {
        $this->start_controls_section(
            'section_carousel',
            [
                'label'     => __( 'Carrusel', 'anima-engine' ),
                'condition' => [ 'layout_mode' => 'carousel' ],
            ]
        );

        $this->add_responsive_control(
            'carousel_slides',
            [
                'label'          => __( 'Slides visibles', 'anima-engine' ),
                'type'           => Controls_Manager::NUMBER,
                'min'            => 1,
                'max'            => 6,
                'desktop_default'=> 3,
                'tablet_default' => 2,
                'mobile_default' => 1,
            ]
        );

        $this->add_control(
            'carousel_autoplay',
            [
                'label'        => __( 'Autoplay', 'anima-engine' ),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => __( 'Sí', 'anima-engine' ),
                'label_off'    => __( 'No', 'anima-engine' ),
                'return_value' => 'yes',
            ]
        );

        $this->add_control(
            'carousel_loop',
            [
                'label'        => __( 'Loop infinito', 'anima-engine' ),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => __( 'Sí', 'anima-engine' ),
                'label_off'    => __( 'No', 'anima-engine' ),
                'return_value' => 'yes',
            ]
        );

        $this->add_control(
            'carousel_speed',
            [
                'label'   => __( 'Velocidad (ms)', 'anima-engine' ),
                'type'    => Controls_Manager::NUMBER,
                'min'     => 100,
                'max'     => 10000,
                'default' => 600,
            ]
        );

        $this->add_control(
            'carousel_navigation',
            [
                'label'        => __( 'Controles visibles', 'anima-engine' ),
                'type'         => Controls_Manager::SELECT,
                'default'      => 'arrows-dots',
                'options'      => [
                    'arrows-dots' => __( 'Flechas y puntos', 'anima-engine' ),
                    'arrows'      => __( 'Solo flechas', 'anima-engine' ),
                    'dots'        => __( 'Solo puntos', 'anima-engine' ),
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function register_tab_style_controls(): void {
        $this->start_controls_section(
            'section_tabs_style',
            [
                'label' => __( 'Estilo de pestañas', 'anima-engine' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'tabs_position',
            [
                'label'   => __( 'Posición', 'anima-engine' ),
                'type'    => Controls_Manager::SELECT,
                'default' => 'top',
                'options' => [
                    'top'  => __( 'Superior', 'anima-engine' ),
                    'left' => __( 'Izquierda', 'anima-engine' ),
                ],
            ]
        );

        $this->add_control(
            'tabs_alignment',
            [
                'label'   => __( 'Alineación', 'anima-engine' ),
                'type'    => Controls_Manager::CHOOSE,
                'default' => 'flex-start',
                'options' => [
                    'flex-start' => [
                        'title' => __( 'Inicio', 'anima-engine' ),
                        'icon'  => 'eicon-text-align-left',
                    ],
                    'center'     => [
                        'title' => __( 'Centro', 'anima-engine' ),
                        'icon'  => 'eicon-text-align-center',
                    ],
                    'flex-end'   => [
                        'title' => __( 'Final', 'anima-engine' ),
                        'icon'  => 'eicon-text-align-right',
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .anima-tabs' => '--an-tabs-align: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'tabs_typography',
                'selector' => '{{WRAPPER}} .anima-tabs__button',
            ]
        );

        $this->add_control(
            'tabs_gap',
            [
                'label'   => __( 'Separación entre pestañas', 'anima-engine' ),
                'type'    => Controls_Manager::SLIDER,
                'range'   => [ 'px' => [ 'min' => 0, 'max' => 60 ] ],
                'default' => [ 'size' => 12, 'unit' => 'px' ],
                'selectors' => [
                    '{{WRAPPER}} .anima-tabs' => '--an-tabs-gap: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'tabs_radius',
            [
                'label' => __( 'Radio', 'anima-engine' ),
                'type'  => Controls_Manager::SLIDER,
                'range' => [ 'px' => [ 'min' => 0, 'max' => 40 ] ],
                'selectors' => [
                    '{{WRAPPER}} .anima-tabs__button' => 'border-radius: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'tabs_color_idle',
            [
                'label' => __( 'Color base', 'anima-engine' ),
                'type'  => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}}' => '--an-tabs-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'tabs_color_active',
            [
                'label' => __( 'Color activo', 'anima-engine' ),
                'type'  => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}}' => '--an-tabs-active: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'tabs_color_hover',
            [
                'label' => __( 'Color hover', 'anima-engine' ),
                'type'  => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}}' => '--an-tabs-hover: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'tabs_indicator',
            [
                'label'        => __( 'Mostrar indicador inferior', 'anima-engine' ),
                'type'         => Controls_Manager::SWITCHER,
                'return_value' => '1',
                'selectors'    => [
                    '{{WRAPPER}}' => '--an-tabs-indicator: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function register_card_style_controls(): void {
        $this->start_controls_section(
            'section_card_style',
            [
                'label' => __( 'Tarjeta', 'anima-engine' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'card_radius',
            [
                'label' => __( 'Radio de tarjeta', 'anima-engine' ),
                'type'  => Controls_Manager::SLIDER,
                'range' => [ 'px' => [ 'min' => 0, 'max' => 60 ] ],
                'selectors' => [
                    '{{WRAPPER}} .an-card' => 'border-radius: {{SIZE}}{{UNIT}};',
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
                'name'     => 'card_overlay',
                'selector' => '{{WRAPPER}} .an-card__overlay',
            ]
        );

        $this->add_control(
            'chip_color',
            [
                'label' => __( 'Color de chips', 'anima-engine' ),
                'type'  => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}}' => '--an-chip-custom: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function register_advanced_controls(): void {
        $this->start_controls_section(
            'section_advanced',
            [
                'label' => __( 'Avanzado', 'anima-engine' ),
            ]
        );

        $this->add_control(
            'enable_ajax',
            [
                'label'        => __( 'Carga AJAX al cambiar pestaña', 'anima-engine' ),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => __( 'Sí', 'anima-engine' ),
                'label_off'    => __( 'No', 'anima-engine' ),
                'return_value' => 'yes',
            ]
        );

        $this->add_control(
            'enable_prefetch',
            [
                'label'        => __( 'Prefetch de siguiente pestaña', 'anima-engine' ),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => __( 'Sí', 'anima-engine' ),
                'label_off'    => __( 'No', 'anima-engine' ),
                'return_value' => 'yes',
                'condition'    => [ 'enable_ajax' => 'yes' ],
            ]
        );

        $this->add_control(
            'cache_ttl',
            [
                'label'   => __( 'TTL caché (segundos)', 'anima-engine' ),
                'type'    => Controls_Manager::NUMBER,
                'min'     => 0,
                'max'     => 86400,
                'default' => 900,
                'condition' => [ 'enable_ajax' => 'yes' ],
            ]
        );

        $this->end_controls_section();
    }

    protected function get_taxonomy_options( string $taxonomy ): array {
        $terms = get_terms(
            [
                'taxonomy'   => $taxonomy,
                'hide_empty' => true,
            ]
        );

        if ( empty( $terms ) || is_wp_error( $terms ) ) {
            return [];
        }

        $options = [];
        foreach ( $terms as $term ) {
            if ( $term instanceof WP_Term ) {
                $options[ $term->slug ] = $term->name;
            }
        }

        return $options;
    }

    protected function render(): void {
        $settings = $this->get_settings_for_display();
        $terms    = $this->resolve_terms( $settings );

        if ( empty( $terms ) ) {
            echo '<div class="an-empty" role="status">' . esc_html__( 'No hay servicios disponibles.', 'anima-engine' ) . '</div>';
            return;
        }

        $layout         = $this->sanitize_layout( $settings['layout_mode'] ?? 'grid' );
        $ajax_enabled   = 'yes' === ( $settings['enable_ajax'] ?? '' );
        $prefetch       = 'yes' === ( $settings['enable_prefetch'] ?? '' );
        $cache_ttl      = absint( $settings['cache_ttl'] ?? 0 );
        $posts_per_page = absint( $settings['posts_per_page'] ?? 6 );
        $orderby        = $this->sanitize_orderby( $settings['orderby'] ?? 'date' );
        $order          = $this->sanitize_order( $settings['order'] ?? 'DESC' );
        $year_min       = isset( $settings['year_min'] ) ? absint( $settings['year_min'] ) : 0;
        $year_max       = isset( $settings['year_max'] ) ? absint( $settings['year_max'] ) : 0;
        $search         = sanitize_text_field( $settings['search_keyword'] ?? '' );

        $card_settings = [
            'show_image'     => $settings['show_image'] ?? 'yes',
            'show_client'    => $settings['show_client'] ?? 'yes',
            'show_year'      => $settings['show_year'] ?? 'yes',
            'show_excerpt'   => $settings['show_excerpt'] ?? 'yes',
            'show_stack'     => $settings['show_stack'] ?? 'yes',
            'show_kpis'      => $settings['show_kpis'] ?? 'yes',
            'button_text'    => $settings['button_text'] ?? __( 'Ver caso', 'anima-engine' ),
            'excerpt_length' => absint( $settings['excerpt_length'] ?? 26 ),
            'kpi_limit'      => absint( $settings['kpi_limit'] ?? 3 ),
        ];

        $data_columns = $this->build_responsive_columns( $settings );
        $carousel     = $this->build_carousel_settings( $settings );

        $container_attrs = [
            'class'             => 'anima-projects-tabs',
            'data-layout'       => $layout,
            'data-ajax'         => $ajax_enabled ? '1' : '0',
            'data-prefetch'     => $prefetch ? '1' : '0',
            'data-cache-ttl'    => $cache_ttl,
            'data-endpoint'     => rest_url( 'anima/v' . ANIMA_ENGINE_API_VERSION . '/proyectos' ),
            'data-columns'      => $data_columns,
            'data-carousel'     => $carousel,
            'data-tabs-align'   => $settings['tabs_alignment'] ?? 'flex-start',
            'data-tabs-position'=> $settings['tabs_position'] ?? 'top',
        ];

        $orientation = 'left' === ( $settings['tabs_position'] ?? 'top' ) ? 'vertical' : 'horizontal';

        printf( '<div %s>', $this->build_html_attributes( $container_attrs ) );
        printf( '<div class="anima-tabs" role="tablist" aria-orientation="%s">', esc_attr( $orientation ) );

        foreach ( $terms as $index => $term ) {
            $tab_id   = $this->get_id() . '-tab-' . $term->slug;
            $panel_id = $this->get_id() . '-panel-' . $term->slug;
            $selected = 0 === $index;
            printf(
                '<button class="anima-tabs__button" id="%1$s" role="tab" aria-controls="%2$s" aria-selected="%3$s" tabindex="%4$d" data-term="%5$s">%6$s</button>',
                esc_attr( $tab_id ),
                esc_attr( $panel_id ),
                $selected ? 'true' : 'false',
                $selected ? 0 : -1,
                esc_attr( $term->slug ),
                esc_html( $term->name )
            );
        }

        echo '</div>';

        echo '<div class="anima-tabs__panels">';
        foreach ( $terms as $index => $term ) {
            $panel_id = $this->get_id() . '-panel-' . $term->slug;
            $tab_id   = $this->get_id() . '-tab-' . $term->slug;
            $selected = 0 === $index;

            $panel_attrs = [
                'id'             => $panel_id,
                'class'          => 'anima-tabs__panel',
                'role'           => 'tabpanel',
                'aria-labelledby'=> $tab_id,
                'data-term'      => $term->slug,
            ];

            if ( ! $selected ) {
                $panel_attrs['hidden'] = 'hidden';
            }

            $query_args = [
                'post_type'      => 'proyecto',
                'post_status'    => 'publish',
                'posts_per_page' => $posts_per_page,
                'orderby'        => $orderby,
                'order'          => $order,
                'tax_query'      => [
                    [
                        'taxonomy' => 'servicio',
                        'field'    => 'slug',
                        'terms'    => $term->slug,
                    ],
                ],
            ];

            if ( $year_min || $year_max ) {
                $meta_query = [
                    'key'     => 'anima_anio',
                    'compare' => '>=',
                    'type'    => 'NUMERIC',
                    'value'   => $year_min,
                ];

                if ( $year_min && $year_max && $year_max >= $year_min ) {
                    $query_args['meta_query'] = [
                        [
                            'key'     => 'anima_anio',
                            'value'   => [ $year_min, $year_max ],
                            'type'    => 'NUMERIC',
                            'compare' => 'BETWEEN',
                        ],
                    ];
                } elseif ( $year_min ) {
                    $query_args['meta_query'] = [ $meta_query ];
                } elseif ( $year_max ) {
                    $query_args['meta_query'] = [
                        [
                            'key'     => 'anima_anio',
                            'value'   => $year_max,
                            'compare' => '<=',
                            'type'    => 'NUMERIC',
                        ],
                    ];
                }
            }

            if ( ! empty( $search ) ) {
                $query_args['s'] = $search;
            }

            if ( 'meta_value' === $orderby ) {
                $query_args['meta_key'] = 'anima_anio';
            }

            $panel_attrs['data-query']     = wp_json_encode( $this->build_query_payload( $term->slug, $settings ) );
            $panel_attrs['data-card']      = wp_json_encode( $card_settings );
            $panel_attrs['data-layout']    = $layout;
            $panel_attrs['data-columns']   = wp_json_encode( $data_columns );
            $panel_attrs['data-carousel']  = wp_json_encode( $carousel );

            printf( '<div %s>', $this->build_html_attributes( $panel_attrs ) );

            echo '<div class="anima-tabs__panel-inner" data-layout="' . esc_attr( $layout ) . '">';

            if ( $selected || ! $ajax_enabled ) {
                $query = new WP_Query( $query_args );
                if ( $query->have_posts() ) {
                    $posts = $query->posts;
                    echo $this->render_layout_wrapper( $layout, ProjectCardRenderer::render_cards( $posts, $card_settings ), $data_columns, $carousel );
                } else {
                    echo '<div class="an-empty" role="status">' . esc_html__( 'No hay proyectos disponibles en este servicio.', 'anima-engine' ) . '</div>';
                }
                wp_reset_postdata();
            } else {
                echo $this->render_placeholder( $posts_per_page, $layout, $data_columns, $carousel );
            }

            echo '</div>';
            echo '</div>';
        }
        echo '</div>';
        echo '</div>';
    }

    protected function build_query_payload( string $servicio, array $settings ): array {
        return [
            'servicio'    => $servicio,
            'per_page'    => absint( $settings['posts_per_page'] ?? 6 ),
            'layout'      => $this->sanitize_layout( $settings['layout_mode'] ?? 'grid' ),
            'orderby'     => $this->sanitize_orderby( $settings['orderby'] ?? 'date' ),
            'order'       => $this->sanitize_order( $settings['order'] ?? 'DESC' ),
            'year_min'    => isset( $settings['year_min'] ) ? absint( $settings['year_min'] ) : 0,
            'year_max'    => isset( $settings['year_max'] ) ? absint( $settings['year_max'] ) : 0,
            'search'      => sanitize_text_field( $settings['search_keyword'] ?? '' ),
            'card'        => [
                'show_image'     => $settings['show_image'] ?? 'yes',
                'show_client'    => $settings['show_client'] ?? 'yes',
                'show_year'      => $settings['show_year'] ?? 'yes',
                'show_excerpt'   => $settings['show_excerpt'] ?? 'yes',
                'show_stack'     => $settings['show_stack'] ?? 'yes',
                'show_kpis'      => $settings['show_kpis'] ?? 'yes',
                'excerpt_length' => absint( $settings['excerpt_length'] ?? 26 ),
                'kpi_limit'      => absint( $settings['kpi_limit'] ?? 3 ),
                'button_text'    => $settings['button_text'] ?? __( 'Ver caso', 'anima-engine' ),
            ],
            'carousel'    => $this->build_carousel_settings( $settings ),
            'columns'     => $this->build_responsive_columns( $settings ),
        ];
    }

    protected function build_responsive_columns( array $settings ): array {
        return [
            'desktop' => absint( $settings['columns_desktop'] ?? 3 ),
            'tablet'  => absint( $settings['columns_tablet'] ?? 2 ),
            'mobile'  => absint( $settings['columns_mobile'] ?? 1 ),
            'gap'     => $settings['grid_gap'] ?? [ 'size' => 28, 'unit' => 'px' ],
        ];
    }

    protected function build_carousel_settings( array $settings ): array {
        return [
            'desktop'   => absint( $settings['carousel_slides'] ?? 3 ),
            'tablet'    => absint( $settings['carousel_slides_tablet'] ?? 2 ),
            'mobile'    => absint( $settings['carousel_slides_mobile'] ?? 1 ),
            'autoplay'  => 'yes' === ( $settings['carousel_autoplay'] ?? '' ),
            'loop'      => 'yes' === ( $settings['carousel_loop'] ?? '' ),
            'speed'     => absint( $settings['carousel_speed'] ?? 600 ),
            'navigation'=> $settings['carousel_navigation'] ?? 'arrows-dots',
        ];
    }

    protected function render_layout_wrapper( string $layout, string $content, array $columns, array $carousel ): string {
        return ProjectCardRenderer::wrap_with_layout( $layout, $content, $columns, $carousel );
    }

    protected function wrap_carousel_slides( string $content ): string {
        if ( '' === trim( $content ) ) {
            return '';
        }

        $dom = new \DOMDocument( '1.0', 'UTF-8' );
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput       = false;

        $html = '<div>' . $content . '</div>';
        if ( false === @$dom->loadHTML( $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD ) ) {
            return '<div class="swiper-slide">' . $content . '</div>';
        }

        $wrapper = $dom->getElementsByTagName( 'div' )->item( 0 );
        if ( ! $wrapper ) {
            return '<div class="swiper-slide">' . $content . '</div>';
        }

        $slides = [];
        foreach ( $wrapper->childNodes as $child ) {
            $html_fragment = $dom->saveHTML( $child );
            if ( null === $html_fragment ) {
                continue;
            }
            $slides[] = '<div class="swiper-slide">' . $html_fragment . '</div>';
        }

        return implode( '', $slides );
    }

    protected function render_placeholder( int $count, string $layout, array $columns, array $carousel ): string {
        $count = max( 1, min( 6, $count ) );
        $items = [];
        for ( $i = 0; $i < $count; $i++ ) {
            $items[] = '<div class="an-card an-card--skeleton"><div class="an-card__media"></div><div class="an-card__body"><span class="an-skeleton an-skeleton--title"></span><span class="an-skeleton an-skeleton--text"></span><span class="an-skeleton an-skeleton--text"></span></div></div>';
        }

        $content = implode( '', $items );

        $output = ProjectCardRenderer::wrap_with_layout( $layout, $content, $columns, $carousel );

        if ( 'carousel' === $layout ) {
            return str_replace( 'anima-carousel swiper', 'anima-carousel swiper is-loading', $output );
        }

        return preg_replace( '/class="([^"]*an-grid[^"]*)"/', 'class="$1 an-grid--skeleton"', (string) $output, 1 ) ?: $output;
    }

    protected function sanitize_layout( string $layout ): string {
        $allowed = [ 'grid', 'masonry', 'carousel' ];
        $layout  = sanitize_text_field( $layout );

        return in_array( $layout, $allowed, true ) ? $layout : 'grid';
    }

    protected function sanitize_orderby( string $orderby ): string {
        $allowed = [ 'date', 'title', 'meta_value', 'rand' ];
        $value   = sanitize_text_field( $orderby );

        return in_array( $value, $allowed, true ) ? $value : 'date';
    }

    protected function sanitize_order( string $order ): string {
        $value = strtoupper( sanitize_text_field( $order ) );

        return in_array( $value, [ 'ASC', 'DESC' ], true ) ? $value : 'DESC';
    }

    /**
     * @param array<int, WP_Term> $settings_terms
     *
     * @return array<int, WP_Term>
     */
    protected function resolve_terms( array $settings ): array {
        $selected = $settings['servicio_terms'] ?? [];

        if ( empty( $selected ) ) {
            $terms = get_terms(
                [
                    'taxonomy'   => 'servicio',
                    'hide_empty' => true,
                ]
            );
            if ( is_wp_error( $terms ) || empty( $terms ) ) {
                return [];
            }

            return is_array( $terms ) ? $terms : [];
        }

        $terms = get_terms(
            [
                'taxonomy'   => 'servicio',
                'hide_empty' => true,
                'slug'       => $selected,
            ]
        );

        if ( is_wp_error( $terms ) || empty( $terms ) ) {
            return [];
        }

        return is_array( $terms ) ? $terms : [];
    }

    /**
     * Convierte arreglo de atributos en string HTML.
     *
     * @param array<string, mixed> $attributes Atributos.
     */
    protected function build_html_attributes( array $attributes ): string {
        $pairs = [];
        foreach ( $attributes as $key => $value ) {
            if ( null === $value || '' === $value ) {
                continue;
            }
            if ( true === $value ) {
                $pairs[] = $key;
                continue;
            }

            if ( is_array( $value ) || is_object( $value ) ) {
                $value = wp_json_encode( $value );
            }

            $pairs[] = sprintf( '%s="%s"', esc_attr( $key ), esc_attr( (string) $value ) );
        }

        return implode( ' ', $pairs );
    }
}
