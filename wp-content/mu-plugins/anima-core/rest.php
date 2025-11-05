<?php
/**
 * REST API endpoints for Anima Core.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'rest_api_init', 'anima_core_register_rest_routes' );

/**
 * Register anima/v1 routes.
 */
function anima_core_register_rest_routes(): void {
    register_rest_route(
        'anima/v1',
        '/projects',
        array(
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => 'anima_core_rest_get_projects',
            'permission_callback' => '__return_true',
            'args'                => array(
                'per_page' => array(
                    'description'       => __( 'Number of items per page.', 'anima-core' ),
                    'type'              => 'integer',
                    'sanitize_callback' => 'absint',
                ),
                'page'     => array(
                    'description'       => __( 'Current page of the collection.', 'anima-core' ),
                    'type'              => 'integer',
                    'sanitize_callback' => 'absint',
                ),
            ),
        )
    );

    register_rest_route(
        'anima/v1',
        '/projects/(?P<identifier>[a-z0-9-]+)',
        array(
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => 'anima_core_rest_get_project',
            'permission_callback' => '__return_true',
            'args'                => array(
                'identifier' => array(
                    'description'       => __( 'Project ID or slug.', 'anima-core' ),
                    'type'              => 'string',
                    'sanitize_callback' => 'sanitize_title_for_query',
                ),
            ),
        )
    );

    register_rest_route(
        'anima/v1',
        '/courses',
        array(
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => 'anima_core_rest_get_courses',
            'permission_callback' => '__return_true',
            'args'                => array(
                'per_page' => array(
                    'type'              => 'integer',
                    'sanitize_callback' => 'absint',
                ),
                'page'     => array(
                    'type'              => 'integer',
                    'sanitize_callback' => 'absint',
                ),
                'level'    => array(
                    'type'              => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ),
                'modality' => array(
                    'type'              => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ),
                'search'   => array(
                    'type'              => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ),
            ),
        )
    );

    register_rest_route(
        'anima/v1',
        '/courses/(?P<identifier>[a-z0-9-]+)',
        array(
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => 'anima_core_rest_get_course',
            'permission_callback' => '__return_true',
            'args'                => array(
                'identifier' => array(
                    'description'       => __( 'Course ID or slug.', 'anima-core' ),
                    'type'              => 'string',
                    'sanitize_callback' => 'sanitize_title_for_query',
                ),
            ),
        )
    );

    register_rest_route(
        'anima/v1',
        '/slides',
        array(
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => 'anima_core_rest_get_slides',
            'permission_callback' => '__return_true',
            'args'                => array(
                'group' => array(
                    'type'              => 'string',
                    'sanitize_callback' => 'sanitize_title_for_query',
                ),
            ),
        )
    );

    register_rest_route(
        'anima/v1',
        '/hotspots',
        array(
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => 'anima_core_rest_get_hotspots',
            'permission_callback' => '__return_true',
        )
    );

    register_rest_route(
        'anima/v1',
        '/hotspots/(?P<identifier>[a-z0-9-]+)',
        array(
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => 'anima_core_rest_get_hotspot',
            'permission_callback' => '__return_true',
            'args'                => array(
                'identifier' => array(
                    'type'              => 'string',
                    'sanitize_callback' => 'sanitize_title_for_query',
                ),
            ),
        )
    );
}

/**
 * Get collection of projects.
 *
 * @param WP_REST_Request $request Request.
 * @return WP_REST_Response
 */
function anima_core_rest_get_projects( WP_REST_Request $request ): WP_REST_Response {
    $per_page = (int) $request->get_param( 'per_page' );
    $per_page = $per_page > 0 ? min( $per_page, 50 ) : 10;
    $page     = max( 1, (int) $request->get_param( 'page' ) );

    $query = new WP_Query(
        array(
            'post_type'      => 'project',
            'post_status'    => 'publish',
            'paged'          => $page,
            'posts_per_page' => $per_page,
        )
    );

    $items = array();
    foreach ( $query->posts as $post ) {
        $items[] = anima_core_format_project( $post );
    }

    wp_reset_postdata();

    $response = anima_core_prepare_rest_response( $items );
    $response->header( 'X-WP-Total', (int) $query->found_posts );
    $response->header( 'X-WP-TotalPages', (int) $query->max_num_pages );

    return $response;
}

