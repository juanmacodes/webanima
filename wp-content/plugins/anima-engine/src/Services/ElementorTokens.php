<?php
namespace Anima\Engine\Services;

use Elementor\Plugin as ElementorPlugin;

/**
 * Sincroniza los tokens de marca con los colores y tipografías globales de Elementor.
 */
class ElementorTokens implements ServiceInterface {
    /**
     * Registra los hooks necesarios.
     */
    public function register(): void {
        add_action( 'elementor/init', [ $this, 'register_tokens' ] );
    }

    /**
     * Inserta los tokens cuando Elementor está disponible.
     */
    public function register_tokens(): void {
        if ( ! is_admin() ) {
            return;
        }

        if ( ! class_exists( ElementorPlugin::class ) ) {
            return;
        }

        $elementor = ElementorPlugin::instance();

        if ( ! $elementor || ! isset( $elementor->kits_manager ) ) {
            return;
        }

        $kits_manager = $elementor->kits_manager;
        $kit          = null;

        if ( method_exists( $kits_manager, 'get_active_kit' ) ) {
            $kit = $kits_manager->get_active_kit();
        }

        if ( ! $kit && method_exists( $kits_manager, 'get_active_kit_for_frontend' ) ) {
            $kit = $kits_manager->get_active_kit_for_frontend();
        }

        if ( ! $kit ) {
            return;
        }

        $color_tokens      = $this->merge_tokens(
            $kit->get_settings_for_display( 'custom_colors' ),
            $this->get_color_tokens(),
            'color'
        );
        $typography_tokens = $this->merge_tokens(
            $kit->get_settings_for_display( 'custom_typography' ),
            $this->get_typography_tokens(),
            'typography_typography'
        );

        $updates = [];

        if ( $color_tokens['updated'] ) {
            $updates['custom_colors'] = $color_tokens['tokens'];
        }

        if ( $typography_tokens['updated'] ) {
            $updates['custom_typography'] = $typography_tokens['tokens'];
        }

        if ( empty( $updates ) ) {
            return;
        }

        $kit->update_settings( $updates );
    }

    /**
     * Fusiona los tokens manteniendo los valores personalizados existentes.
     *
     * @param mixed $existing Tokens actuales almacenados por Elementor.
     * @param array<int, array<string, string>> $defaults Tokens por defecto del tema.
     * @param string $value_key Clave que contiene el valor principal del token.
     *
     * @return array{tokens: array<int, array<string, mixed>>, updated: bool}
     */
    private function merge_tokens( $existing, array $defaults, string $value_key ): array {
        $existing_tokens = is_array( $existing ) ? $existing : [];
        $updated         = false;

        foreach ( $defaults as $default ) {
            if ( empty( $default['_id'] ) ) {
                continue;
            }

            $index = $this->find_token_index( $existing_tokens, $default['_id'] );

            if ( null === $index ) {
                $existing_tokens[] = $default;
                $updated           = true;
                continue;
            }

            $current_value = $existing_tokens[ $index ][ $value_key ] ?? '';
            $should_update = '' === trim( (string) $current_value ) || 'default' === $current_value;

            if ( ! $should_update ) {
                continue;
            }

            if ( isset( $default[ $value_key ] ) ) {
                $existing_tokens[ $index ][ $value_key ] = $default[ $value_key ];
            }

            if ( isset( $default['title'] ) && empty( $existing_tokens[ $index ]['title'] ) ) {
                $existing_tokens[ $index ]['title'] = $default['title'];
            }

            foreach ( $default as $key => $default_value ) {
                if ( in_array( $key, [ '_id', $value_key, 'title' ], true ) ) {
                    continue;
                }

                if ( empty( $existing_tokens[ $index ][ $key ] ) ) {
                    $existing_tokens[ $index ][ $key ] = $default_value;
                }
            }

            $updated = true;
        }

        return [
            'tokens'  => $existing_tokens,
            'updated' => $updated,
        ];
    }

    /**
     * Localiza el índice de un token dentro del array existente.
     *
     * @param array<int, array<string, mixed>> $tokens Tokens actuales.
     * @param string $id Identificador buscado.
     */
    private function find_token_index( array $tokens, string $id ): ?int {
        foreach ( $tokens as $index => $token ) {
            if ( isset( $token['_id'] ) && $token['_id'] === $id ) {
                return $index;
            }
        }

        return null;
    }

    /**
     * Tokens de color que se sincronizan con Elementor.
     *
     * @return array<int, array<string, string>>
     */
    private function get_color_tokens(): array {
        return [
            [
                '_id'   => 'anima-primary',
                'title' => __( 'Anima Primario', 'anima-engine' ),
                'color' => '#6F4BF6',
            ],
            [
                '_id'   => 'anima-secondary',
                'title' => __( 'Anima Secundario', 'anima-engine' ),
                'color' => '#2FD4FF',
            ],
            [
                '_id'   => 'anima-tertiary',
                'title' => __( 'Anima Destello', 'anima-engine' ),
                'color' => '#F94FD7',
            ],
            [
                '_id'   => 'anima-surface',
                'title' => __( 'Anima Superficie', 'anima-engine' ),
                'color' => '#131326',
            ],
            [
                '_id'   => 'anima-text',
                'title' => __( 'Anima Texto', 'anima-engine' ),
                'color' => '#F5F7FF',
            ],
        ];
    }

    /**
     * Tokens tipográficos sincronizados con Elementor.
     *
     * @return array<int, array<string, string>>
     */
    private function get_typography_tokens(): array {
        return [
            [
                '_id'                           => 'anima-headings',
                'title'                         => __( 'Anima Titulares', 'anima-engine' ),
                'typography_typography'         => 'custom',
                'typography_font_family'        => 'Rajdhani',
                'typography_font_weight'        => '700',
                'typography_transform'          => 'uppercase',
                'typography_line_height'        => '1.1',
                'typography_letter_spacing'     => '0.08em',
            ],
            [
                '_id'                           => 'anima-body',
                'title'                         => __( 'Anima Texto base', 'anima-engine' ),
                'typography_typography'         => 'custom',
                'typography_font_family'        => 'Inter',
                'typography_font_weight'        => '400',
                'typography_line_height'        => '1.6',
                'typography_letter_spacing'     => '0em',
            ],
        ];
    }
}
