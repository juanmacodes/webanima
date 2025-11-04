<?php
/**
 * Analítica y consentimiento.
 *
 * @package Anima
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function anima_get_ga4_id(): string {
    $id = defined( 'ANIMA_GA4_ID' ) ? ANIMA_GA4_ID : (string) get_option( 'anima_ga4_id', '' );
    return trim( $id );
}

add_action(
    'wp_head',
    static function (): void {
        $ga_id = anima_get_ga4_id();
        if ( empty( $ga_id ) ) {
            return;
        }
        ?>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('consent', 'default', {ad_storage: 'denied', analytics_storage: 'denied'});
        </script>
        <script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo esc_attr( $ga_id ); ?>"></script>
        <script>
            gtag('js', new Date());
            gtag('config', '<?php echo esc_js( $ga_id ); ?>', { 'anonymize_ip': true });
        </script>
        <?php
    },
    5
);

add_action(
    'wp_footer',
    static function (): void {
        ?>
        <div class="anima-cookie-banner" data-anima-cookie hidden>
            <div class="anima-cookie-banner__content">
                <p><?php esc_html_e( 'Usamos cookies para mejorar la experiencia y medir las campañas. ¿Aceptas?', 'anima' ); ?></p>
                <div class="anima-cookie-banner__actions">
                    <button type="button" data-anima-cookie-accept class="wp-block-button__link"><?php esc_html_e( 'Aceptar', 'anima' ); ?></button>
                    <button type="button" data-anima-cookie-decline class="wp-block-button__link wp-block-button__link--ghost"><?php esc_html_e( 'Rechazar', 'anima' ); ?></button>
                    <a href="<?php echo esc_url( home_url( '/cookies/' ) ); ?>" class="anima-cookie-banner__link"><?php esc_html_e( 'Más información', 'anima' ); ?></a>
                </div>
            </div>
        </div>
        <?php
    }
);
