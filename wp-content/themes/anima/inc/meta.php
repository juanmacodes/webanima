<?php
/**
 * Registro de metadatos para CPT.
 *
 * @package Anima
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function anima_sanitize_text_field_list( $value ): array {
    if ( empty( $value ) ) {
        return [];
    }

    if ( is_string( $value ) ) {
        $decoded = json_decode( $value, true );
        $value   = is_array( $decoded ) ? $decoded : [];
    }

    if ( ! is_array( $value ) ) {
        return [];
    }

    return array_values(
        array_map(
            static function ( $item ): array {
                if ( ! is_array( $item ) ) {
                    return [];
                }
                $label = isset( $item['label'] ) ? sanitize_text_field( wp_unslash( (string) $item['label'] ) ) : '';
                $val   = isset( $item['value'] ) ? sanitize_text_field( wp_unslash( (string) $item['value'] ) ) : '';
                return [
                    'label' => $label,
                    'value' => $val,
                ];
            },
            $value
        )
    );
}

function anima_sanitize_gallery( $value ): array {
    if ( empty( $value ) ) {
        return [];
    }

    if ( is_string( $value ) ) {
        $decoded = json_decode( $value, true );
        $value   = is_array( $decoded ) ? $decoded : [];
    }

    if ( ! is_array( $value ) ) {
        return [];
    }

    return array_values(
        array_filter(
            array_map(
                static function ( $item ) {
                    return absint( $item );
                },
                $value
            ),
            static function ( $item ) {
                return $item > 0;
            }
        )
    );
}

function anima_register_meta_fields(): void {
    register_post_meta(
        'proyecto',
        'anima_cliente',
        [
            'type'              => 'string',
            'single'            => true,
            'show_in_rest'      => true,
            'sanitize_callback' => 'sanitize_text_field',
            'auth_callback'     => '__return_true',
        ]
    );

    register_post_meta(
        'proyecto',
        'anima_anio',
        [
            'type'              => 'integer',
            'single'            => true,
            'show_in_rest'      => true,
            'sanitize_callback' => 'absint',
            'auth_callback'     => '__return_true',
        ]
    );

    register_post_meta(
        'proyecto',
        'anima_stack',
        [
            'type'              => 'string',
            'single'            => true,
            'show_in_rest'      => true,
            'sanitize_callback' => 'sanitize_text_field',
            'auth_callback'     => '__return_true',
        ]
    );

    register_post_meta(
        'proyecto',
        'anima_kpis',
        [
            'type'              => 'array',
            'single'            => true,
            'show_in_rest'      => [
                'schema' => [
                    'type'  => 'array',
                    'items' => [
                        'type'       => 'object',
                        'properties' => [
                            'label' => [ 'type' => 'string' ],
                            'value' => [ 'type' => 'string' ],
                        ],
                    ],
                ],
            ],
            'sanitize_callback' => 'anima_sanitize_text_field_list',
            'auth_callback'     => '__return_true',
        ]
    );

    register_post_meta(
        'proyecto',
        'anima_galeria',
        [
            'type'              => 'array',
            'single'            => true,
            'show_in_rest'      => [
                'schema' => [
                    'type'  => 'array',
                    'items' => [ 'type' => 'integer' ],
                ],
            ],
            'sanitize_callback' => 'anima_sanitize_gallery',
            'auth_callback'     => '__return_true',
        ]
    );

    register_post_meta(
        'curso',
        'anima_duracion',
        [
            'type'              => 'number',
            'single'            => true,
            'show_in_rest'      => true,
            'sanitize_callback' => 'floatval',
            'auth_callback'     => '__return_true',
        ]
    );

    register_post_meta(
        'curso',
        'anima_precio',
        [
            'type'              => 'number',
            'single'            => true,
            'show_in_rest'      => true,
            'sanitize_callback' => 'floatval',
            'auth_callback'     => '__return_true',
        ]
    );

    register_post_meta(
        'curso',
        'anima_modalidad_extra',
        [
            'type'              => 'string',
            'single'            => true,
            'show_in_rest'      => true,
            'sanitize_callback' => 'sanitize_text_field',
            'auth_callback'     => '__return_true',
        ]
    );

    register_post_meta(
        'curso',
        'anima_resumen',
        [
            'type'              => 'string',
            'single'            => true,
            'show_in_rest'      => true,
            'sanitize_callback' => 'wp_kses_post',
            'auth_callback'     => '__return_true',
        ]
    );

    register_post_meta(
        'curso',
        'anima_temario',
        [
            'type'              => 'array',
            'single'            => true,
            'show_in_rest'      => [
                'schema' => [
                    'type'  => 'array',
                    'items' => [
                        'type'       => 'object',
                        'properties' => [
                            'titulo'    => [ 'type' => 'string' ],
                            'lecciones' => [ 'type' => 'array', 'items' => [ 'type' => 'string' ] ],
                        ],
                    ],
                ],
            ],
            'sanitize_callback' => static function ( $value ): array {
                if ( empty( $value ) ) {
                    return [];
                }
                if ( is_string( $value ) ) {
                    $decoded = json_decode( $value, true );
                    $value   = is_array( $decoded ) ? $decoded : [];
                }
                if ( ! is_array( $value ) ) {
                    return [];
                }
                return array_values(
                    array_map(
                        static function ( $item ): array {
                            if ( ! is_array( $item ) ) {
                                return [ 'titulo' => '', 'lecciones' => [] ];
                            }
                            $titulo    = isset( $item['titulo'] ) ? sanitize_text_field( wp_unslash( (string) $item['titulo'] ) ) : '';
                            $lecciones = [];
                            if ( isset( $item['lecciones'] ) && is_array( $item['lecciones'] ) ) {
                                foreach ( $item['lecciones'] as $lesson ) {
                                    $lecciones[] = sanitize_text_field( wp_unslash( (string) $lesson ) );
                                }
                            }
                            return [
                                'titulo'    => $titulo,
                                'lecciones' => $lecciones,
                            ];
                        },
                        $value
                    )
                );
            },
            'auth_callback'     => '__return_true',
        ]
    );

    register_post_meta(
        'curso',
        'anima_requisitos',
        [
            'type'              => 'string',
            'single'            => true,
            'show_in_rest'      => true,
            'sanitize_callback' => 'wp_kses_post',
            'auth_callback'     => '__return_true',
        ]
    );

    register_post_meta(
        'curso',
        'anima_instructores',
        [
            'type'              => 'array',
            'single'            => true,
            'show_in_rest'      => [
                'schema' => [
                    'type'  => 'array',
                    'items' => [
                        'type'       => 'object',
                        'properties' => [
                            'nombre' => [ 'type' => 'string' ],
                            'bio'    => [ 'type' => 'string' ],
                            'avatar' => [ 'type' => 'integer' ],
                        ],
                    ],
                ],
            ],
            'sanitize_callback' => static function ( $value ): array {
                if ( empty( $value ) ) {
                    return [];
                }
                if ( is_string( $value ) ) {
                    $decoded = json_decode( $value, true );
                    $value   = is_array( $decoded ) ? $decoded : [];
                }
                if ( ! is_array( $value ) ) {
                    return [];
                }
                return array_values(
                    array_map(
                        static function ( $item ): array {
                            if ( ! is_array( $item ) ) {
                                return [ 'nombre' => '', 'bio' => '', 'avatar' => 0 ];
                            }
                            return [
                                'nombre' => isset( $item['nombre'] ) ? sanitize_text_field( wp_unslash( (string) $item['nombre'] ) ) : '',
                                'bio'    => isset( $item['bio'] ) ? wp_kses_post( $item['bio'] ) : '',
                                'avatar' => isset( $item['avatar'] ) ? absint( $item['avatar'] ) : 0,
                            ];
                        },
                        $value
                    )
                );
            },
            'auth_callback'     => '__return_true',
        ]
    );

    register_post_meta(
        'curso',
        'anima_fechas',
        [
            'type'              => 'array',
            'single'            => true,
            'show_in_rest'      => [
                'schema' => [
                    'type'  => 'array',
                    'items' => [ 'type' => 'string' ],
                ],
            ],
            'sanitize_callback' => static function ( $value ): array {
                if ( empty( $value ) ) {
                    return [];
                }
                if ( is_string( $value ) ) {
                    $decoded = json_decode( $value, true );
                    $value   = is_array( $decoded ) ? $decoded : [];
                }
                if ( ! is_array( $value ) ) {
                    return [];
                }
                return array_values(
                    array_map(
                        static function ( $item ): string {
                            return sanitize_text_field( wp_unslash( (string) $item ) );
                        },
                        $value
                    )
                );
            },
            'auth_callback'     => '__return_true',
        ]
    );
}
add_action( 'init', 'anima_register_meta_fields' );

function anima_require_featured_image_notice(): void {
    $screen = get_current_screen();
    if ( ! $screen ) {
        return;
    }

    if ( in_array( $screen->post_type, [ 'proyecto', 'curso' ], true ) && 'post' === $screen->base ) {
        $post_id = get_the_ID();
        if ( $post_id && ! has_post_thumbnail( $post_id ) ) {
            echo '<div class="notice notice-warning"><p>' . esc_html__( 'Recuerda añadir una imagen destacada antes de publicar.', 'anima' ) . '</p></div>';
        }
    }
}
add_action( 'admin_notices', 'anima_require_featured_image_notice' );

function anima_require_featured_image_on_publish( $maybe_empty, $postarr ) {
    if ( in_array( $postarr['post_type'], [ 'proyecto', 'curso' ], true ) && 'publish' === $postarr['post_status'] ) {
        if ( empty( $postarr['ID'] ) || ! has_post_thumbnail( (int) $postarr['ID'] ) ) {
            return new WP_Error( 'anima_no_featured_image', __( 'Es necesaria una imagen destacada para publicar.', 'anima' ) );
        }
    }
    return $maybe_empty;
}
add_filter( 'wp_insert_post_empty_content', 'anima_require_featured_image_on_publish', 10, 2 );
