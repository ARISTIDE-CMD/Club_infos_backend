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
            ],
            'default_sorting_field' => 'student_id'
        ];

        // Créer la collection si elle n'existe pas
        try {
            $this->client->collections['students']->retrieve();
        } catch (\Exception $e) {
            $this->client->collections->create($schema);
        }

        // Indexer chaque étudiant
        foreach ($students as $student) {
            $this->client->collections['students']->documents->upsert([
                'id' => (string)$student['id'],
                'first_name' => $student['first_name'],
                'last_name' => $student['last_name'],
                'student_id' => $student['student_id'],
                'class_group' => $student['class_group']
            ]);
        }
    }
}
