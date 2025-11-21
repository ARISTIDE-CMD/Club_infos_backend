<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Student;
use App\Services\TypesenseService;

class TypesenseIndexStudents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'typesense:students:index {--class_group=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Indexe les étudiants dans Typesense (optionnel: --class_group=XYZ)';

    protected TypesenseService $typesenseService;

    public function __construct(TypesenseService $typesenseService)
    {
        parent::__construct();
        $this->typesenseService = $typesenseService;
    }

    public function handle()
    {
        $this->info('Récupération des étudiants...');

        $query = Student::with('teacher.user');

        if ($this->option('class_group')) {
            $query->where('class_group', $this->option('class_group'));
        }

        $students = $query->get();

        $studentsArray = $students->map(function ($student) {
            return [
                'id' => $student->id,
                'first_name' => $student->first_name,
                'last_name' => $student->last_name,
                'student_id' => $student->student_id,
                'class_group' => $student->class_group,
                'teacher_id' => $student->teacher_id ?? null,
                'teacher_name' => $student->teacher ? $student->teacher->user->name : null,
            ];
        })->toArray();

        $this->info('Indexation vers Typesense...');
        $this->typesenseService->indexStudents($studentsArray);

        $this->info('Indexation terminée. Total indexés: ' . count($studentsArray));
        return 0;
    }
}
