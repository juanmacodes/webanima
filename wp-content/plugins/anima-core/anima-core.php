<?php
/**
 * Plugin Name:       Anima Core
 * Description:       CPTs, GraphQL, REST y funciones core para el ecosistema Anima.
 * Version:           1.0.0
 * Author:            Equipo Anima
 * Requires PHP:      8.0
 * Text Domain:       anima-core
 */

defined('ABSPATH') || exit;

// Define constante de versión
define('ANIMA_CORE_VERSION', '1.0.0');

// Cargar módulos solo si no estamos en línea de comandos WP CLI
if ( ! defined('WP_CLI') || ! WP_CLI ) {
    add_action('plugins_loaded', 'anima_core_load_modules', 1);
}

function anima_core_load_modules() {
    $base = plugin_dir_path(__FILE__);

    // Seguridad: todos los includes dentro de bloque try
    try {
        require_once $base . 'cpt.php';
        require_once $base . 'meta.php';
        require_once $base . 'rest.php';
        require_once $base . 'graphql.php';
        require_once $base . 'admin-demo-importer.php';
        require_once $base . 'activate.php';
    } catch (Throwable $e) {
        error_log('[Anima Core] Error al cargar módulos: ' . $e->getMessage());
    }
}

// Ejecutar hook de activación
register_activation_hook(__FILE__, 'anima_core_activate');
