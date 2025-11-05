<?php

add_action(
    'rest_api_init',
    static function (): void {
        foreach ( array_keys( anima_core_meta_fields() ) as $field ) {
            register_rest_field(
                'project',
                "anima_{$field}",
                [
                    'get_callback'    => static function ( array $object, string $field_name, \WP_REST_Request $request ) use ( $field ) {
                        $post_id = isset( $object['id'] ) ? (int) $object['id'] : 0;

                        if ( $post_id <= 0 ) {
                            return '';
                        }

                        $value = get_post_meta( $post_id, "anima_{$field}", true );

                        return is_scalar( $value ) ? (string) $value : '';
                    },
                    'update_callback' => static function ( $value, $post, string $field_name ) use ( $field ) {
                        $post_id = 0;

                        if ( $post instanceof \WP_Post ) {
                            $post_id = $post->ID;
                        } elseif ( is_array( $post ) && isset( $post['ID'] ) ) {
                            $post_id = (int) $post['ID'];
                        }

                        if ( $post_id <= 0 ) {
                            return false;
                        }

                        if ( null === $value || '' === $value ) {
                            delete_post_meta( $post_id, "anima_{$field}" );

                            return true;
                        }

                        if ( is_array( $value ) ) {
                            $value = reset( $value );
                        }

                        $sanitized = sanitize_text_field( wp_unslash( (string) $value ) );
                        update_post_meta( $post_id, "anima_{$field}", $sanitized );

                        return true;
                    },
                    'schema'          => [
                        'type' => 'string',
                    ],
                ]
            );
        }
    }
);
