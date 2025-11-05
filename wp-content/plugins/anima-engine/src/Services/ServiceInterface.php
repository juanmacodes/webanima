<?php
namespace Anima\Engine\Services;

/**
 * Interfaz básica para servicios del plugin.
 */
interface ServiceInterface {
    /**
     * Registra los hooks del servicio.
     */
    public function register(): void;
}
