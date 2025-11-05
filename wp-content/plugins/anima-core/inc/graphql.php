<?php
/**
 * WPGraphQL integration.
 *
 * @package anima-core
 */

defined( 'ABSPATH' ) || exit;

add_action( 'graphql_register_types', 'anima_core_register_graphql_types' );

/**
 * Register custom GraphQL types and fields.
 */
function anima_core_register_graphql_types(): void {
    if ( ! function_exists( 'register_graphql_field' ) ) {
        return;
    }

    register_graphql_object_type(
        'AnimaKPI',
        [
            'fields' => [
                'label' => [
                    'type'        => 'String',
                    'description' => __( 'Etiqueta del KPI.', 'anima-core' ),
                ],
                'value' => [
                    'type'        => 'String',
                    'description' => __( 'Valor del KPI.', 'anima-core' ),
                ],
            ],
        ]
    );

    register_graphql_object_type(
        'AnimaSyllabusModule',
        [
            'fields' => [
                'title' => [
                    'type'        => 'String',
                    'description' => __( 'Título del módulo.', 'anima-core' ),
                ],
                'lessons' => [
                    'type'        => [ 'list_of' => 'String' ],
                    'description' => __( 'Lecciones incluidas en el módulo.', 'anima-core' ),
                ],
            ],
        ]
    );

    register_graphql_object_type(
        'AnimaInstructor',
        [
            'fields' => [
                'name' => [
                    'type'        => 'String',
                    'description' => __( 'Nombre del instructor.', 'anima-core' ),
                ],
                'bio'  => [
                    'type'        => 'String',
                    'description' => __( 'Biografía breve del instructor.', 'anima-core' ),
                ],
                'avatarUrl' => [
                    'type'        => 'String',
                    'description' => __( 'URL del avatar del instructor.', 'anima-core' ),
                ],
            ],
        ]
    );

    anima_core_register_proyecto_graphql_fields();
    anima_core_register_curso_graphql_fields();
}

/**
 * Register Proyecto GraphQL fields.
 */
function anima_core_register_proyecto_graphql_fields(): void {
    register_graphql_field(
        'Proyecto',
        'animaClient',
        [
            'type'        => 'String',
            'description' => __( 'Cliente asociado al proyecto.', 'anima-core' ),
            'resolve'     => static fn( $post ) => get_post_meta( $post->ID, 'anima_client', true ),
        ]
    );

    register_graphql_field(
        'Proyecto',
        'animaYear',
        [
            'type'        => 'Int',
            'description' => __( 'Año del proyecto.', 'anima-core' ),
            'resolve'     => static fn( $post ) => (int) get_post_meta( $post->ID, 'anima_year', true ),
        ]
    );

    register_graphql_field(
        'Proyecto',
        'animaStack',
        [
            'type'        => [ 'list_of' => 'String' ],
            'description' => __( 'Stack tecnológico del proyecto.', 'anima-core' ),
            'resolve'     => static function ( $post ): array {
                $value = anima_core_get_meta( $post->ID, 'anima_stack' );
                if ( ! is_array( $value ) ) {
                    return [];
                }

                return array_values(
                    array_map(
                        static function ( $item ): string {
                            if ( is_array( $item ) ) {
                                if ( isset( $item['item'] ) ) {
                                    $item = $item['item'];
                                } elseif ( isset( $item['value'] ) ) {
                                    $item = $item['value'];
                                } elseif ( ! empty( $item ) ) {
                                    $item = reset( $item );
                                }
                            }

                            return (string) $item;
                        },
                        $value
                    )
                );
            },
        ]
    );

    register_graphql_field(
        'Proyecto',
        'animaKpis',
        [
            'type'        => [ 'list_of' => 'AnimaKPI' ],
            'description' => __( 'Resultados clave del proyecto.', 'anima-core' ),
            'resolve'     => static function ( $post ): array {
                $items = anima_core_get_meta( $post->ID, 'anima_kpis' );
                if ( ! is_array( $items ) ) {
                    return [];
                }

                return array_values(
                    array_map(
                        static function ( $item ): array {
                            $label = '';
                            $value = '';

                            if ( is_array( $item ) ) {
                                if ( isset( $item['label'] ) ) {
                                    $label = (string) $item['label'];
                                }
                                if ( isset( $item['value'] ) ) {
                                    $value = (string) $item['value'];
                                }

                                if ( '' === $label && isset( $item['title'] ) ) {
                                    $label = (string) $item['title'];
                                }
                                if ( '' === $value && isset( $item['result'] ) ) {
                                    $value = (string) $item['result'];
                                }

                                if ( '' === $label || '' === $value ) {
                                    $values = array_values( $item );
                                    if ( '' === $label && isset( $values[0] ) ) {
                                        $label = (string) $values[0];
                                    }
                                    if ( '' === $value && isset( $values[1] ) ) {
                                        $value = (string) $values[1];
                                    }
                                }
                            }

                            return [
                                'label' => $label,
                                'value' => $value,
                            ];
                        },
                        $items
                    )
                );
            },
        ]
    );

    register_graphql_field(
        'Proyecto',
        'animaGallery',
        [
            'type'        => [ 'list_of' => 'Int' ],
            'description' => __( 'Galería de IDs multimedia.', 'anima-core' ),
            'resolve'     => static function ( $post ): array {
                $value = anima_core_get_meta( $post->ID, 'anima_gallery' );

                if ( ! is_array( $value ) ) {
                    return [];
                }

                return array_values( array_map( 'intval', $value ) );
            },
        ]
    );

    register_graphql_field(
        'Proyecto',
        'animaVideoUrl',
        [
            'type'        => 'String',
            'description' => __( 'Video destacado del proyecto.', 'anima-core' ),
            'resolve'     => static fn( $post ) => get_post_meta( $post->ID, 'anima_video_url', true ),
        ]
    );
}

