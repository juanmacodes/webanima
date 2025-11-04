<?php
/**
 * Marcado Schema.org
 *
 * @package Anima
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action(
    'wp_head',
    static function (): void {
        $schemas = [];

        $organization = [
            '@context' => 'https://schema.org',
            '@type'    => 'Organization',
            'name'     => 'Anima',
            'url'      => home_url( '/' ),
            'logo'     => ANIMA_ASSETS_URL . '/img/logo-anima.svg',
            'sameAs'   => [
                'https://www.linkedin.com/company/anima',
                'mailto:hola@anima.studio',
            ],
        ];

        $schemas[] = $organization;

        $services = [
            [
                '@context'    => 'https://schema.org',
                '@type'       => 'Service',
                'name'        => __( 'Streaming de Avatares', 'anima' ),
                'serviceType' => __( 'Producción de directos con avatares 3D', 'anima' ),
                'url'         => home_url( '/servicios/streaming/' ),
                'provider'    => [ '@type' => 'Organization', 'name' => 'Anima' ],
            ],
            [
                '@context'    => 'https://schema.org',
                '@type'       => 'Service',
                'name'        => __( 'Cabinas Holográficas', 'anima' ),
                'serviceType' => __( 'Instalaciones holográficas en eventos', 'anima' ),
                'url'         => home_url( '/servicios/holograficos/' ),
                'provider'    => [ '@type' => 'Organization', 'name' => 'Anima' ],
            ],
            [
                '@context'    => 'https://schema.org',
                '@type'       => 'Service',
                'name'        => __( 'IA Conversacional', 'anima' ),
                'serviceType' => __( 'Agentes virtuales y personajes con IA', 'anima' ),
                'url'         => home_url( '/servicios/ia/' ),
                'provider'    => [ '@type' => 'Organization', 'name' => 'Anima' ],
            ],
            [
                '@context'    => 'https://schema.org',
                '@type'       => 'Service',
                'name'        => __( 'VR e Interactividad', 'anima' ),
                'serviceType' => __( 'Experiencias inmersivas VR/AR', 'anima' ),
                'url'         => home_url( '/servicios/vr/' ),
                'provider'    => [ '@type' => 'Organization', 'name' => 'Anima' ],
            ],
        ];

        foreach ( $services as $service ) {
            $schemas[] = $service;
        }

        if ( is_singular( 'curso' ) ) {
            $course_id   = get_the_ID();
            $duration    = get_post_meta( $course_id, 'anima_duracion', true );
            $price       = get_post_meta( $course_id, 'anima_precio', true );
            $summary     = get_post_meta( $course_id, 'anima_resumen', true );
            $temario     = get_post_meta( $course_id, 'anima_temario', true );
            $instructor  = get_post_meta( $course_id, 'anima_instructores', true );

            $provider = [
                '@type' => 'Organization',
                'name'  => 'Anima Academy',
                'url'   => home_url( '/cursos/' ),
            ];

            $course_schema = [
                '@context'        => 'https://schema.org',
                '@type'           => 'Course',
                'name'            => get_the_title( $course_id ),
                'description'     => wp_strip_all_tags( $summary ?: get_the_excerpt( $course_id ) ),
                'provider'        => $provider,
                'url'             => get_permalink( $course_id ),
                'courseCode'      => 'CUR-' . $course_id,
                'timeRequired'    => $duration ? sprintf( 'PT%sH', (float) $duration ) : null,
                'offers'          => [
                    '@type'         => 'Offer',
                    'price'         => $price ?: '0',
                    'priceCurrency' => 'EUR',
                    'availability'  => 'https://schema.org/InStock',
                    'url'           => get_permalink( $course_id ),
                ],
            ];

            if ( ! empty( $temario ) && is_array( $temario ) ) {
                $course_schema['hasCourseInstance'] = [];
                foreach ( $temario as $module ) {
                    $course_schema['hasCourseInstance'][] = [
                        '@type'        => 'CourseInstance',
                        'name'         => $module['titulo'] ?? '',
                        'description'  => isset( $module['lecciones'] ) ? implode( ', ', (array) $module['lecciones'] ) : '',
                        'courseMode'   => 'online',
                    ];
                }
            }

            if ( ! empty( $instructor ) && is_array( $instructor ) ) {
                $course_schema['instructor'] = [];
                foreach ( $instructor as $person ) {
                    $course_schema['instructor'][] = [
                        '@type' => 'Person',
                        'name'  => $person['nombre'] ?? '',
                        'description' => wp_strip_all_tags( $person['bio'] ?? '' ),
                    ];
                }
            }

            $schemas[] = array_filter( $course_schema );
        }

        if ( is_page( 'anima-live' ) ) {
            $app_schema = [
                '@context'   => 'https://schema.org',
                '@type'      => 'SoftwareApplication',
                'name'       => 'Anima Live',
                'operatingSystem' => 'Windows, macOS',
                'applicationCategory' => 'https://schema.org/MultimediaApplication',
                'offers'     => [
                    '@type'         => 'Offer',
                    'price'         => '0',
                    'priceCurrency' => 'EUR',
                ],
                'featureList' => [
                    __( 'Rig facial en tiempo real', 'anima' ),
                    __( 'Texto a voz y voz propia', 'anima' ),
                    __( 'Escenas y overlays listos', 'anima' ),
                    __( 'Compatibilidad RTMP multiplaforma', 'anima' ),
                    __( 'Comandos de chat y analítica', 'anima' ),
                ],
                'url'        => home_url( '/anima-live/' ),
            ];

            $schemas[] = $app_schema;
        }

        if ( empty( $schemas ) ) {
            return;
        }

        echo '<script type="application/ld+json">' . wp_json_encode( $schemas, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>';
    }
);
