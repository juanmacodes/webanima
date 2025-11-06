<?php
namespace Anima\Engine\Admin;

use Anima\Engine\Models\Asset;
use Anima\Engine\Services\ServiceInterface;
use WC_Product;

use const DAY_IN_SECONDS;

use function __;
use function absint;
use function add_action;
use function add_options_page;
use function add_query_arg;
use function admin_url;
use function checked;
use function esc_attr;
use function esc_html__;
use function esc_html_e;
use function esc_js;
use function esc_textarea;
use function esc_url;
use function esc_url_raw;
use function function_exists;
use function get_option;
use function get_permalink;
use function get_post_status;
use function do_action;
use function number_format_i18n;
use function register_setting;
use function sanitize_key;
use function sanitize_text_field;
use function sanitize_title;
use function settings_errors;
use function settings_fields;
use function submit_button;
use function selected;
use function wp_nonce_field;
use function wp_parse_args;
use function wp_safe_redirect;
use function wp_unslash;
use function wc_get_product;
use function wc_get_products;
use function check_admin_referer;
use function in_array;
use function untrailingslashit;
use function current_user_can;

/**
 * Página de opciones del plugin.
 */
class OptionsPage implements ServiceInterface {
    protected Asset $assets;

    public function __construct( ?Asset $assets = null ) {
        $this->assets = $assets ?? new Asset();
    }

    /**
     * {@inheritDoc}
     */
    public function register(): void {
        add_action( 'admin_menu', [ $this, 'register_menu' ] );
        add_action( 'admin_init', [ $this, 'register_settings' ] );
        add_action( 'admin_post_anima_engine_save_asset', [ $this, 'handle_save_asset' ] );
        add_action( 'admin_post_anima_engine_delete_asset', [ $this, 'handle_delete_asset' ] );
    }

    /**
     * Añade el menú de opciones.
     */
    public function register_menu(): void {
        add_options_page(
            __( 'Anima Engine', 'anima-engine' ),
            __( 'Anima Engine', 'anima-engine' ),
            'manage_options',
            'anima-engine',
            [ $this, 'render_page' ]
        );
    }

    /**
     * Registra los ajustes.
     */
    public function register_settings(): void {
        register_setting( 'anima_engine_settings', 'anima_engine_options', [ $this, 'sanitize_options' ] );
    }

