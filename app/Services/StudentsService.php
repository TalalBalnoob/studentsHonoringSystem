<?php

namespace App\Services;

use App\Enums\Governorate;
use App\Repositories\StudentRepository;

class StudentsService {
    // This service can be used for any shared logic related to students that doesn't fit into the repository or controller

    public function __construct(
        private CertificateStorageService $storageService,
        private StudentCsvExportService $csvExportService,
        private StudentRepository $studentRepository,
    ) {
    }

    public function getAllStudents(array $filters = []) {
        $allowed   = ['class', 'grade', 'created_at', 'first_name', 'last_name'];
        $sortBy    = in_array($filters['sort_by'] ?? null, $allowed) ? $filters['sort_by'] : 'created_at';
        $sortOrder = in_array($filters['sort_order'] ?? null, ['asc', 'desc']) ? $filters['sort_order'] : 'desc';
        $perPage   = min($filters['per_page'] ?? 50, 100);

        $query = $this->studentRepository->getFiltered($filters, $sortBy, $sortOrder, $perPage);

        return $query;
    }

    public function getStudentById(int $id) {
        try {
            return $student = $this->studentRepository->findOrFail($id);
        } catch (\Exception $e) {
            throw new \Exception("Student with ID {$id} not found.");
        }
    }

    public function createNewStudent(array $data) {
        // Handle image upload
        if (isset($data['image'])) {
            $data['image'] = $this->storageService->uploadCertificate($data['image']);
        }

        return $this->studentRepository->create($data);
    }

    public function updateStudent(int $id, array $data) {
        try {
            $student = $this->studentRepository->findOrFail($id);
        } catch (\Exception $e) {
            throw new \Exception("Student with ID {$id} not found.");
        }

        // Handle image update
        if (isset($data['image'])) {
            // Delete old image if exists
            if ($student->image) {
                $this->storageService->deleteCertificate($student->image);
            }
            $data['image'] = $this->storageService->uploadCertificate($data['image']);
        }

        return $this->studentRepository->update($student, $data);
    }

    public function deleteStudent(int $id) {
        try {
            $student = $this->studentRepository->findOrFail($id);
        } catch (\Exception $e) {
            throw new \Exception("Student with ID {$id} not found.");
        }

        // Delete associated image if exists
        if ($student->image) {
            $this->storageService->deleteCertificate($student->image);
        }

        return $this->studentRepository->delete($student);
    }
}
