<?php
add_action( 'rest_api_init', function () {
    foreach ( array_keys( anima_core_meta_fields() ) as $field ) {
        register_rest_field(
            'project',
            "anima_{$field}",
            [
                'get_callback'    => function ( $object ) use ( $field ) {
                    return get_post_meta( $object['id'], "anima_{$field}", true );
                },
                'update_callback' => function ( $value, $post ) use ( $field ) {
                    update_post_meta( $post->ID, "anima_{$field}", sanitize_text_field( $value ) );
                },
                'schema'          => [
                    'type' => 'string',
                ],
            ]
        );
    }
} );