/**
 * Register Curso GraphQL fields.
 */
function anima_core_register_curso_graphql_fields(): void {
    register_graphql_field(
        'Curso',
        'animaDurationHours',
        [
            'type'        => 'Int',
            'description' => __( 'Duración total del curso en horas.', 'anima-core' ),
            'resolve'     => static fn( $post ) => (int) get_post_meta( $post->ID, 'anima_duration_hours', true ),
        ]
    );

    register_graphql_field(
        'Curso',
        'animaPrice',
        [
            'type'        => 'Float',
            'description' => __( 'Precio del curso.', 'anima-core' ),
            'resolve'     => static fn( $post ) => (float) get_post_meta( $post->ID, 'anima_price', true ),
        ]
    );

    register_graphql_field(
        'Curso',
        'animaSyllabus',
        [
            'type'        => [ 'list_of' => 'AnimaSyllabusModule' ],
            'description' => __( 'Contenido del curso.', 'anima-core' ),
            'resolve'     => static function ( $post ): array {
                $items = anima_core_get_meta( $post->ID, 'anima_syllabus' );
                if ( ! is_array( $items ) ) {
                    return [];
                }

                return array_values(
                    array_map(
                        static function ( $item ): array {
                            $title   = '';
                            $lessons = [];

                            if ( is_array( $item ) ) {
                                if ( isset( $item['title'] ) ) {
                                    $title = (string) $item['title'];
                                } elseif ( isset( $item['module'] ) ) {
                                    $title = (string) $item['module'];
                                } elseif ( ! empty( $item ) ) {
                                    $first = array_values( $item );
                                    $title = isset( $first[0] ) ? (string) $first[0] : '';
                                }

                                if ( isset( $item['lessons'] ) && is_array( $item['lessons'] ) ) {
                                    $lessons_source = $item['lessons'];
                                } else {
                                    $lessons_source = $item['items'] ?? [];
                                }

                                if ( is_array( $lessons_source ) ) {
                                    foreach ( $lessons_source as $lesson ) {
                                        if ( is_array( $lesson ) ) {
                                            if ( isset( $lesson['lesson'] ) ) {
                                                $lessons[] = (string) $lesson['lesson'];
                                            } elseif ( isset( $lesson['title'] ) ) {
                                                $lessons[] = (string) $lesson['title'];
                                            } elseif ( ! empty( $lesson ) ) {
                                                $lessons[] = (string) reset( $lesson );
                                            }
                                        } elseif ( null !== $lesson ) {
                                            $lessons[] = (string) $lesson;
                                        }
                                    }
                                }
                            }

                            return [
                                'title'   => $title,
                                'lessons' => $lessons,
                            ];
                        },
                        $items
                    )
                );
            },
        ]
    );

    register_graphql_field(
        'Curso',
        'animaRequirements',
        [
            'type'        => 'String',
            'description' => __( 'Requisitos del curso.', 'anima-core' ),
            'resolve'     => static fn( $post ) => get_post_meta( $post->ID, 'anima_requirements', true ),
        ]
    );

    register_graphql_field(
        'Curso',
        'animaInstructors',
        [
            'type'        => [ 'list_of' => 'AnimaInstructor' ],
            'description' => __( 'Equipo docente.', 'anima-core' ),
            'resolve'     => static function ( $post ): array {
                $items = anima_core_get_meta( $post->ID, 'anima_instructors' );
                if ( ! is_array( $items ) ) {
                    return [];
                }

                return array_values(
                    array_map(
                        static function ( $item ): array {
                            $name  = '';
                            $bio   = '';
                            $avatar = '';

                            if ( is_array( $item ) ) {
                                if ( isset( $item['name'] ) ) {
                                    $name = (string) $item['name'];
                                }
                                if ( isset( $item['bio'] ) ) {
                                    $bio = (string) $item['bio'];
                                }
                                if ( isset( $item['avatar_url'] ) ) {
                                    $avatar = (string) $item['avatar_url'];
                                } elseif ( isset( $item['avatarUrl'] ) ) {
                                    $avatar = (string) $item['avatarUrl'];
                                }

                                if ( '' === $name && ! empty( $item ) ) {
                                    $first = array_values( $item );
                                    $name  = isset( $first[0] ) ? (string) $first[0] : '';
                                }
                            }

                            return [
                                'name'      => $name,
                                'bio'       => $bio,
                                'avatarUrl' => $avatar,
                            ];
                        },
                        $items
                    )
                );
            },
        ]
    );

    register_graphql_field(
        'Curso',
        'animaUpcomingDates',
        [
            'type'        => [ 'list_of' => 'String' ],
            'description' => __( 'Próximas fechas del curso.', 'anima-core' ),
            'resolve'     => static function ( $post ): array {
                $items = anima_core_get_meta( $post->ID, 'anima_upcoming_dates' );
                if ( ! is_array( $items ) ) {
                    return [];
                }

                return array_values(
                    array_map(
                        static function ( $item ): string {
                            if ( is_array( $item ) ) {
                                if ( isset( $item['date'] ) ) {
                                    $item = $item['date'];
                                } elseif ( ! empty( $item ) ) {
                                    $item = reset( $item );
                                }
                            }

                            return (string) $item;
                        },
                        $items
                    )
                );
            },
        ]
    );
}

