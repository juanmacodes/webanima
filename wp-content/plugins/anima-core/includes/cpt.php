<?php
if (!defined('ABSPATH')) exit;

function anima_register_cpt($slug, $singular, $plural, $supports = ['title','editor','thumbnail']) {
  $labels = [
    'name' => $plural,
    'singular_name' => $singular,
    'add_new_item' => "Añadir $singular",
    'edit_item' => "Editar $singular",
    'new_item' => "$singular nuevo",
    'view_item' => "Ver $singular",
    'search_items' => "Buscar $plural",
    'not_found' => "No se encontraron $plural",
  ];

  register_post_type($slug, [
    'labels' => $labels,
    'public' => true,
    'show_ui' => true,
    'show_in_menu' => true,
    'show_in_rest' => true,          // Para Gutenberg/REST
    'has_archive' => true,
    'rewrite' => ['slug' => $slug],
    'supports' => $supports,
    'menu_icon' => 'dashicons-layout',
  ]);
}
