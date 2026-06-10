<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAdminStudentRequest;
use App\Http\Requests\UpdateStudentRequest;
use App\Services\StudentCsvExportService;
use App\Services\StudentsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminStudentController extends Controller {
    public function __construct(
        private StudentCsvExportService $csvExportService,
        private StudentsService $studentsService,
    ) {
    }

    /**
     * Display a listing of students with filtering, searching, and sorting.
     */
    public function index(Request $request): JsonResponse {
        // Validate inputs upfront instead of trusting has()/input() blindly
        $validated = $request->validate([
            'class'         => ['sometimes', 'integer', 'min:1', 'max:12'],
            'gender'        => ['sometimes', 'in:male,female'],
            'gradeMoreThen' => ['sometimes', 'numeric', 'min:0', 'max:100'],
            'gradeLessThen' => ['sometimes', 'numeric', 'min:0', 'max:100'],
            'school_name'   => ['sometimes', 'string', 'max:255'],
            'search'        => ['sometimes', 'string', 'max:255'],
            'gavernorate'    => ['sometimes', 'integer', 'min:1', 'max:12'],
            'sort_by'       => ['sometimes', 'string'],
            'sort_order'    => ['sometimes', 'in:asc,desc'],
            'per_page'      => ['sometimes', 'integer', 'min:1', 'max:100'],
        ]);

        $query = $this->studentsService->getAllStudents($validated);


        return response()->json($query);
    }
    /**
     * Display the specified student.
     */
    public function show(string $id): JsonResponse {
        $student = $this->studentsService->getStudentById($id);

        return response()->json([
            'data' => $student,
        ]);
    }

    /**
     * Store a newly created student.
     */
    public function store(StoreAdminStudentRequest $request): JsonResponse {
        try {
            $validated = $request->validated();
            $student = $this->studentsService->createNewStudent($validated);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }

        return response()->json([
            'message' => 'Student created successfully',
            'data' => $student,
        ], 201);
    }

    /**
     * Update the specified student.
     */
    public function update(UpdateStudentRequest $request, string $id): JsonResponse {

        try {
            $validated = $request->validated();
            $updatedStudent = $this->studentsService->updateStudent($id, $validated);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 404);
        }

        return response()->json([
            'message' => 'Student updated successfully',
            'data' => $updatedStudent,
        ]);
    }

    /**
     * Remove the specified student.
     */
    public function destroy(string $id): JsonResponse {
        try {
            $this->studentsService->deleteStudent($id);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 404);
        }

        return response()->json([
            'message' => 'Student deleted successfully',
        ]);
    }

    /**
     * Export students to CSV file with optional filters.
     */
    public function exportCsv(Request $request): StreamedResponse {
        $filters = $request->all();
        $query = $this->csvExportService->buildQuery($filters);

        $filename = 'students-' . now()->format('Y-m-d-His') . '.csv';

        return $this->csvExportService->export($query, $filename);
    }
}