add_action( 'acf/init', 'anima_core_register_acf_fields' );

/**
 * Register ACF field groups when ACF is available.
 */
function anima_core_register_acf_fields(): void {
    if ( ! function_exists( 'acf_add_local_field_group' ) ) {
        return;
    }

    acf_add_local_field_group(
        [
            'key'             => 'group_anima_proyecto',
            'title'           => __( 'Proyecto — Datos', 'anima-core' ),
            'show_in_graphql' => 1,
            'location'        => [
                [
                    [
                        'param'    => 'post_type',
                        'operator' => '==',
                        'value'    => 'proyecto',
                    ],
                ],
            ],
            'fields'          => [
                [
                    'key'             => 'field_anima_client',
                    'label'           => __( 'Cliente', 'anima-core' ),
                    'name'            => 'anima_client',
                    'type'            => 'text',
                    'show_in_graphql' => 1,
                ],
                [
                    'key'             => 'field_anima_year',
                    'label'           => __( 'Año', 'anima-core' ),
                    'name'            => 'anima_year',
                    'type'            => 'number',
                    'show_in_graphql' => 1,
                ],
                [
                    'key'             => 'field_anima_stack',
                    'label'           => __( 'Stack', 'anima-core' ),
                    'name'            => 'anima_stack',
                    'type'            => 'repeater',
                    'show_in_graphql' => 1,
                    'sub_fields'      => [
                        [
                            'key'             => 'field_anima_stack_item',
                            'label'           => __( 'Tecnología', 'anima-core' ),
                            'name'            => 'item',
                            'type'            => 'text',
                            'show_in_graphql' => 1,
                        ],
                    ],
                ],
                [
                    'key'             => 'field_anima_kpis',
                    'label'           => __( 'KPIs', 'anima-core' ),
                    'name'            => 'anima_kpis',
                    'type'            => 'repeater',
                    'show_in_graphql' => 1,
                    'sub_fields'      => [
                        [
                            'key'             => 'field_anima_kpi_label',
                            'label'           => __( 'Etiqueta', 'anima-core' ),
                            'name'            => 'label',
                            'type'            => 'text',
                            'show_in_graphql' => 1,
                        ],
                        [
                            'key'             => 'field_anima_kpi_value',
                            'label'           => __( 'Valor', 'anima-core' ),
                            'name'            => 'value',
                            'type'            => 'text',
                            'show_in_graphql' => 1,
                        ],
                    ],
                ],
                [
                    'key'             => 'field_anima_gallery',
                    'label'           => __( 'Galería', 'anima-core' ),
                    'name'            => 'anima_gallery',
                    'type'            => 'gallery',
                    'show_in_graphql' => 1,
                ],
                [
                    'key'             => 'field_anima_video_url',
                    'label'           => __( 'Video URL', 'anima-core' ),
                    'name'            => 'anima_video_url',
                    'type'            => 'url',
                    'show_in_graphql' => 1,
                ],
            ],
        ]
    );

    acf_add_local_field_group(
        [
            'key'             => 'group_anima_curso',
            'title'           => __( 'Curso — Datos', 'anima-core' ),
            'show_in_graphql' => 1,
            'location'        => [
                [
                    [
                        'param'    => 'post_type',
                        'operator' => '==',
                        'value'    => 'curso',
                    ],
                ],
            ],
            'fields'          => [
                [
                    'key'             => 'field_anima_duration_hours',
                    'label'           => __( 'Duración (horas)', 'anima-core' ),
                    'name'            => 'anima_duration_hours',
                    'type'            => 'number',
                    'show_in_graphql' => 1,
                ],
                [
                    'key'             => 'field_anima_price',
                    'label'           => __( 'Precio', 'anima-core' ),
                    'name'            => 'anima_price',
                    'type'            => 'number',
                    'show_in_graphql' => 1,
                ],
                [
                    'key'             => 'field_anima_syllabus',
                    'label'           => __( 'Syllabus', 'anima-core' ),
                    'name'            => 'anima_syllabus',
                    'type'            => 'repeater',
                    'show_in_graphql' => 1,
                    'sub_fields'      => [
                        [
                            'key'             => 'field_anima_syllabus_title',
                            'label'           => __( 'Título', 'anima-core' ),
                            'name'            => 'title',
                            'type'            => 'text',
                            'show_in_graphql' => 1,
                        ],
                        [
                            'key'             => 'field_anima_syllabus_lessons',
                            'label'           => __( 'Lecciones', 'anima-core' ),
                            'name'            => 'lessons',
                            'type'            => 'repeater',
                            'show_in_graphql' => 1,
                            'sub_fields'      => [
                                [
                                    'key'             => 'field_anima_syllabus_lesson_item',
                                    'label'           => __( 'Lección', 'anima-core' ),
                                    'name'            => 'lesson',
                                    'type'            => 'text',
                                    'show_in_graphql' => 1,
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'key'             => 'field_anima_requirements',
                    'label'           => __( 'Requisitos', 'anima-core' ),
                    'name'            => 'anima_requirements',
                    'type'            => 'wysiwyg',
                    'show_in_graphql' => 1,
                ],
                [
                    'key'             => 'field_anima_instructors',
                    'label'           => __( 'Instructores', 'anima-core' ),
                    'name'            => 'anima_instructors',
                    'type'            => 'repeater',
                    'show_in_graphql' => 1,
                    'sub_fields'      => [
                        [
                            'key'             => 'field_anima_instructor_name',
                            'label'           => __( 'Nombre', 'anima-core' ),
                            'name'            => 'name',
                            'type'            => 'text',
                            'show_in_graphql' => 1,
                        ],
                        [
                            'key'             => 'field_anima_instructor_bio',
                            'label'           => __( 'Bio', 'anima-core' ),
                            'name'            => 'bio',
                            'type'            => 'textarea',
                            'show_in_graphql' => 1,
                        ],
                        [
                            'key'             => 'field_anima_instructor_avatar',
                            'label'           => __( 'Avatar URL', 'anima-core' ),
                            'name'            => 'avatar_url',
                            'type'            => 'url',
                            'show_in_graphql' => 1,
                        ],
                    ],
                ],
                [
                    'key'             => 'field_anima_upcoming_dates',
                    'label'           => __( 'Próximas fechas', 'anima-core' ),
                    'name'            => 'anima_upcoming_dates',
                    'type'            => 'repeater',
                    'show_in_graphql' => 1,
                    'sub_fields'      => [
                        [
                            'key'             => 'field_anima_upcoming_date',
                            'label'           => __( 'Fecha', 'anima-core' ),
                            'name'            => 'date',
                            'type'            => 'date_picker',
                            'show_in_graphql' => 1,
                        ],
                    ],
                ],
            ],
        ]
    );
}
