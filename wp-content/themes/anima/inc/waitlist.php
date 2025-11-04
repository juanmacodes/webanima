<?php
/**
 * Gestión de la lista de espera.
 *
 * @package Anima
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function anima_waitlist_table(): string {
    global $wpdb;
    return $wpdb->prefix . 'anima_waitlist';
}

function anima_waitlist_create_table(): void {
    global $wpdb;

    $table_name      = anima_waitlist_table();
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE {$table_name} (
        id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        name varchar(200) NOT NULL,
        email varchar(200) NOT NULL,
        network varchar(100) NOT NULL,
        country varchar(120) NOT NULL,
        wants_beta tinyint(1) DEFAULT 0,
        created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY  (id),
        UNIQUE KEY email_unique (email)
    ) {$charset_collate};";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta( $sql );
}
add_action( 'after_switch_theme', 'anima_waitlist_create_table' );

function anima_waitlist_insert( array $payload ) {
    global $wpdb;

    $table = anima_waitlist_table();
    $data  = [
        'name'       => sanitize_text_field( $payload['name'] ?? '' ),
        'email'      => sanitize_email( $payload['email'] ?? '' ),
        'network'    => sanitize_text_field( $payload['network'] ?? '' ),
        'country'    => sanitize_text_field( $payload['country'] ?? '' ),
        'wants_beta' => ! empty( $payload['beta'] ) ? 1 : 0,
    ];

    if ( empty( $data['name'] ) || empty( $data['email'] ) || empty( $data['network'] ) || empty( $data['country'] ) ) {
        return new WP_Error( 'anima_waitlist_invalid', __( 'Faltan campos obligatorios.', 'anima' ) );
    }

    $exists = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$table} WHERE email = %s", $data['email'] ) );
    if ( $exists ) {
        return (int) $exists;
    }

    $inserted = $wpdb->insert( $table, $data, [ '%s', '%s', '%s', '%s', '%d' ] );

    if ( false === $inserted ) {
        return new WP_Error( 'anima_waitlist_db', __( 'No se pudo guardar tu registro.', 'anima' ) );
    }

    $insert_id = (int) $wpdb->insert_id;

    anima_waitlist_send_notifications( $data );

    return $insert_id;
}

function anima_waitlist_send_notifications( array $data ): void {
    $admin_email = get_option( 'admin_email' );
    $subject     = __( 'Nuevo registro en Anima Live', 'anima' );
    $message     = sprintf(
        "Nombre: %s\nEmail: %s\nRed: %s\nPaís: %s\nBeta: %s",
        $data['name'],
        $data['email'],
        $data['network'],
        $data['country'],
        $data['wants_beta'] ? __( 'Sí', 'anima' ) : __( 'No', 'anima' )
    );

    wp_mail( $admin_email, $subject, $message );

    if ( defined( 'ANIMA_MAILCHIMP_API_KEY' ) && defined( 'ANIMA_MAILCHIMP_LIST_ID' ) ) {
        anima_waitlist_subscribe_mailchimp( $data );
    }

    if ( defined( 'ANIMA_SENDGRID_API_KEY' ) && defined( 'ANIMA_SENDGRID_LIST_ID' ) ) {
        anima_waitlist_sync_sendgrid( $data );
    }

    do_action( 'anima_waitlist_registered', $data );
}

function anima_waitlist_subscribe_mailchimp( array $data ): void {
    $api_key = ANIMA_MAILCHIMP_API_KEY;
    $list_id = ANIMA_MAILCHIMP_LIST_ID;

    if ( empty( $api_key ) || empty( $list_id ) ) {
        return;
    }

    list( , $dc ) = explode( '-', $api_key );
    $endpoint     = sprintf( 'https://%s.api.mailchimp.com/3.0/lists/%s/members', $dc, $list_id );

    $payload = [
        'email_address' => $data['email'],
        'status'        => 'subscribed',
        'merge_fields'  => [
            'FNAME' => $data['name'],
            'PAIS'  => $data['country'],
            'RED'   => $data['network'],
        ],
    ];

    wp_remote_post(
        $endpoint,
        [
            'headers' => [
                'Authorization' => 'apikey ' . $api_key,
                'Content-Type'  => 'application/json',
            ],
            'body'    => wp_json_encode( $payload ),
            'timeout' => 10,
        ]
    );
}

function anima_waitlist_sync_sendgrid( array $data ): void {
    $api_key = ANIMA_SENDGRID_API_KEY;
    $list_id = ANIMA_SENDGRID_LIST_ID;

    if ( empty( $api_key ) || empty( $list_id ) ) {
        return;
    }

    $endpoint = 'https://api.sendgrid.com/v3/marketing/contacts';

    $payload = [
        'list_ids' => [ $list_id ],
        'contacts' => [
            [
                'email'     => $data['email'],
                'first_name'=> $data['name'],
                'country'   => $data['country'],
                'custom_fields' => [
                    'network'    => $data['network'],
                    'wants_beta' => $data['wants_beta'],
                ],
            ],
        ],
    ];

    wp_remote_post(
        $endpoint,
        [
            'headers' => [
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type'  => 'application/json',
            ],
            'body'    => wp_json_encode( $payload ),
            'timeout' => 10,
        ]
    );
}
