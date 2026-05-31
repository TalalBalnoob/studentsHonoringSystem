<?php

namespace App\Services;

use App\Jobs\DeleteStudentCertificates;
use App\Models\AddImage;
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
        $allowed = ['class', 'grade', 'created_at', 'first_name', 'last_name', 'qiyes_grade', 'SAAT_grade'];
        $sortBy = in_array($filters['sort_by'] ?? null, $allowed) ? $filters['sort_by'] : 'created_at';
        $sortOrder = in_array($filters['sort_order'] ?? null, ['asc', 'desc']) ? $filters['sort_order'] : 'desc';
        $perPage = min($filters['per_page'] ?? 50, 100);

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
        // Handle main certificate image upload
        if (isset($data['cert_image'])) {
            $data['cert_image'] = $this->storageService->uploadCertificate($data['cert_image']);
        }

        if ($data['class'] == 12) {
            // Handle additional certificate images
            if (isset($data['SAAT_cert_image'])) {
                $data['SAAT_cert_image'] = $this->storageService->uploadCertificate($data['SAAT_cert_image']);
            }
            if (isset($data['qiyes_cert_image'])) {
                $data['qiyes_cert_image'] = $this->storageService->uploadCertificate($data['qiyes_cert_image']);
            }
        }

        // Extract and remove additional_images before saving the student
        $additionalImages = $data['additional_images'] ?? null;
        if (isset($data['additional_images'])) {
            unset($data['additional_images']);
        }

        $student = $this->studentRepository->create($data);

        // Save additional images to add_images table
        if ($additionalImages && is_array($additionalImages)) {
            foreach ($additionalImages as $image) {
                $imagePath = $this->storageService->uploadCertificate($image);
                AddImage::create([
                    'student_id' => $student->id,
                    'image_path' => $imagePath,
                ]);
            }
        }

        return $student->load('additionalImages');
    }

    public function updateStudent(int $id, array $data) {
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

        // Extract and remove additional_images before updating the student
        $additionalImages = $data['additional_images'] ?? null;
        if (isset($data['additional_images'])) {
            unset($data['additional_images']);
        }

        $updatedStudent = $this->studentRepository->update($student, $data);

        // Save new additional images to add_images table
        if ($additionalImages && is_array($additionalImages)) {
            foreach ($additionalImages as $image) {
                $imagePath = $this->storageService->uploadCertificate($image);
                AddImage::create([
                    'student_id' => $student->id,
                    'image_path' => $imagePath,
                ]);
            }
        }

        return $updatedStudent->load('additionalImages');
    }

    public function deleteStudent(int $id) {
        try {
            $student = $this->studentRepository->findOrFail($id);
        } catch (\Exception $e) {
            throw new \Exception("Student with ID {$id} not found.");
        }

        // Collect all certificate paths
        $certificatesToDelete = [
            $student->cert_image,
            $student->SAAT_cert_image,
            $student->qiyes_cert_image,
        ];

        // Add all additional image paths
        foreach ($student->additionalImages as $addImage) {
            if ($addImage->image_path) {
                $this->storageService->deleteCertificate($addImage->image_path);
                $certificatesToDelete[] = $addImage->image_path;
            }
        }

        $student->additionalImages()->delete();


        return $this->studentRepository->delete($student);
    }
}