/**
 * Retrieve single project by ID or slug.
 *
 * @param WP_REST_Request $request Request.
 * @return WP_REST_Response|WP_Error
 */
function anima_core_rest_get_project( WP_REST_Request $request ) {
    $identifier = $request->get_param( 'identifier' );
    $post       = anima_core_get_post_by_identifier( 'project', $identifier );

    if ( ! $post ) {
        return new WP_Error( 'anima_project_not_found', __( 'Project not found.', 'anima-core' ), array( 'status' => 404 ) );
    }

    return anima_core_prepare_rest_response( anima_core_format_project( $post ) );
}

/**
 * Get courses collection.
 *
 * @param WP_REST_Request $request Request.
 * @return WP_REST_Response
 */
function anima_core_rest_get_courses( WP_REST_Request $request ): WP_REST_Response {
    $per_page = (int) $request->get_param( 'per_page' );
    $per_page = $per_page > 0 ? min( $per_page, 50 ) : 10;
    $page     = max( 1, (int) $request->get_param( 'page' ) );

    $meta_query      = array( 'relation' => 'AND' );
    $level           = sanitize_text_field( $request->get_param( 'level' ) );
    $modality        = sanitize_text_field( $request->get_param( 'modality' ) );
    $allowed_levels  = array( 'Intro', 'Intermedio', 'Avanzado' );
    $allowed_modes   = array( 'Online', 'Presencial', 'Híbrido' );

    if ( $level && in_array( $level, $allowed_levels, true ) ) {
        $meta_query[] = array(
            'key'   => 'level',
            'value' => $level,
        );
    }

    if ( $modality && in_array( $modality, $allowed_modes, true ) ) {
        $meta_query[] = array(
            'key'   => 'modality',
            'value' => $modality,
        );
    }

    if ( count( $meta_query ) <= 1 ) {
        $meta_query = array();
    }

    $search = sanitize_text_field( $request->get_param( 'search' ) );

    $query_args = array(
        'post_type'      => 'course',
        'post_status'    => 'publish',
        'paged'          => $page,
        'posts_per_page' => $per_page,
    );

    if ( $meta_query ) {
        $query_args['meta_query'] = $meta_query;
    }

    if ( $search ) {
        $query_args['s'] = $search;
    }

    $query = new WP_Query( $query_args );

    $items = array();
    foreach ( $query->posts as $post ) {
        $items[] = anima_core_format_course( $post );
    }

    wp_reset_postdata();

    $response = anima_core_prepare_rest_response( $items );
    $response->header( 'X-WP-Total', (int) $query->found_posts );
    $response->header( 'X-WP-TotalPages', (int) $query->max_num_pages );

    return $response;
}

/**
 * Retrieve a single course by identifier.
 *
 * @param WP_REST_Request $request Request.
 * @return WP_REST_Response|WP_Error
 */
function anima_core_rest_get_course( WP_REST_Request $request ) {
    $identifier = $request->get_param( 'identifier' );
    $post       = anima_core_get_post_by_identifier( 'course', $identifier );

    if ( ! $post ) {
        return new WP_Error( 'anima_course_not_found', __( 'Course not found.', 'anima-core' ), array( 'status' => 404 ) );
    }

    return anima_core_prepare_rest_response( anima_core_format_course( $post ) );
}

/**
 * Retrieve slides collection.
 *
 * @param WP_REST_Request $request Request.
 * @return WP_REST_Response
 */
function anima_core_rest_get_slides( WP_REST_Request $request ): WP_REST_Response {
    $group = sanitize_text_field( $request->get_param( 'group' ) );

    $tax_query = array();
    if ( $group ) {
        $tax_query[] = array(
            'taxonomy' => 'slide_group',
            'field'    => 'slug',
            'terms'    => $group,
        );
    }

    $query = new WP_Query(
        array(
            'post_type'      => 'slide',
            'post_status'    => 'publish',
            'posts_per_page' => 50,
            'orderby'        => 'meta_value_num',
            'meta_key'       => 'order',
            'order'          => 'ASC',
            'tax_query'      => $tax_query,
        )
    );

    $items = array();
    foreach ( $query->posts as $post ) {
        $items[] = anima_core_format_slide( $post );
    }

    wp_reset_postdata();

    return anima_core_prepare_rest_response( $items );
}

