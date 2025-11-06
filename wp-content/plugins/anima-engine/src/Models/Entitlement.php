<?php
namespace Anima\Engine\Models;

use wpdb;

/**
 * Modelo para gestionar los permisos/licencias de los usuarios.
 */
class Entitlement
{
    /**
     * Instancia de base de datos.
     */
    protected wpdb $db;

    /**
     * Nombre de la tabla asociada.
     */
    protected string $table;

    /**
     * Formatos aceptados para las columnas de la tabla.
     *
     * @var array<string, string>
     */
    protected array $columnFormats = [
        'user_id'      => '%d',
        'asset_id'     => '%d',
        'asset_type'   => '%s',
        'license_key'  => '%s',
        'source_order' => '%d',
        'expires_at'   => '%s',
        'created_at'   => '%s',
    ];

    /**
     * Constructor.
     */
    public function __construct(?wpdb $db = null)
    {
        global $wpdb;

        $this->db    = $db ?? $wpdb;
        $this->table = $this->db->prefix . 'anima_entitlements';
    }

    /**
     * Crea un nuevo registro de licencia.
     */
    public function create(array $data): int
    {
        $data = $this->filterData($data);

        if (empty($data)) {
            return 0;
        }

        $formats = $this->formatsFor($data);
        $result  = $this->db->insert($this->table, $data, $formats);

        if (false === $result) {
            return 0;
        }

        return (int) $this->db->insert_id;
    }

    /**
     * Obtiene una licencia por su ID.
     */
    public function getById(int $id): ?array
    {
        $sql = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = %d", $id);

        $row = $this->db->get_row($sql, ARRAY_A);

        return $row ?: null;
    }

    /**
     * Obtiene una licencia por su clave.
     */
    public function getByLicenseKey(string $licenseKey): ?array
    {
        $sql = $this->db->prepare(
            "SELECT * FROM {$this->table} WHERE license_key = %s",
            $licenseKey
        );

        $row = $this->db->get_row($sql, ARRAY_A);

        return $row ?: null;
    }

    /**
     * Obtiene todas las licencias para un usuario.
     */
    public function getForUser(int $userId): array
    {
        $sql = $this->db->prepare(
            "SELECT * FROM {$this->table} WHERE user_id = %d ORDER BY created_at DESC",
            $userId
        );

        $results = $this->db->get_results($sql, ARRAY_A);

        return $results ?: [];
    }

    /**
     * Obtiene las licencias de un usuario incluyendo datos del asset.
     */
    public function getWithAssetsForUser(int $userId): array
    {
        $assets_table = $this->db->prefix . 'anima_assets';

        $sql = $this->db->prepare(
            "SELECT e.*, a.title, a.media_url, a.type FROM {$this->table} e LEFT JOIN {$assets_table} a ON e.asset_id = a.id WHERE e.user_id = %d ORDER BY e.created_at DESC",
            $userId
        );

        $results = $this->db->get_results($sql, ARRAY_A);

        return $results ?: [];
    }

    /**
     * Busca una licencia por usuario y asset.
     */
    public function findForUserAsset(int $userId, int $assetId): ?array
    {
        $sql = $this->db->prepare(
            "SELECT * FROM {$this->table} WHERE user_id = %d AND asset_id = %d LIMIT 1",
            $userId,
            $assetId
        );

        $row = $this->db->get_row($sql, ARRAY_A);

        return $row ?: null;
    }

    /**
     * Verifica si el usuario tiene licencia para un asset.
     */
    public function userHasEntitlement(int $userId, int $assetId): bool
    {
        return null !== $this->findForUserAsset($userId, $assetId);
    }

    /**
     * Actualiza una licencia existente.
     */
    public function update(int $id, array $data): bool
    {
        $data = $this->filterData($data);

        if (empty($data)) {
            return false;
        }

        $formats = $this->formatsFor($data);

        $updated = $this->db->update(
            $this->table,
            $data,
            [ 'id' => $id ],
            $formats,
            [ '%d' ]
        );

        return false !== $updated;
    }

    /**
     * Elimina un registro de licencia.
     */
    public function delete(int $id): bool
    {
        $deleted = $this->db->delete(
            $this->table,
            [ 'id' => $id ],
            [ '%d' ]
        );

        return false !== $deleted;
    }

    /**
     * Filtra la información recibida.
     *
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     */
    protected function filterData(array $data): array
    {
        $filtered = [];

        foreach ($data as $key => $value) {
            if (! isset($this->columnFormats[$key])) {
                continue;
            }

            if (in_array($key, ['user_id', 'asset_id', 'source_order'], true)) {
                $value = null === $value ? null : (int) $value;
            }

            $filtered[$key] = $value;
        }

        return $filtered;
    }

    /**
     * Obtiene los formatos asociados a los datos filtrados.
     *
     * @param array<string, mixed> $data
     *
     * @return array<int, string>
     */
    protected function formatsFor(array $data): array
    {
        $formats = [];

        foreach (array_keys($data) as $key) {
            $formats[] = $this->columnFormats[$key];
        }

        return $formats;
    }
}
