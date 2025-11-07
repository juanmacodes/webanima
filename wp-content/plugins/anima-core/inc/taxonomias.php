<?php
if (!defined('ABSPATH')) exit;

function anima_register_taxonomias() {
  // Nivel (para Cursos)
  register_taxonomy('nivel', ['curso'], [
    'label' => 'Niveles',
    'public' => true,
    'show_ui' => true,
    'show_in_rest' => true,
    'hierarchical' => false,
    'rewrite' => ['slug' => 'nivel'],
  ]);

  // Modalidad (para Cursos)
  register_taxonomy('modalidad', ['curso'], [
    'label' => 'Modalidades',
    'public' => true,
    'show_ui' => true,
    'show_in_rest' => true,
    'hierarchical' => false,
    'rewrite' => ['slug' => 'modalidad'],
  ]);
}
