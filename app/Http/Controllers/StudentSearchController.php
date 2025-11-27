<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\TypesenseService;

class StudentSearchController extends Controller
{
    protected TypesenseService $typesense;

    public function __construct(TypesenseService $typesense)
    {
        $this->typesense = $typesense;
    }

    public function search(Request $request)
    {
        $q = $request->query('q', '');
        if ($q === '') {
            return response()->json(['message' => 'Missing query parameter `q`.'], 422);
        }

        $options = [
    'query_by' => $request->query('query_by', 'first_name,last_name,class_group,student_id'),
    'per_page' => (int)$request->query('per_page', 50),
    'page' => (int)$request->query('page', 1),
    // Ajoute ces deux lignes :
    'prefix' => $request->query('prefix', 'true'), // active le prefix search
    'max_candidates' => (int)$request->query('max_candidates', 10), // pour typo / prefix
];


        if ($request->has('filter_by')) {
            $options['filter_by'] = $request->query('filter_by');
        }

        try {
            $results = $this->typesense->searchStudents($q, $options);
            return response()->json($results);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Search failed', 'error' => $e->getMessage()], 500);
        }
    }
}
