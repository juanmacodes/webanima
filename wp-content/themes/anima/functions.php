<?php
/**
 * Funciones principales del tema Anima.
 *
 * @package Anima
 */

define( 'ANIMA_VERSION', '1.0.0' );

define( 'ANIMA_THEME_PATH', get_template_directory() );
define( 'ANIMA_THEME_URL', get_template_directory_uri() );

define( 'ANIMA_ASSETS_URL', ANIMA_THEME_URL . '/assets' );
define( 'ANIMA_ASSETS_PATH', ANIMA_THEME_PATH . '/assets' );

require_once ANIMA_THEME_PATH . '/inc/setup.php';
require_once ANIMA_THEME_PATH . '/inc/scripts.php';
require_once ANIMA_THEME_PATH . '/inc/custom-post-types.php';
require_once ANIMA_THEME_PATH . '/inc/taxonomies.php';
require_once ANIMA_THEME_PATH . '/inc/meta.php';
require_once ANIMA_THEME_PATH . '/inc/shortcodes.php';
require_once ANIMA_THEME_PATH . '/inc/waitlist.php';
require_once ANIMA_THEME_PATH . '/inc/rest.php';
require_once ANIMA_THEME_PATH . '/inc/schema.php';
require_once ANIMA_THEME_PATH . '/inc/analytics.php';
require_once ANIMA_THEME_PATH . '/inc/cli.php';
