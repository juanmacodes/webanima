<?php
/**
 * Plugin activation tasks.
 *
 * @package anima-core
 */

defined( 'ABSPATH' ) || exit;

/**
 * Run on plugin activation.
 */
function anima_core_activate(): void {
	global $wpdb;

	$table_name      = $wpdb->prefix . 'anima_waitlist';
	$charset_collate = $wpdb->get_charset_collate();

	require_once ABSPATH . 'wp-admin/includes/upgrade.php';

	$sql = "CREATE TABLE {$table_name} (
		id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		name varchar(190) NOT NULL,
		email varchar(190) NOT NULL,
		network varchar(190) DEFAULT '' NOT NULL,
		country varchar(190) DEFAULT '' NOT NULL,
		consent tinyint(1) DEFAULT 0 NOT NULL,
		created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
		PRIMARY KEY  (id),
		KEY email (email)
	) {$charset_collate};";

	dbDelta( $sql );

	update_option( 'anima_core_version', ANIMA_CORE_VERSION );
}
