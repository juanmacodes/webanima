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
    if ( ! function_exists( 'buddypress' ) ) {
        return;
    }

    // Placeholder para conectar los CPTs con la actividad de BuddyPress.
    // add_action( 'bp_activity_add', 'anima_registrar_actividad_curso' );
}