    /**
     * Limpia los datos antes de guardarlos.
     */
    public function sanitize_options( array $options ): array {
        $clean = [];

        $clean['enable_slider']        = ! empty( $options['enable_slider'] );
        $clean['enable_model_viewer']  = ! empty( $options['enable_model_viewer'] );
        $clean['enable_cache']         = ! empty( $options['enable_cache'] );
        $clean['enable_schema']        = ! empty( $options['enable_schema'] );
        $clean['cache_catalog']        = ! empty( $options['cache_catalog'] );
        $clean['cache_entitlements']   = ! empty( $options['cache_entitlements'] );
        $clean['enable_webhooks']      = ! empty( $options['enable_webhooks'] );

        $clean['recaptcha_site_key']   = isset( $options['recaptcha_site_key'] ) ? sanitize_text_field( $options['recaptcha_site_key'] ) : '';
        $clean['recaptcha_secret_key'] = isset( $options['recaptcha_secret_key'] ) ? sanitize_text_field( $options['recaptcha_secret_key'] ) : '';
        $clean['webhook_secret']       = isset( $options['webhook_secret'] ) ? sanitize_text_field( $options['webhook_secret'] ) : '';

        $clean['grid_width']      = isset( $options['grid_width'] ) ? max( 1, (int) $options['grid_width'] ) : 3;
        $clean['page_home']       = isset( $options['page_home'] ) ? sanitize_text_field( $options['page_home'] ) : '';
        $clean['page_contact']    = isset( $options['page_contact'] ) ? sanitize_text_field( $options['page_contact'] ) : '';
        $clean['page_experience'] = isset( $options['page_experience'] ) ? sanitize_text_field( $options['page_experience'] ) : '';
        $clean['page_live']       = isset( $options['page_live'] ) ? sanitize_text_field( $options['page_live'] ) : '';

        $clean['cors_domains'] = [];
        if ( isset( $options['cors_domains'] ) ) {
            $domains = preg_split( '/[\r\n,]+/', (string) $options['cors_domains'] ) ?: [];
            foreach ( $domains as $domain ) {
                $domain = esc_url_raw( trim( $domain ) );
                if ( '' !== $domain ) {
                    $clean['cors_domains'][] = untrailingslashit( $domain );
                }
            }
            $clean['cors_domains'] = array_values( array_unique( $clean['cors_domains'] ) );
        }

        $provider = isset( $options['storage_provider'] ) ? sanitize_key( $options['storage_provider'] ) : 'wp_media';
        if ( ! in_array( $provider, [ 'wp_media', 's3', 'r2' ], true ) ) {
            $provider = 'wp_media';
        }
        $clean['storage_provider']   = $provider;
        $clean['storage_bucket']     = isset( $options['storage_bucket'] ) ? sanitize_text_field( $options['storage_bucket'] ) : '';
        $clean['storage_access_key'] = isset( $options['storage_access_key'] ) ? sanitize_text_field( $options['storage_access_key'] ) : '';
        $clean['storage_secret_key'] = isset( $options['storage_secret_key'] ) ? sanitize_text_field( $options['storage_secret_key'] ) : '';

        $clean['jwt_secret']       = isset( $options['jwt_secret'] ) ? sanitize_text_field( $options['jwt_secret'] ) : '';
        $clean['jwt_access_ttl']   = isset( $options['jwt_access_ttl'] ) ? max( 300, absint( $options['jwt_access_ttl'] ) ) : 15 * 60;
        $clean['jwt_refresh_ttl']  = isset( $options['jwt_refresh_ttl'] ) ? max( DAY_IN_SECONDS, absint( $options['jwt_refresh_ttl'] ) ) : 30 * DAY_IN_SECONDS;

        $clean['subscription_product_id'] = isset( $options['subscription_product_id'] ) ? absint( $options['subscription_product_id'] ) : 0;
        $clean['live_product_id']         = isset( $options['live_product_id'] ) ? absint( $options['live_product_id'] ) : 0;

        return $clean;
    }

