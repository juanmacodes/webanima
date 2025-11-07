<?php
if (!defined('ABSPATH')) exit;

function anima_register_graphql_types() {
  if (!function_exists('register_graphql_field')) return;

  add_action('graphql_register_types', function () {
    register_graphql_field('Curso', 'nivelTerms', [
      'type' => ['list_of' => 'String'],
      'resolve' => function($post) {
        $terms = wp_get_post_terms($post->ID, 'nivel', ['fields' => 'names']);
        return is_wp_error($terms) ? [] : $terms;
      },
    ]);
  });
}
