<?php
/**
 * Ajustes para integraciones como CAPTCHA.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Registra los ajustes disponibles del plugin.
 */
function anima_register_contact_settings() {
    if ( ! is_admin() ) {
        return;
    }

    add_action( 'admin_init', 'anima_contact_settings_init' );
}

/**
 * Inicializa los campos y opciones dentro de Ajustes > Generales.
 */
function anima_contact_settings_init() {
    register_setting(
        'general',
        'anima_contact_captcha_provider',
        array(
            'type'              => 'string',
            'sanitize_callback' => 'anima_sanitize_captcha_provider',
            'default'           => 'none',
        )
    );

    $text_settings = array(
        'type'              => 'string',
        'sanitize_callback' => 'anima_sanitize_setting_text',
        'default'           => '',
    );

    register_setting( 'general', 'anima_recaptcha_site_key', $text_settings );
    register_setting( 'general', 'anima_recaptcha_secret_key', $text_settings );
    register_setting( 'general', 'anima_hcaptcha_site_key', $text_settings );
    register_setting( 'general', 'anima_hcaptcha_secret_key', $text_settings );

    add_settings_section(
        'anima_contact_settings',
        __( 'Contacto Anima', 'anima-core' ),
        'anima_render_contact_settings_section',
        'general'
    );

    add_settings_field(
        'anima_contact_captcha_provider',
        __( 'Protección contra spam', 'anima-core' ),
        'anima_render_captcha_provider_field',
        'general',
        'anima_contact_settings'
    );

    add_settings_field(
        'anima_contact_recaptcha_keys',
        __( 'Claves reCAPTCHA v3', 'anima-core' ),
        'anima_render_recaptcha_keys_field',
        'general',
        'anima_contact_settings'
    );

    add_settings_field(
        'anima_contact_hcaptcha_keys',
        __( 'Claves hCaptcha', 'anima-core' ),
        'anima_render_hcaptcha_keys_field',
        'general',
        'anima_contact_settings'
    );
}

/**
 * Texto descriptivo de la sección de ajustes.
 */
function anima_render_contact_settings_section() {
    echo '<p>' . esc_html__( 'Configura la protección antispam para el endpoint de contacto y formularios personalizados.', 'anima-core' ) . '</p>';
}

/**
 * Renderiza el selector de proveedor de CAPTCHA.
 */
function anima_render_captcha_provider_field() {
    $provider = get_option( 'anima_contact_captcha_provider', 'none' );
    ?>
    <select name="anima_contact_captcha_provider">
        <option value="none" <?php selected( $provider, 'none' ); ?>><?php esc_html_e( 'Desactivado', 'anima-core' ); ?></option>
        <option value="recaptcha_v3" <?php selected( $provider, 'recaptcha_v3' ); ?>><?php esc_html_e( 'Google reCAPTCHA v3', 'anima-core' ); ?></option>
        <option value="hcaptcha" <?php selected( $provider, 'hcaptcha' ); ?>><?php esc_html_e( 'hCaptcha', 'anima-core' ); ?></option>
    </select>
    <p class="description">
        <?php esc_html_e( 'Activa una opción para exigir la validación del token en el endpoint /wp-json/anima/v1/contacto.', 'anima-core' ); ?>
    </p>
    <?php
}

/**
 * Renderiza los campos para las claves de reCAPTCHA.
 */
function anima_render_recaptcha_keys_field() {
    $site_key   = get_option( 'anima_recaptcha_site_key', '' );
    $secret_key = get_option( 'anima_recaptcha_secret_key', '' );
    ?>
    <input type="text" class="regular-text" name="anima_recaptcha_site_key" value="<?php echo esc_attr( $site_key ); ?>" placeholder="<?php esc_attr_e( 'Clave del sitio', 'anima-core' ); ?>" />
    <br />
    <input type="text" class="regular-text" name="anima_recaptcha_secret_key" value="<?php echo esc_attr( $secret_key ); ?>" placeholder="<?php esc_attr_e( 'Clave secreta', 'anima-core' ); ?>" />
    <p class="description"><?php esc_html_e( 'Introduce las claves v3 proporcionadas por Google reCAPTCHA.', 'anima-core' ); ?></p>
    <?php
}

/**
 * Renderiza los campos para las claves de hCaptcha.
 */
function anima_render_hcaptcha_keys_field() {
    $site_key   = get_option( 'anima_hcaptcha_site_key', '' );
    $secret_key = get_option( 'anima_hcaptcha_secret_key', '' );
    ?>
    <input type="text" class="regular-text" name="anima_hcaptcha_site_key" value="<?php echo esc_attr( $site_key ); ?>" placeholder="<?php esc_attr_e( 'Clave del sitio', 'anima-core' ); ?>" />
    <br />
    <input type="text" class="regular-text" name="anima_hcaptcha_secret_key" value="<?php echo esc_attr( $secret_key ); ?>" placeholder="<?php esc_attr_e( 'Clave secreta', 'anima-core' ); ?>" />
    <p class="description"><?php esc_html_e( 'Introduce las claves proporcionadas por hCaptcha.', 'anima-core' ); ?></p>
    <?php
}

/**
 * Sanitiza el proveedor seleccionado.
 */
function anima_sanitize_captcha_provider( $value ) {
    $allowed = array( 'none', 'recaptcha_v3', 'hcaptcha' );
    $value   = sanitize_key( $value );

    if ( ! in_array( $value, $allowed, true ) ) {
        return 'none';
    }

    return $value;
}

/**
 * Sanitiza cadenas simples provenientes de los ajustes.
 */
function anima_sanitize_setting_text( $value ) {
    return sanitize_text_field( $value );
}
