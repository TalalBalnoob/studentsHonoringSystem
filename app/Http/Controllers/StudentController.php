<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStudentRequest;
use App\Models\Student;
use App\Services\CertificateStorageService;
use Illuminate\Http\JsonResponse;

class StudentController extends Controller {
    public function __construct(private CertificateStorageService $storageService) {
    }

    /**
     * Store a newly created student record from public form submission.
     */
    public function store(StoreStudentRequest $request): JsonResponse {
        $validated = $request->validated([
            'first_name' => ['required', 'string', 'max:255'],
            'second_name' => ['required', 'string', 'max:255'],
            'third_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'gender' => ['required', 'in:male,female'],
            'governorate' => ['required', 'integer', 'min:1', 'max:12'],
            'class' => ['required', 'integer', 'min:1', 'max:12'],
            'school_name' => ['required', 'string', 'max:255'],
            'grade' => ['required', 'numeric', 'min:0', 'max:100'],
            'image' => ['required', 'file', 'image', 'mimes:jpeg,png,jpg,gif', 'max:51200'], // 50MB
        ]);

        // Upload image
        $imagePath = $this->storageService->uploadCertificate($request->file('image'));

        // Create student
        $student = Student::create([
            'first_name' => $validated['first_name'],
            'second_name' => $validated['second_name'],
            'third_name' => $validated['third_name'],
            'last_name' => $validated['last_name'],
            'class' => $validated['class'],
            'school_name' => $validated['school_name'],
            'grade' => $validated['grade'],
            'gender' => $validated['gender'],
            'governorate' => $validated['governorate'],
            'image' => $imagePath,
        ]);

        return response()->json([
            'message' => 'Student record created successfully',
            'data' => $student,
        ], 201);
    }
}
