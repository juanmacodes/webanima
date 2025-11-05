<?php
/**
 * Plugin Name: Anima Core
 * Description: Custom Post Type Proyectos, taxonomía Stack y metadatos para el ecosistema Anima.
 * Author: Equipo Anima
 * Version: 0.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'ANIMA_CORE_VERSION', '0.1.0' );
define( 'ANIMA_CORE_PATH', plugin_dir_path( __FILE__ ) );

require_once ANIMA_CORE_PATH . 'includes/cpt.php';
require_once ANIMA_CORE_PATH . 'includes/meta.php';
require_once ANIMA_CORE_PATH . 'includes/rest.php';
require_once ANIMA_CORE_PATH . 'includes/admin-demo-importer.php';
