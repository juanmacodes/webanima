<?php
namespace Anima\Engine\Admin;

use Anima\Engine\Services\ServiceInterface;

use function add_options_page;
use function add_action;
use function checked;
use function esc_attr;
use function esc_html__;
use function esc_html_e;
use function get_option;
use function register_setting;
use function sanitize_text_field;
use function settings_fields;
use function submit_button;
use function wp_nonce_field;

/**
 * Página de opciones del plugin.
 */
class OptionsPage implements ServiceInterface {
    /**
     * {@inheritDoc}
     */
    public function register(): void {
        add_action( 'admin_menu', [ $this, 'register_menu' ] );
        add_action( 'admin_init', [ $this, 'register_settings' ] );
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
        $clean['enable_slider']       = isset( $options['enable_slider'] ) ? (bool) $options['enable_slider'] : false;
        $clean['enable_model_viewer'] = isset( $options['enable_model_viewer'] ) ? (bool) $options['enable_model_viewer'] : false;
        $clean['grid_width']          = isset( $options['grid_width'] ) ? (int) $options['grid_width'] : 3;
        $clean['page_home']           = isset( $options['page_home'] ) ? sanitize_text_field( $options['page_home'] ) : '';
        $clean['page_contact']        = isset( $options['page_contact'] ) ? sanitize_text_field( $options['page_contact'] ) : '';
        $clean['page_experience']     = isset( $options['page_experience'] ) ? sanitize_text_field( $options['page_experience'] ) : '';
        $clean['page_live']           = isset( $options['page_live'] ) ? sanitize_text_field( $options['page_live'] ) : '';

        return $clean;
    }

    /**
     * Renderiza la página de opciones.
     */
    public function render_page(): void {
        $options = get_option( 'anima_engine_options', [] );
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Anima Engine — Configuración', 'anima-engine' ); ?></h1>
            <p><?php esc_html_e( 'Activa funciones opcionales y define páginas clave para integraciones.', 'anima-engine' ); ?></p>
            <form action="options.php" method="post">
                <?php settings_fields( 'anima_engine_settings' ); ?>
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
                        <th scope="row"><?php esc_html_e( 'Activar Model Viewer', 'anima-engine' ); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="anima_engine_options[enable_model_viewer]" value="1" <?php checked( ! empty( $options['enable_model_viewer'] ) ); ?> />
                                <?php esc_html_e( 'Permite cargar el visor 3D en páginas personalizadas.', 'anima-engine' ); ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Columnas del grid por defecto', 'anima-engine' ); ?></th>
                        <td>
                            <input type="number" min="1" max="6" name="anima_engine_options[grid_width]" value="<?php echo esc_attr( $options['grid_width'] ?? 3 ); ?>" />
                            <p class="description"><?php esc_html_e( 'Define la cantidad recomendada de columnas para galerías generadas dinámicamente.', 'anima-engine' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Página de inicio personalizada', 'anima-engine' ); ?></th>
                        <td><input type="text" name="anima_engine_options[page_home]" value="<?php echo esc_attr( $options['page_home'] ?? '' ); ?>" /></td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Página de contacto', 'anima-engine' ); ?></th>
                        <td><input type="text" name="anima_engine_options[page_contact]" value="<?php echo esc_attr( $options['page_contact'] ?? '' ); ?>" /></td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Página experiencia inmersiva', 'anima-engine' ); ?></th>
                        <td><input type="text" name="anima_engine_options[page_experience]" value="<?php echo esc_attr( $options['page_experience'] ?? '' ); ?>" /></td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Página Anima Live', 'anima-engine' ); ?></th>
                        <td><input type="text" name="anima_engine_options[page_live]" value="<?php echo esc_attr( $options['page_live'] ?? '' ); ?>" /></td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
}
