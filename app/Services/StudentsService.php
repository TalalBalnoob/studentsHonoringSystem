<?php

namespace App\Services;

use App\Repositories\StudentRepository;

class StudentsService
{
    // This service can be used for any shared logic related to students that doesn't fit into the repository or controller

    public function __construct(
        private CertificateStorageService $storageService,
        private StudentCsvExportService $csvExportService,
        private StudentRepository $studentRepository,
    ) {}

    public function getAllStudents(array $filters = [])
    {
        $allowed = ['class', 'grade', 'created_at', 'first_name', 'last_name'];
        $sortBy = in_array($filters['sort_by'] ?? null, $allowed) ? $filters['sort_by'] : 'created_at';
        $sortOrder = in_array($filters['sort_order'] ?? null, ['asc', 'desc']) ? $filters['sort_order'] : 'desc';
        $perPage = min($filters['per_page'] ?? 50, 100);

        $query = $this->studentRepository->getFiltered($filters, $sortBy, $sortOrder, $perPage);

        return $query;
    }

    public function getStudentById(int $id)
    {
        try {
            return $student = $this->studentRepository->findOrFail($id);
        } catch (\Exception $e) {
            throw new \Exception("Student with ID {$id} not found.");
        }
    }

    public function createNewStudent(array $data)
    {
        // Handle main certificate image upload
        if (isset($data['cert_image'])) {
            $data['cert_image'] = $this->storageService->uploadCertificate($data['cert_image']);
        }

        // Handle additional certificate images
        if (isset($data['SAAT_cert_image'])) {
            $data['SAAT_cert_image'] = $this->storageService->uploadCertificate($data['SAAT_cert_image']);
        }
        if (isset($data['qiyes_cert_image'])) {
            $data['qiyes_cert_image'] = $this->storageService->uploadCertificate($data['qiyes_cert_image']);
        }
        if (isset($data['other_cert_image'])) {
            $data['other_cert_image'] = $this->storageService->uploadCertificate($data['other_cert_image']);
        }

        return $this->studentRepository->create($data);
    }

    public function updateStudent(int $id, array $data)
    {
        try {
            $student = $this->studentRepository->findOrFail($id);
        } catch (\Exception $e) {
            throw new \Exception("Student with ID {$id} not found.");
        }

        // Handle main certificate image update
        if (isset($data['cert_image'])) {
            if ($student->cert_image) {
                $this->storageService->deleteCertificate($student->cert_image);
            }
            $data['cert_image'] = $this->storageService->uploadCertificate($data['cert_image']);
        }

        // Handle SAAT certificate image update
        if (isset($data['SAAT_cert_image'])) {
            if ($student->SAAT_cert_image) {
                $this->storageService->deleteCertificate($student->SAAT_cert_image);
            }
            $data['SAAT_cert_image'] = $this->storageService->uploadCertificate($data['SAAT_cert_image']);
        }

        // Handle QIYES certificate image update
        if (isset($data['qiyes_cert_image'])) {
            if ($student->qiyes_cert_image) {
                $this->storageService->deleteCertificate($student->qiyes_cert_image);
            }
            $data['qiyes_cert_image'] = $this->storageService->uploadCertificate($data['qiyes_cert_image']);
        }

        // Handle other certificate image update
        if (isset($data['other_cert_image'])) {
            if ($student->other_cert_image) {
                $this->storageService->deleteCertificate($student->other_cert_image);
            }
            $data['other_cert_image'] = $this->storageService->uploadCertificate($data['other_cert_image']);
        }

        return $this->studentRepository->update($student, $data);
    }

    public function deleteStudent(int $id)
    {
        try {
            $student = $this->studentRepository->findOrFail($id);
        } catch (\Exception $e) {
            throw new \Exception("Student with ID {$id} not found.");
        }

        // Delete all associated certificate images
        if ($student->cert_image) {
            $this->storageService->deleteCertificate($student->cert_image);
        }
        if ($student->SAAT_cert_image) {
            $this->storageService->deleteCertificate($student->SAAT_cert_image);
        }
        if ($student->qiyes_cert_image) {
            $this->storageService->deleteCertificate($student->qiyes_cert_image);
        }
        if ($student->other_cert_image) {
            $this->storageService->deleteCertificate($student->other_cert_image);
        }

        return $this->studentRepository->delete($student);
    }
}
