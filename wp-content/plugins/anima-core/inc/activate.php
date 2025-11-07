<?php
if (!defined('ABSPATH')) exit;

function anima_core_activate() {
  // Forzamos registro para poder hacer flush correctamente
  require_once ANIMA_CORE_PATH . 'cpt.php';
  require_once ANIMA_CORE_PATH . 'cpt-curso.php';
  require_once ANIMA_CORE_PATH . 'cpt-proyecto.php';
  require_once ANIMA_CORE_PATH . 'taxonomias.php';

  anima_register_cpt_curso();
  anima_register_cpt_proyecto();
  anima_register_taxonomias();

  flush_rewrite_rules();
}

function anima_core_deactivate() {
  flush_rewrite_rules();
}
