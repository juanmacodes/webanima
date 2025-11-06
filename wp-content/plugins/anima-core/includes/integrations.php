<?php
/**
 * Hooks reservados para integraciones futuras.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function anima_register_graphql_hooks() {
    if ( ! function_exists( 'register_graphql_field' ) ) {
        return;
    }

    // Placeholder para exponer metacampos personalizados en WPGraphQL.
    // Ejemplo:
    // register_graphql_field( 'Curso', 'instructores', array( ... ) );
}

function anima_register_buddypress_hooks() {
    if ( ! function_exists( 'buddypress' ) || ! function_exists( 'bp_core_new_nav_item' ) ) {
        return;
    }

    add_action( 'bp_setup_nav', 'anima_register_buddypress_portfolio_nav', 100 );
}

function anima_register_buddypress_portfolio_nav() {
    if ( ! function_exists( 'buddypress' ) || ! function_exists( 'bp_core_new_nav_item' ) ) {
        return;
    }

    $bp = buddypress();

    if ( isset( $bp->members, $bp->members->nav ) && method_exists( $bp->members->nav, 'get' ) ) {
        if ( $bp->members->nav->get( 'anima-portafolio' ) ) {
            return;
        }
    }

    bp_core_new_nav_item(
        array(
            'name'                => __( 'Portafolio', 'anima-core' ),
            'slug'                => 'anima-portafolio',
            'screen_function'     => 'anima_buddypress_portfolio_screen',
            'default_subnav_slug' => 'anima-portafolio',
            'position'            => 85,
            'item_css_id'         => 'anima-portafolio',
        )
    );
}

function anima_buddypress_portfolio_screen() {
    add_action( 'bp_template_content', 'anima_buddypress_portfolio_content' );
    bp_core_load_template( 'members/single/plugins' );
}

function anima_buddypress_portfolio_content() {
    $content = '<p>' . esc_html__( 'Tu portafolio aparecerá aquí con los cursos, proyectos y logros destacados.', 'anima-core' ) . '</p>';
    $content = apply_filters( 'anima_buddypress_portfolio_placeholder', $content );

    echo '<div class="anima-bp-portafolio">' . wp_kses_post( $content ) . '</div>';
}
