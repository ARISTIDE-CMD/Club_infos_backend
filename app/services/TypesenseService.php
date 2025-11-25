<?php
namespace App\Services;

use Typesense\Client;

class TypesenseService
{
    protected $client;

    public function __construct()
    {
        $this->client = new Client(config('typesense'));
    }

    public function indexStudents(array $students)
    {
        $schema = [
            'name' => 'students',
            'fields' => [
                ['name' => 'id', 'type' => 'string'],
                ['name' => 'first_name', 'type' => 'string'],
                ['name' => 'last_name', 'type' => 'string'],
                ['name' => 'student_id', 'type' => 'string'],
                ['name' => 'class_group', 'type' => 'string'],
                ['name' => 'teacher_id', 'type' => 'int64'],
                ['name' => 'teacher_name', 'type' => 'string'],
            ],
            // Pas de default_sorting_field ici : Typesense exige un champ numérique unique
        ];

        // Créer la collection si elle n'existe pas.
        // Si elle existe mais que le schéma diffère, supprimer et recréer pour garantir la cohérence.
        try {
            $existing = $this->client->collections['students']->retrieve();

            // extraire les noms de champs existants
            $existingFieldNames = array_map(function ($f) {
                return $f['name'];
            }, $existing['fields'] ?? []);

            $desiredFieldNames = array_map(function ($f) {
                return $f['name'];
            }, $schema['fields']);

            sort($existingFieldNames);
            sort($desiredFieldNames);

            if ($existingFieldNames !== $desiredFieldNames) {
                // supprimer et recréer
                try {
                    $this->client->collections['students']->delete();
                } catch (\Exception $e) {
                    // ignore delete errors and try to create anyway
                }

                $this->client->collections->create($schema);
            }
        } catch (\Exception $e) {
            $this->client->collections->create($schema);
        }

        // Indexer chaque étudiant. On accepte des tableaux associatifs ou des modèles transformés en array.
        foreach ($students as $student) {
            // normaliser en tableau si c'est un objet
            $s = is_array($student) ? $student : (array)$student;

            $doc = [
                'id' => (string)($s['id'] ?? ''),
                'first_name' => $s['first_name'] ?? null,
                'last_name' => $s['last_name'] ?? null,
                'student_id' => $s['student_id'] ?? null,
                'class_group' => $s['class_group'] ?? null,
                'teacher_id' => isset($s['teacher_id']) ? (int)$s['teacher_id'] : null,
                'teacher_name' => $s['teacher_name'] ?? null,
            ];

            // Supprimer les champs nulls — Typesense rejette les valeurs null/non définies
            $doc = array_filter($doc, function ($v) { return !is_null($v); });

            // Si le document est vide ou n'a pas d'identifiant valide, on l'ignore.
            if (empty($doc) || !isset($doc['id']) || $doc['id'] === '') {
                continue;
            }

            $this->client->collections['students']->documents->upsert($doc);
        }
    }

    /**
     * Search students in Typesense.
     *
     * @param string $q
     * @param array $options Supported keys: query_by, per_page, page, filter_by, sort_by
     * @return array Typesense response
     */
    public function searchStudents(string $q, array $options = []): array
    {
        $params = [
            'q' => $q,
            'query_by' => $options['query_by'] ?? 'first_name,last_name,class_group',
            'per_page' => $options['per_page'] ?? 10,
            'page' => $options['page'] ?? 1,
        ];

        if (!empty($options['filter_by'])) {
            $params['filter_by'] = $options['filter_by'];
        }

        if (!empty($options['sort_by'])) {
            $params['sort_by'] = $options['sort_by'];
        }

        $response = $this->client->collections['students']->documents->search($params);

        return is_array($response) ? $response : (array)$response;
    }

    /**
     * Upsert a single student document into Typesense.
     * @param array $doc
     * @return void
     */
    public function upsertStudent(array $doc): void
    {
        // normalize and remove nulls
        $doc['id'] = (string)($doc['id'] ?? '');
        if (isset($doc['teacher_id'])) {
            $doc['teacher_id'] = is_numeric($doc['teacher_id']) ? (int)$doc['teacher_id'] : null;
        }

        $doc = array_filter($doc, function ($v) { return !is_null($v); });

        if (empty($doc) || !isset($doc['id']) || $doc['id'] === '') {
            return;
        }

        $this->client->collections['students']->documents->upsert($doc);
    }

    /**
     * Delete a student document by id
     * @param string $id
     * @return void
     */
    public function deleteStudent(string $id): void
    {
        if ($id === '') return;

        try {
            $this->client->collections['students']->documents[$id]->delete();
        } catch (\Exception $e) {
            // rethrow to let caller handle logging if desired
            throw $e;
        }
    }
}
