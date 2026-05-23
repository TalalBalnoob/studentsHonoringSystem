<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAdminStudentRequest;
use App\Http\Requests\UpdateStudentRequest;
use App\Models\Student;
use App\Services\CertificateStorageService;
use App\Services\StudentCsvExportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminStudentController extends Controller {
    public function __construct(
        private CertificateStorageService $storageService,
        private StudentCsvExportService $csvExportService
    ) {
    }

    /**
     * Display a listing of students with filtering, searching, and sorting.
     */
    public function index(Request $request): JsonResponse {
        // Validate inputs upfront instead of trusting has()/input() blindly
        $request->validate([
            'class'         => ['sometimes', 'integer', 'min:1', 'max:12'],
            'gradeMoreThen' => ['sometimes', 'numeric', 'min:0', 'max:100'],
            'gradeLessThen' => ['sometimes', 'numeric', 'min:0', 'max:100'],
            'school_name'   => ['sometimes', 'string', 'max:255'],
            'search'        => ['sometimes', 'string', 'max:255'],
            'sort_by'       => ['sometimes', 'string'],
            'sort_order'    => ['sometimes', 'in:asc,desc'],
            'per_page'      => ['sometimes', 'integer', 'min:1', 'max:100'],
        ]);

        $query = Student::query();

        // Filter by class
        if ($request->filled('class')) {
            $query->where('class', $request->input('class'));
        }

        // Filter by grade range
        if ($request->filled('gradeMoreThen')) {
            $query->where('grade', '>=', $request->input('gradeMoreThen'));
        }
        if ($request->filled('gradeLessThen')) {
            $query->where('grade', '<=', $request->input('gradeLessThen'));
        }

        // Filter by school name
        if ($request->filled('school_name')) {
            $query->where('school_name', 'like', '%' . $request->input('school_name') . '%');
        }

        // Search across name fields and school name
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('first_name',  'like', '%' . $search . '%')
                    ->orWhere('second_name', 'like', '%' . $search . '%')
                    ->orWhere('third_name',  'like', '%' . $search . '%')
                    ->orWhere('last_name',   'like', '%' . $search . '%')
                    ->orWhere('school_name', 'like', '%' . $search . '%');
            });
        }

        // Sort — whitelist prevents SQL injection through sort parameters
        $allowed  = ['class', 'grade', 'created_at', 'first_name', 'last_name'];
        $sortBy    = in_array($request->input('sort_by'), $allowed)
            ? $request->input('sort_by')
            : 'created_at';
        $sortOrder = $request->input('sort_order', 'desc'); // already validated above

        $query->orderBy($sortBy, $sortOrder);

        // Paginate — cap at 100 rows per page to protect the DB
        $students = $query->paginate($request->input('per_page', 50));

        return response()->json($students);
    }
    /**
     * Display the specified student.
     */
    public function show(string $id): JsonResponse {
        $student = Student::findOrFail($id);

        return response()->json([
            'data' => $student,
        ]);
    }

    /**
     * Store a newly created student.
     */
    public function store(StoreAdminStudentRequest $request): JsonResponse {
        $validated = $request->validated();

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $this->storageService->uploadCertificate($request->file('image'));
        }

        $student = Student::create([
            'first_name' => $validated['first_name'],
            'second_name' => $validated['second_name'],
            'third_name' => $validated['third_name'],
            'last_name' => $validated['last_name'],
            'gender' => $validated['gender'],
            'governorate' => $validated['governorate'],
            'class' => $validated['class'],
            'school_name' => $validated['school_name'],
            'grade' => $validated['grade'],
            'image' => $imagePath,
        ]);

        return response()->json([
            'message' => 'Student created successfully',
            'data' => $student,
        ], 201);
    }

    /**
     * Update the specified student.
     */
    public function update(UpdateStudentRequest $request, string $id): JsonResponse {
        $student = Student::findOrFail($id);
        $validated = $request->validated();

        // Handle image update
        if ($request->hasFile('image')) {
            // Delete old image
            if ($student->image) {
                $this->storageService->deleteCertificate($student->image);
            }

            $validated['image'] = $this->storageService->uploadCertificate($request->file('image'));
        }

        $student->update($validated);

        return response()->json([
            'message' => 'Student updated successfully',
            'data' => $student,
        ]);
    }

    /**
     * Remove the specified student.
     */
    public function destroy(string $id): JsonResponse {
        $student = Student::findOrFail($id);

        // Delete image
        if ($student->image) {
            $this->storageService->deleteCertificate($student->image);
        }

        $student->delete();

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
