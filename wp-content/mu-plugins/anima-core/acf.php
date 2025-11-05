<?php
/**
 * Advanced Custom Fields integration.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_filter( 'acf/settings/save_json', 'anima_core_acf_json_save_point' );
add_filter( 'acf/settings/load_json', 'anima_core_acf_json_load_point' );
add_action( 'acf/init', 'anima_core_register_acf_groups' );

/**
 * Ensure ACF JSON is stored inside the plugin.
 *
 * @return string
 */
function anima_core_acf_json_save_point(): string {
    return ANIMA_CORE_PATH . '/acf-json';
}

/**
 * Load JSON definitions from the plugin folder.
 *
 * @param array $paths Existing paths.
 * @return array
 */
function anima_core_acf_json_load_point( array $paths ): array {
    $paths[] = ANIMA_CORE_PATH . '/acf-json';

    return $paths;
}

/**
 * Register local field groups mirroring the CPT meta boxes.
 */
function anima_core_register_acf_groups(): void {
    $definitions = anima_core_meta_field_definitions();

    foreach ( $definitions as $post_type => $fields ) {
        $acf_fields = array();

        foreach ( $fields as $key => $field ) {
            $acf_field = array(
                'key'          => 'field_anima_' . $post_type . '_' . $key,
                'name'         => $key,
                'label'        => $field['label'],
                'instructions' => $field['description'] ?? '',
                'required'     => 0,
            );

            switch ( $field['type'] ) {
                case 'number':
                    $acf_field['type'] = 'number';
                    if ( isset( $field['step'] ) ) {
                        $acf_field['step'] = $field['step'];
                    }
                    break;
                case 'select':
                    $acf_field['type']    = 'select';
                    $acf_field['choices'] = array_combine( $field['options'], $field['options'] );
                    $acf_field['ui']      = 0;
                    break;
                case 'url':
                    $acf_field['type'] = 'url';
                    break;
                case 'textarea':
                    $acf_field['type']      = 'textarea';
                    $acf_field['rows']      = 4;
                    $acf_field['new_lines'] = 'br';
                    break;
                default:
                    $acf_field['type'] = 'text';
                    break;
            }

            $acf_fields[] = $acf_field;
        }

        acf_add_local_field_group(
            array(
                'key'      => 'group_anima_' . $post_type,
                'title'    => sprintf( __( 'Anima %s Details', 'anima-core' ), ucfirst( $post_type ) ),
                'fields'   => $acf_fields,
                'location' => array(
                    array(
                        array(
                            'param'    => 'post_type',
                            'operator' => '==',
                            'value'    => $post_type,
                        ),
                    ),
                ),
                'position' => 'acf_after_title',
                'style'    => 'default',
                'active'   => true,
            )
        );
    }
}