/**
 * Retrieve hotspots collection.
 *
 * @return WP_REST_Response
 */
function anima_core_rest_get_hotspots(): WP_REST_Response {
    $query = new WP_Query(
        array(
            'post_type'      => 'hotspot',
            'post_status'    => 'publish',
            'posts_per_page' => 100,
            'orderby'        => 'title',
            'order'          => 'ASC',
        )
    );

    $items = array();
    foreach ( $query->posts as $post ) {
        $items[] = anima_core_format_hotspot( $post );
    }

    wp_reset_postdata();

    return anima_core_prepare_rest_response( $items );
}

/**
 * Retrieve a single hotspot.
 *
 * @param WP_REST_Request $request Request.
 * @return WP_REST_Response|WP_Error
 */
function anima_core_rest_get_hotspot( WP_REST_Request $request ) {
    $identifier = $request->get_param( 'identifier' );
    $post       = anima_core_get_post_by_identifier( 'hotspot', $identifier );

    if ( ! $post ) {
        return new WP_Error( 'anima_hotspot_not_found', __( 'Hotspot not found.', 'anima-core' ), array( 'status' => 404 ) );
    }

    return anima_core_prepare_rest_response( anima_core_format_hotspot( $post ) );
}

/**
 * Locate a post by numeric ID or slug.
 *
 * @param string $post_type Post type.
 * @param string $identifier Identifier.
 * @return WP_Post|null
 */
function anima_core_get_post_by_identifier( string $post_type, string $identifier ): ?WP_Post {
    if ( is_numeric( $identifier ) ) {
        $post = get_post( (int) $identifier );
        if ( $post && $post->post_type === $post_type && 'publish' === $post->post_status ) {
            return $post;
        }
    }

    $post = get_page_by_path( $identifier, OBJECT, $post_type );
    if ( $post && 'publish' === $post->post_status ) {
        return $post;
    }

    return null;
}

/**
 * Format project for API output.
 *
 * @param WP_Post $post Post object.
 * @return array
 */
function anima_core_format_project( WP_Post $post ): array {
    $cover_id = get_post_thumbnail_id( $post );
    $cover    = anima_core_prepare_image( $cover_id, 'anima-card-2x' );
    $gallery  = array();

    foreach ( anima_core_get_meta_array( $post->ID, 'gallery' ) as $url ) {
        $gallery[] = esc_url_raw( $url );
    }

    $terms = wp_get_post_terms( $post->ID, 'project_type', array( 'fields' => 'names' ) );

    return array(
        'id'      => (int) $post->ID,
        'slug'    => $post->post_name,
        'title'   => wp_strip_all_tags( get_the_title( $post ) ),
        'excerpt' => wp_strip_all_tags( get_the_excerpt( $post ) ),
        'cover'   => $cover,
        'client'  => sanitize_text_field( anima_core_get_meta( $post->ID, 'client', '' ) ),
        'year'    => (int) anima_core_get_meta( $post->ID, 'year', 0 ),
        'cta'     => anima_core_prepare_cta(
            (string) anima_core_get_meta( $post->ID, 'cta_label', '' ),
            (string) anima_core_get_meta( $post->ID, 'cta_url', '' )
        ),
        'gallery' => $gallery,
        'type'    => array_map( 'sanitize_text_field', $terms ),
    );
}

/**
 * Format course for API output.
 *
 * @param WP_Post $post Post object.
 * @return array
 */