    /**
     * Renderiza la página de opciones.
     */
    public function render_page(): void {
        $defaults = [
            'enable_slider'           => true,
            'enable_model_viewer'     => true,
            'enable_cache'            => true,
            'enable_schema'           => true,
            'cache_catalog'           => true,
            'cache_entitlements'      => true,
            'enable_webhooks'         => false,
            'recaptcha_site_key'      => '',
            'recaptcha_secret_key'    => '',
            'webhook_secret'          => '',
            'grid_width'              => 3,
            'page_home'               => '',
            'page_contact'            => '',
            'page_experience'         => '',
            'page_live'               => '',
            'cors_domains'            => [],
            'jwt_secret'              => '',
            'jwt_access_ttl'          => 15 * 60,
            'jwt_refresh_ttl'         => 30 * DAY_IN_SECONDS,
            'storage_provider'        => 'wp_media',
            'storage_bucket'          => '',
            'storage_access_key'      => '',
            'storage_secret_key'      => '',
            'subscription_product_id' => 0,
            'live_product_id'         => 0,
        ];

        $options    = wp_parse_args( get_option( 'anima_engine_options', [] ), $defaults );
        $cors_value = implode( "\n", array_map( 'strval', (array) $options['cors_domains'] ) );

        $assets         = $this->assets->getAll();
        $edit_asset_id  = isset( $_GET['asset_id'] ) ? absint( wp_unslash( (string) $_GET['asset_id'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $asset_to_edit  = $edit_asset_id > 0 ? $this->assets->getById( $edit_asset_id ) : null;
        $linked_product = $asset_to_edit ? $this->find_product_id_for_asset( (int) $asset_to_edit['id'] ) : 0;

        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Anima Engine — Configuración', 'anima-engine' ); ?></h1>
            <?php $this->render_notices(); ?>
            <form action="options.php" method="post">
                <?php settings_fields( 'anima_engine_settings' ); ?>
                <h2><?php esc_html_e( 'Ajustes generales', 'anima-engine' ); ?></h2>
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Activar slider global', 'anima-engine' ); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="anima_engine_options[enable_slider]" value="1" <?php checked( ! empty( $options['enable_slider'] ) ); ?> />
                                <?php esc_html_e( 'Cargar el slider incluso fuera de la portada.', 'anima-engine' ); ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Activar visor 3D / AR', 'anima-engine' ); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="anima_engine_options[enable_model_viewer]" value="1" <?php checked( ! empty( $options['enable_model_viewer'] ) ); ?> />
                                <?php esc_html_e( 'Permite cargar el visor 3D/AR en páginas personalizadas.', 'anima-engine' ); ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Habilitar caché global', 'anima-engine' ); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="anima_engine_options[enable_cache]" value="1" <?php checked( ! empty( $options['enable_cache'] ) ); ?> />
                                <?php esc_html_e( 'Utilizar transients para mejorar el rendimiento de consultas frecuentes.', 'anima-engine' ); ?>
                            </label>
                            <br />
                            <label>
                                <input type="checkbox" name="anima_engine_options[cache_catalog]" value="1" <?php checked( ! empty( $options['cache_catalog'] ) ); ?> />
                                <?php esc_html_e( 'Cachear el shortcode y API del catálogo.', 'anima-engine' ); ?>
                            </label>
                            <br />
                            <label>
                                <input type="checkbox" name="anima_engine_options[cache_entitlements]" value="1" <?php checked( ! empty( $options['cache_entitlements'] ) ); ?> />
                                <?php esc_html_e( 'Cachear la lista de licencias del usuario.', 'anima-engine' ); ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Activar esquema SEO', 'anima-engine' ); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="anima_engine_options[enable_schema]" value="1" <?php checked( ! empty( $options['enable_schema'] ) ); ?> />
                                <?php esc_html_e( 'Inyectar marcado JSON-LD para organización, cursos y experiencias.', 'anima-engine' ); ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Columnas del grid por defecto', 'anima-engine' ); ?></th>
                        <td>
                            <input type="number" min="1" max="6" name="anima_engine_options[grid_width]" value="<?php echo esc_attr( $options['grid_width'] ); ?>" />
                            <p class="description"><?php esc_html_e( 'Define la cantidad recomendada de columnas para galerías generadas dinámicamente.', 'anima-engine' ); ?></p>
                        </td>
                    </tr>
                </table>

                <h2><?php esc_html_e( 'Seguridad y API', 'anima-engine' ); ?></h2>
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Dominios permitidos (CORS)', 'anima-engine' ); ?></th>
                        <td>
                            <textarea name="anima_engine_options[cors_domains]" rows="4" cols="50" class="large-text code"><?php echo esc_textarea( $cors_value ); ?></textarea>
                            <p class="description"><?php esc_html_e( 'Introduce un dominio por línea. Se respetará el formato https://dominio.com', 'anima-engine' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Secreto JWT', 'anima-engine' ); ?></th>
                        <td>
                            <input type="text" class="regular-text" name="anima_engine_options[jwt_secret]" value="<?php echo esc_attr( $options['jwt_secret'] ); ?>" />
                            <p class="description"><?php esc_html_e( 'Utilizado para firmar tokens de acceso y refresco.', 'anima-engine' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'TTL token de acceso (segundos)', 'anima-engine' ); ?></th>
                        <td>
                            <input type="number" min="300" name="anima_engine_options[jwt_access_ttl]" value="<?php echo esc_attr( $options['jwt_access_ttl'] ); ?>" />
                            <p class="description"><?php esc_html_e( 'Duración del token de acceso emitido por la API. Mínimo 300 segundos.', 'anima-engine' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'TTL token de refresco (segundos)', 'anima-engine' ); ?></th>
                        <td>
                            <input type="number" min="<?php echo esc_attr( DAY_IN_SECONDS ); ?>" name="anima_engine_options[jwt_refresh_ttl]" value="<?php echo esc_attr( $options['jwt_refresh_ttl'] ); ?>" />
                            <p class="description"><?php esc_html_e( 'Duración del token de refresco. Mínimo 24 horas.', 'anima-engine' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'reCAPTCHA site key', 'anima-engine' ); ?></th>
                        <td>
                            <input type="text" class="regular-text" name="anima_engine_options[recaptcha_site_key]" value="<?php echo esc_attr( $options['recaptcha_site_key'] ); ?>" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'reCAPTCHA secret key', 'anima-engine' ); ?></th>
                        <td>
                            <input type="text" class="regular-text" name="anima_engine_options[recaptcha_secret_key]" value="<?php echo esc_attr( $options['recaptcha_secret_key'] ); ?>" />
                        </td>
                    </tr>
                </table>

                <h2><?php esc_html_e( 'Almacenamiento y Webhooks', 'anima-engine' ); ?></h2>
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Proveedor de archivos', 'anima-engine' ); ?></th>
                        <td>
                            <select name="anima_engine_options[storage_provider]">
                                <option value="wp_media" <?php selected( $options['storage_provider'], 'wp_media' ); ?>><?php esc_html_e( 'Biblioteca de medios de WordPress', 'anima-engine' ); ?></option>
                                <option value="s3" <?php selected( $options['storage_provider'], 's3' ); ?>><?php esc_html_e( 'Amazon S3 compatible', 'anima-engine' ); ?></option>
                                <option value="r2" <?php selected( $options['storage_provider'], 'r2' ); ?>><?php esc_html_e( 'Cloudflare R2', 'anima-engine' ); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Bucket o contenedor', 'anima-engine' ); ?></th>
                        <td><input type="text" class="regular-text" name="anima_engine_options[storage_bucket]" value="<?php echo esc_attr( $options['storage_bucket'] ); ?>" /></td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Access key', 'anima-engine' ); ?></th>
                        <td><input type="text" class="regular-text" name="anima_engine_options[storage_access_key]" value="<?php echo esc_attr( $options['storage_access_key'] ); ?>" /></td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Secret key', 'anima-engine' ); ?></th>
                        <td><input type="text" class="regular-text" name="anima_engine_options[storage_secret_key]" value="<?php echo esc_attr( $options['storage_secret_key'] ); ?>" /></td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Webhooks WooCommerce', 'anima-engine' ); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="anima_engine_options[enable_webhooks]" value="1" <?php checked( ! empty( $options['enable_webhooks'] ) ); ?> />
                                <?php esc_html_e( 'Validar peticiones entrantes con el secreto configurado.', 'anima-engine' ); ?>
                            </label>
                            <p><input type="text" class="regular-text" name="anima_engine_options[webhook_secret]" value="<?php echo esc_attr( $options['webhook_secret'] ); ?>" placeholder="<?php esc_attr_e( 'Clave secreta para firmas HMAC', 'anima-engine' ); ?>" /></p>
                        </td>
                    </tr>
                </table>

                <h2><?php esc_html_e( 'Páginas y productos', 'anima-engine' ); ?></h2>
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Página de inicio personalizada', 'anima-engine' ); ?></th>
                        <td><input type="text" name="anima_engine_options[page_home]" value="<?php echo esc_attr( $options['page_home'] ); ?>" /></td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Página de contacto', 'anima-engine' ); ?></th>
                        <td><input type="text" name="anima_engine_options[page_contact]" value="<?php echo esc_attr( $options['page_contact'] ); ?>" /></td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Página experiencia inmersiva', 'anima-engine' ); ?></th>
                        <td><input type="text" name="anima_engine_options[page_experience]" value="<?php echo esc_attr( $options['page_experience'] ); ?>" /></td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Página Anima Live', 'anima-engine' ); ?></th>
                        <td><input type="text" name="anima_engine_options[page_live]" value="<?php echo esc_attr( $options['page_live'] ); ?>" /></td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Producto suscripción principal', 'anima-engine' ); ?></th>
                        <td>
                            <input type="number" name="anima_engine_options[subscription_product_id]" value="<?php echo esc_attr( $options['subscription_product_id'] ); ?>" />
                            <p class="description"><?php esc_html_e( 'Identificador del producto WooCommerce que otorga la suscripción.', 'anima-engine' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Producto Anima Live', 'anima-engine' ); ?></th>
                        <td><input type="number" name="anima_engine_options[live_product_id]" value="<?php echo esc_attr( $options['live_product_id'] ); ?>" /></td>
                    </tr>
                </table>

                <?php submit_button(); ?>
            </form>

            <?php $this->render_assets_section( $assets, $asset_to_edit, $linked_product ); ?>
        </div>
        <?php
    }

    /**
     * Muestra los avisos de la página.
     */
    protected function render_notices(): void {
        settings_errors( 'anima_engine_settings' );

        $notice = isset( $_GET['anima_engine_notice'] ) ? sanitize_key( wp_unslash( (string) $_GET['anima_engine_notice'] ) ) : '';
        if ( '' === $notice ) {
            return;
        }

        $type = isset( $_GET['anima_engine_notice_type'] ) ? sanitize_key( wp_unslash( (string) $_GET['anima_engine_notice_type'] ) ) : 'success';
        $type = in_array( $type, [ 'success', 'error' ], true ) ? $type : 'success';

        $messages = [
            'asset_saved'        => __( 'El asset se ha guardado correctamente.', 'anima-engine' ),
            'asset_save_failed'  => __( 'No se pudo guardar el asset. Revisa los datos e inténtalo de nuevo.', 'anima-engine' ),
            'asset_deleted'      => __( 'El asset se ha eliminado.', 'anima-engine' ),
            'asset_delete_failed'=> __( 'No se pudo eliminar el asset seleccionado.', 'anima-engine' ),
        ];

        if ( ! isset( $messages[ $notice ] ) ) {
            return;
        }

        $class = 'notice notice-' . ( 'error' === $type ? 'error' : 'success' );

        printf(
            '<div class="%1$s"><p>%2$s</p></div>',
            esc_attr( $class ),
            esc_html( $messages[ $notice ] )
        );
    }

    /**
     * Renderiza la sección de gestión de assets.
     *
     * @param array<int, array<string, mixed>> $assets       Lista de assets disponibles.
     * @param array<string, mixed>|null         $asset       Asset seleccionado para edición.
     * @param int                               $product_id  Producto enlazado actualmente.
     */
    protected function render_assets_section( array $assets, ?array $asset, int $product_id ): void {
        $defaults = [
            'id'        => 0,
            'title'     => '',
            'slug'      => '',
            'type'      => 'skin',
            'media_url' => '',
            'version'   => '',
            'price'     => 0,
            'active'    => 1,
        ];

        $asset   = wp_parse_args( $asset ?? [], $defaults );
        $editing = (int) $asset['id'] > 0;

        $form_action = esc_url( admin_url( 'admin-post.php' ) );

        $product_name = '';
        if ( $product_id > 0 && function_exists( 'wc_get_product' ) ) {
            $product = wc_get_product( $product_id );
            if ( $product instanceof WC_Product ) {
                $product_name = $product->get_name();
            }
        }

        ?>
        <hr />
        <h2><?php esc_html_e( 'Assets del catálogo', 'anima-engine' ); ?></h2>
        <p><?php esc_html_e( 'Crea, edita y vincula assets con productos de WooCommerce para controlar su disponibilidad.', 'anima-engine' ); ?></p>

        <div class="card">
            <h3><?php echo $editing ? esc_html__( 'Editar asset', 'anima-engine' ) : esc_html__( 'Nuevo asset', 'anima-engine' ); ?></h3>
            <form method="post" action="<?php echo $form_action; ?>">
                <input type="hidden" name="action" value="anima_engine_save_asset" />
                <input type="hidden" name="asset_id" value="<?php echo esc_attr( (int) $asset['id'] ); ?>" />
                <?php wp_nonce_field( 'anima_engine_save_asset', 'anima_engine_asset_nonce' ); ?>

                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row"><label for="anima-asset-title"><?php esc_html_e( 'Título', 'anima-engine' ); ?></label></th>
                        <td><input type="text" id="anima-asset-title" name="title" value="<?php echo esc_attr( (string) $asset['title'] ); ?>" class="regular-text" required /></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="anima-asset-slug"><?php esc_html_e( 'Slug', 'anima-engine' ); ?></label></th>
                        <td><input type="text" id="anima-asset-slug" name="slug" value="<?php echo esc_attr( (string) $asset['slug'] ); ?>" class="regular-text" required /></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="anima-asset-type"><?php esc_html_e( 'Tipo', 'anima-engine' ); ?></label></th>
                        <td>
                            <select id="anima-asset-type" name="type">
                                <?php foreach ( $this->get_asset_types() as $value => $label ) : ?>
                                    <option value="<?php echo esc_attr( $value ); ?>" <?php selected( $asset['type'], $value ); ?>><?php echo esc_html( $label ); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="anima-asset-media"><?php esc_html_e( 'URL del recurso', 'anima-engine' ); ?></label></th>
                        <td><input type="url" id="anima-asset-media" name="media_url" value="<?php echo esc_attr( (string) $asset['media_url'] ); ?>" class="regular-text" required /></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="anima-asset-version"><?php esc_html_e( 'Versión', 'anima-engine' ); ?></label></th>
                        <td><input type="text" id="anima-asset-version" name="version" value="<?php echo esc_attr( (string) $asset['version'] ); ?>" class="regular-text" /></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="anima-asset-price"><?php esc_html_e( 'Precio sugerido', 'anima-engine' ); ?></label></th>
                        <td><input type="number" step="0.01" min="0" id="anima-asset-price" name="price" value="<?php echo esc_attr( (float) $asset['price'] ); ?>" /></td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Activo', 'anima-engine' ); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="active" value="1" <?php checked( (int) $asset['active'], 1 ); ?> />
                                <?php esc_html_e( 'Disponible en catálogo y APIs.', 'anima-engine' ); ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="anima-asset-product"><?php esc_html_e( 'Producto WooCommerce vinculado', 'anima-engine' ); ?></label></th>
                        <td>
                            <input type="number" id="anima-asset-product" name="product_id" value="<?php echo esc_attr( $product_id ); ?>" min="0" />
                            <p class="description">
                                <?php
                                if ( $product_name ) {
                                    printf(
                                        /* translators: %1$s product name, %2$d product id */
                                        esc_html__( 'Actualmente vinculado a %1$s (#%2$d).', 'anima-engine' ),
                                        esc_html( $product_name ),
                                        (int) $product_id
                                    );
                                } else {
                                    esc_html_e( 'Introduce el ID del producto que desbloquea este asset.', 'anima-engine' );
                                }
                                ?>
                            </p>
                        </td>
                    </tr>
                </table>

                <?php submit_button( $editing ? __( 'Actualizar asset', 'anima-engine' ) : __( 'Crear asset', 'anima-engine' ) ); ?>
            </form>
        </div>

        <h3><?php esc_html_e( 'Listado de assets', 'anima-engine' ); ?></h3>
        <table class="widefat fixed striped">
            <thead>
                <tr>
                    <th><?php esc_html_e( 'ID', 'anima-engine' ); ?></th>
                    <th><?php esc_html_e( 'Título', 'anima-engine' ); ?></th>
                    <th><?php esc_html_e( 'Slug', 'anima-engine' ); ?></th>
                    <th><?php esc_html_e( 'Tipo', 'anima-engine' ); ?></th>
                    <th><?php esc_html_e( 'Precio', 'anima-engine' ); ?></th>
                    <th><?php esc_html_e( 'Activo', 'anima-engine' ); ?></th>
                    <th><?php esc_html_e( 'Producto vinculado', 'anima-engine' ); ?></th>
                    <th><?php esc_html_e( 'Acciones', 'anima-engine' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if ( empty( $assets ) ) : ?>
                    <tr>
                        <td colspan="8"><?php esc_html_e( 'Aún no hay assets registrados.', 'anima-engine' ); ?></td>
                    </tr>
                <?php else : ?>
                    <?php foreach ( $assets as $item ) :
                        $item_id        = (int) $item['id'];
                        $item_product   = $this->find_product_id_for_asset( $item_id );
                        $product_status = $item_product ? get_post_status( $item_product ) : '';
                        $product_link   = $item_product ? get_permalink( $item_product ) : '';
                        ?>
                        <tr>
                            <td><?php echo esc_html( $item_id ); ?></td>
                            <td><?php echo esc_html( (string) $item['title'] ); ?></td>
                            <td><?php echo esc_html( (string) $item['slug'] ); ?></td>
                            <td><?php echo esc_html( (string) $item['type'] ); ?></td>
                            <td><?php echo esc_html( number_format_i18n( (float) ( $item['price'] ?? 0 ) , 2 ) ); ?></td>
                            <td><?php echo ! empty( $item['active'] ) ? esc_html__( 'Sí', 'anima-engine' ) : esc_html__( 'No', 'anima-engine' ); ?></td>
                            <td>
                                <?php if ( $item_product && $product_link ) : ?>
                                    <a href="<?php echo esc_url( $product_link ); ?>" target="_blank" rel="noopener noreferrer">
                                        <?php echo esc_html( sprintf( '#%d (%s)', $item_product, $product_status ?: __( 'sin estado', 'anima-engine' ) ) ); ?>
                                    </a>
                                <?php elseif ( $item_product ) : ?>
                                    <?php echo esc_html( sprintf( '#%d', $item_product ) ); ?>
                                <?php else : ?>
                                    <?php esc_html_e( 'Sin vincular', 'anima-engine' ); ?>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php
                                $edit_url = add_query_arg(
                                    [
                                        'page'     => 'anima-engine',
                                        'asset_id' => $item_id,
                                    ],
                                    admin_url( 'options-general.php' )
                                );
                                ?>
                                <a class="button button-secondary" href="<?php echo esc_url( $edit_url ); ?>"><?php esc_html_e( 'Editar', 'anima-engine' ); ?></a>
                                <form method="post" action="<?php echo $form_action; ?>" style="display:inline-block;">
                                    <input type="hidden" name="action" value="anima_engine_delete_asset" />
                                    <input type="hidden" name="asset_id" value="<?php echo esc_attr( $item_id ); ?>" />
                                    <?php wp_nonce_field( 'anima_engine_delete_asset', 'anima_engine_delete_nonce' ); ?>
                                    <button type="submit" class="button-link-delete" onclick="return confirm('<?php echo esc_js( __( '¿Seguro que deseas eliminar este asset?', 'anima-engine' ) ); ?>');"><?php esc_html_e( 'Eliminar', 'anima-engine' ); ?></button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        <?php
    }

    /**
     * Maneja la creación o actualización de assets.
     */
    public function handle_save_asset(): void {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_safe_redirect( admin_url( 'options-general.php?page=anima-engine&anima_engine_notice=asset_save_failed&anima_engine_notice_type=error' ) );
            exit;
        }

        check_admin_referer( 'anima_engine_save_asset', 'anima_engine_asset_nonce' );

        $asset_id  = isset( $_POST['asset_id'] ) ? absint( wp_unslash( (string) $_POST['asset_id'] ) ) : 0;
        $title     = isset( $_POST['title'] ) ? sanitize_text_field( wp_unslash( (string) $_POST['title'] ) ) : '';
        $slug      = isset( $_POST['slug'] ) ? sanitize_title( wp_unslash( (string) $_POST['slug'] ) ) : '';
        $type      = isset( $_POST['type'] ) ? sanitize_key( wp_unslash( (string) $_POST['type'] ) ) : 'skin';
        $media_url = isset( $_POST['media_url'] ) ? esc_url_raw( wp_unslash( (string) $_POST['media_url'] ) ) : '';
        $version   = isset( $_POST['version'] ) ? sanitize_text_field( wp_unslash( (string) $_POST['version'] ) ) : '';
        $price     = isset( $_POST['price'] ) ? (float) sanitize_text_field( wp_unslash( (string) $_POST['price'] ) ) : 0.0;
        $active    = ! empty( $_POST['active'] ) ? 1 : 0;
        $product   = isset( $_POST['product_id'] ) ? absint( wp_unslash( (string) $_POST['product_id'] ) ) : 0;

        if ( '' === $title || '' === $slug || '' === $media_url ) {
            $this->redirect_with_notice( 'asset_save_failed', 'error' );
        }

        $data = [
            'title'     => $title,
            'slug'      => $slug,
            'type'      => $type,
            'media_url' => $media_url,
            'version'   => $version,
            'price'     => $price,
            'active'    => $active,
        ];

        $is_update = $asset_id > 0;
        $result    = false;

        if ( $is_update ) {
            $result = $this->assets->update( $asset_id, $data );
        } else {
            $asset_id = $this->assets->create( $data );
            $result   = $asset_id > 0;
        }

        if ( ! $result || $asset_id <= 0 ) {
            $this->redirect_with_notice( 'asset_save_failed', 'error' );
        }

        $this->link_asset_to_product( $asset_id, $product );

        do_action( 'anima_engine_asset_saved', $asset_id, $data, $is_update );
        do_action( 'anima_engine_assets_changed', $asset_id );

        $this->redirect_with_notice( 'asset_saved', 'success' );
    }

    /**
     * Maneja la eliminación de assets.
     */
    public function handle_delete_asset(): void {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_safe_redirect( admin_url( 'options-general.php?page=anima-engine&anima_engine_notice=asset_delete_failed&anima_engine_notice_type=error' ) );
            exit;
        }

        check_admin_referer( 'anima_engine_delete_asset', 'anima_engine_delete_nonce' );

        $asset_id = isset( $_POST['asset_id'] ) ? absint( wp_unslash( (string) $_POST['asset_id'] ) ) : 0;

        if ( $asset_id <= 0 ) {
            $this->redirect_with_notice( 'asset_delete_failed', 'error' );
        }

        $this->link_asset_to_product( $asset_id, 0 );

        $deleted = $this->assets->delete( $asset_id );

        do_action( 'anima_engine_asset_deleted', $asset_id, $deleted );
        do_action( 'anima_engine_assets_changed', $asset_id );

        if ( ! $deleted ) {
            $this->redirect_with_notice( 'asset_delete_failed', 'error' );
        }

        $this->redirect_with_notice( 'asset_deleted', 'success' );
    }

    /**
     * Localiza el ID de producto asociado a un asset.
     */
    protected function find_product_id_for_asset( int $asset_id ): int {
        if ( $asset_id <= 0 || ! function_exists( 'wc_get_products' ) ) {
            return 0;
        }

        $products = wc_get_products(
            [
                'limit'      => 1,
                'meta_key'   => '_anima_asset_id',
                'meta_value' => $asset_id,
                'return'     => 'ids',
                'status'     => [ 'publish', 'private', 'draft' ],
            ]
        );

        if ( empty( $products ) ) {
            return 0;
        }

        return (int) $products[0];
    }

    /**
     * Vincula (o desvincula) un asset con un producto WooCommerce.
     */
    protected function link_asset_to_product( int $asset_id, int $product_id ): void {
        if ( ! function_exists( 'wc_get_product' ) ) {
            return;
        }

        $current_product_id = $this->find_product_id_for_asset( $asset_id );

        if ( $current_product_id && $current_product_id !== $product_id ) {
            $current_product = wc_get_product( $current_product_id );
            if ( $current_product instanceof WC_Product ) {
                $current_product->delete_meta_data( '_anima_asset_id' );
                $current_product->save();
            }
        }

        if ( $product_id <= 0 ) {
            return;
        }

        $product = wc_get_product( $product_id );
        if ( ! $product instanceof WC_Product ) {
            return;
        }

        $product->update_meta_data( '_anima_asset_id', $asset_id );
        $product->save();
    }

    /**
     * Obtiene los tipos de asset soportados.
     *
     * @return array<string, string>
     */
    protected function get_asset_types(): array {
        return [
            'skin'        => __( 'Skin', 'anima-engine' ),
            'environment' => __( 'Entorno', 'anima-engine' ),
            'avatar'      => __( 'Avatar', 'anima-engine' ),
        ];
    }

    /**
     * Redirige a la página de opciones con un aviso.
     */
    protected function redirect_with_notice( string $code, string $type ): void {
        $url = add_query_arg(
            [
                'page'                     => 'anima-engine',
                'anima_engine_notice'      => $code,
                'anima_engine_notice_type' => $type,
            ],
            admin_url( 'options-general.php' )
        );

        wp_safe_redirect( $url );
        exit;
    }


}
