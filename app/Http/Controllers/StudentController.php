<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStudentRequest;
use App\Models\Student;
use App\Repositories\StudentRepository;
use App\Services\CertificateStorageService;
use App\Services\StudentsService;
use Illuminate\Http\JsonResponse;

class StudentController extends Controller {
    public function __construct(
        private CertificateStorageService $storageService,
        private StudentRepository $studentRepository,
        private StudentsService $studentsService
    ) {
    }

    /**
     * Store a newly created student record from public form submission.
     */
    public function store(StoreStudentRequest $request): JsonResponse {
        $validated = $request->validated();

        try {
            $student = $this->studentsService->createNewStudent($validated);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create student record: ' . $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'message' => 'Student record created successfully',
            'data' => $student,
        ], 201);
    }
}