function anima_core_format_course( WP_Post $post ): array {
    $cover_id = get_post_thumbnail_id( $post );
    $cover    = anima_core_prepare_image( $cover_id, 'anima-card-2x' );

    return array(
        'id'       => (int) $post->ID,
        'slug'     => $post->post_name,
        'title'    => wp_strip_all_tags( get_the_title( $post ) ),
        'excerpt'  => wp_strip_all_tags( get_the_excerpt( $post ) ),
        'cover'    => $cover,
        'level'    => sanitize_text_field( anima_core_get_meta( $post->ID, 'level', '' ) ),
        'modality' => sanitize_text_field( anima_core_get_meta( $post->ID, 'modality', '' ) ),
        'duration' => sanitize_text_field( anima_core_get_meta( $post->ID, 'duration', '' ) ),
        'price'    => (float) anima_core_get_meta( $post->ID, 'price', 0 ),
        'cta'      => anima_core_prepare_cta(
            (string) anima_core_get_meta( $post->ID, 'cta_label', '' ),
            (string) anima_core_get_meta( $post->ID, 'cta_url', '' )
        ),
        'syllabus' => array_map( 'sanitize_text_field', anima_core_get_meta_array( $post->ID, 'syllabus' ) ),
    );
}

/**
 * Format slide for API output.
 *
 * @param WP_Post $post Post object.
 * @return array
 */
function anima_core_format_slide( WP_Post $post ): array {
    $cover_id = get_post_thumbnail_id( $post );
    $cover    = anima_core_prepare_image( $cover_id, 'anima-hero-2x' );

    $cta = anima_core_prepare_cta(
        (string) anima_core_get_meta( $post->ID, 'button_label', '' ),
        (string) anima_core_get_meta( $post->ID, 'button_url', '' )
    );

    return array(
        'id'      => (int) $post->ID,
        'slug'    => $post->post_name,
        'title'   => wp_strip_all_tags( get_the_title( $post ) ),
        'excerpt' => wp_strip_all_tags( get_the_excerpt( $post ) ),
        'cover'   => $cover,
        'cta'     => $cta,
        'order'   => (int) anima_core_get_meta( $post->ID, 'order', 0 ),
    );
}

/**
 * Format hotspot for API output.
 *
 * @param WP_Post $post Post object.
 * @return array
 */
function anima_core_format_hotspot( WP_Post $post ): array {
    $position = array(
        anima_core_to_float( anima_core_get_meta( $post->ID, 'x', 0 ) ),
        anima_core_to_float( anima_core_get_meta( $post->ID, 'y', 0 ) ),
        anima_core_to_float( anima_core_get_meta( $post->ID, 'z', 0 ) ),
    );

    $look_at_meta = array(
        anima_core_to_float( anima_core_get_meta( $post->ID, 'look_at_x', 0 ) ),
        anima_core_to_float( anima_core_get_meta( $post->ID, 'look_at_y', 0 ) ),
        anima_core_to_float( anima_core_get_meta( $post->ID, 'look_at_z', 0 ) ),
    );

    $look_at = array_filter( $look_at_meta, static function ( $value ) {
        return abs( $value ) > 0;
    } );

    $action = array(
        'label' => sanitize_text_field( anima_core_get_meta( $post->ID, 'action_label', '' ) ),
        'href'  => esc_url_raw( anima_core_get_meta( $post->ID, 'action_href', '' ) ),
        'type'  => sanitize_text_field( anima_core_get_meta( $post->ID, 'action_type', '' ) ),
    );

    if ( '' === $action['label'] && '' === $action['href'] ) {
        $action = null;
    } else {
        if ( '' === $action['label'] ) {
            unset( $action['label'] );
        }
        if ( '' === $action['href'] ) {
            unset( $action['href'] );
        }
        if ( '' === $action['type'] ) {
            unset( $action['type'] );
        }
    }

    $media = array(
        'image' => esc_url_raw( anima_core_get_meta( $post->ID, 'media_image', '' ) ),
        'video' => esc_url_raw( anima_core_get_meta( $post->ID, 'media_video', '' ) ),
    );

    $media = array_filter( $media );
    if ( empty( $media ) ) {
        $media = null;
    }

    return array(
        'id'       => $post->post_name,
        'title'    => wp_strip_all_tags( get_the_title( $post ) ),
        'body'     => wp_strip_all_tags( $post->post_content ),
        'position' => $position,
        'lookAt'   => $look_at ? array_map( 'anima_core_to_float', $look_at_meta ) : null,
        'action'   => $action,
        'media'    => $media,
    );
}
