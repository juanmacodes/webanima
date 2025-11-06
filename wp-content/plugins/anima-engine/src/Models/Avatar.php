<?php
namespace Anima\Engine\Models;

use wpdb;

/**
 * Modelo para gestionar los avatares de usuario.
 */
class Avatar
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
        'user_id'    => '%d',
        'glb_url'    => '%s',
        'poster_url' => '%s',
        'updated_at' => '%s',
        'created_at' => '%s',
    ];

    /**
     * Constructor.
     */
    public function __construct(?wpdb $db = null)
    {
        global $wpdb;

        $this->db    = $db ?? $wpdb;
        $this->table = $this->db->prefix . 'anima_avatars';
    }

    /**
     * Crea un avatar.
     */
    public function create(array $data): int
    {
        $data = $this->filterData($data);

        if (empty($data) || ! isset($data['user_id'])) {
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
     * Inserta o actualiza un avatar según el usuario.
     */
    public function saveForUser(int $userId, array $data): bool
    {
        $data['user_id'] = $userId;
        $data            = $this->filterData($data);

        if (empty($data['user_id'])) {
            return false;
        }

        $formats = $this->formatsFor($data);

        $result = $this->db->replace($this->table, $data, $formats);

        return false !== $result;
    }

    /**
     * Obtiene un avatar por su ID.
     */
    public function getById(int $id): ?array
    {
        $sql = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = %d", $id);

        $row = $this->db->get_row($sql, ARRAY_A);

        return $row ?: null;
    }

    /**
     * Obtiene un avatar por ID de usuario.
     */
    public function getByUserId(int $userId): ?array
    {
        $sql = $this->db->prepare(
            "SELECT * FROM {$this->table} WHERE user_id = %d",
            $userId
        );

        $row = $this->db->get_row($sql, ARRAY_A);

        return $row ?: null;
    }

    /**
     * Actualiza un avatar por ID.
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
     * Elimina un avatar por ID.
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
     * Elimina un avatar por usuario.
     */
    public function deleteByUserId(int $userId): bool
    {
        $deleted = $this->db->delete(
            $this->table,
            [ 'user_id' => $userId ],
            [ '%d' ]
        );

        return false !== $deleted;
    }

    /**
     * Filtra los datos para asegurarse de que solo se guardan columnas válidas.
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

            if ('user_id' === $key) {
                $value = null === $value ? null : (int) $value;
            }

            $filtered[$key] = $value;
        }

        return $filtered;
    }

    /**
     * Obtiene el formato adecuado para los datos filtrados.
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
