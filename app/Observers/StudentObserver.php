<?php
namespace App\Observers;

use App\Models\Student;
use App\Services\TypesenseService;

class StudentObserver
{
    protected TypesenseService $typesense;

    public function __construct()
    {
        $this->typesense = app(TypesenseService::class);
    }

    public function created(Student $student): void
    {
        $this->upsertToTypesense($student);
    }

    public function updated(Student $student): void
    {
        $this->upsertToTypesense($student);
    }

    public function deleted(Student $student): void
    {
        // supprimer le document dans Typesense
        try {
            $this->typesense->deleteStudent((string)$student->id);
        } catch (\Exception $e) {
            // ne pas empêcher la suppression en base si Typesense échoue
            // on peut logger l'erreur si un logger est disponible
            logger()->error('Typesense deleteStudent error: ' . $e->getMessage());
        }
    }

    protected function upsertToTypesense(Student $student): void
    {
        $doc = [
            'id' => (string)$student->id,
            'first_name' => $student->first_name,
            'last_name' => $student->last_name,
            'student_id' => $student->student_id,
            'class_group' => $student->class_group,
            'teacher_id' => $student->teacher_id ?? null,
            'teacher_name' => $student->teacher?->user?->name ?? null,
        ];

        try {
            $this->typesense->upsertStudent($doc);
        } catch (\Exception $e) {
            logger()->error('Typesense upsertStudent error: ' . $e->getMessage());
        }
    }
}

