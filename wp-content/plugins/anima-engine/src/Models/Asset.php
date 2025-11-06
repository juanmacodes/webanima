<?php
namespace Anima\Engine\Models;

use wpdb;

/**
 * Modelo para la tabla de assets personalizados.
 */
class Asset
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
        'slug'       => '%s',
        'type'       => '%s',
        'title'      => '%s',
        'media_url'  => '%s',
        'version'    => '%s',
        'price'      => '%f',
        'active'     => '%d',
        'created_at' => '%s',
    ];

    /**
     * Constructor.
     */
    public function __construct(?wpdb $db = null)
    {
        global $wpdb;

        $this->db    = $db ?? $wpdb;
        $this->table = $this->db->prefix . 'anima_assets';
    }

    /**
     * Crea un registro nuevo.
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
     * Obtiene un asset por ID.
     */
    public function getById(int $id): ?array
    {
        $sql = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = %d", $id);

        $row = $this->db->get_row($sql, ARRAY_A);

        return $row ?: null;
    }

    /**
     * Obtiene un asset por slug.
     */
    public function getBySlug(string $slug): ?array
    {
        $sql = $this->db->prepare("SELECT * FROM {$this->table} WHERE slug = %s", $slug);

        $row = $this->db->get_row($sql, ARRAY_A);

        return $row ?: null;
    }

    /**
     * Lista todos los assets con filtros opcionales.
     *
     * @param array{type?: string|null, active?: int|null, limit?: int|null} $args
     */
    public function getAll(array $args = []): array
    {
        $defaults = [
            'type'   => null,
            'active' => null,
            'limit'  => null,
        ];

        $args     = array_merge($defaults, $args);
        $where    = [];
        $params   = [];

        if (null !== $args['type']) {
            $where[]  = 'type = %s';
            $params[] = $args['type'];
        }

        if (null !== $args['active']) {
            $where[]  = 'active = %d';
            $params[] = (int) $args['active'];
        }

        $sql = "SELECT * FROM {$this->table}";

        if (! empty($where)) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        if (null !== $args['limit']) {
            $sql      .= ' LIMIT %d';
            $params[] = (int) $args['limit'];
        }

        if (! empty($params)) {
            $sql = $this->db->prepare($sql, $params);
        }

        $results = $this->db->get_results($sql, ARRAY_A);

        return $results ?: [];
    }

    /**
     * Actualiza un asset.
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
     * Elimina un asset.
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
     * Filtra los datos para incluir solo columnas válidas.
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

            if ('active' === $key) {
                $value = (int) $value;
            }

            if ('price' === $key) {
                $value = (float) $value;
            }

            $filtered[$key] = $value;
        }

        return $filtered;
    }

    /**
     * Obtiene los formatos de columnas para los datos filtrados.
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
