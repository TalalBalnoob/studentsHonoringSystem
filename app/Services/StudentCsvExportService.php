<?php

namespace App\Services;

use App\Models\Student;
use Illuminate\Database\Eloquent\Builder;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Enums\Governorate;

class StudentCsvExportService {
    /**
     * Export students to CSV file
     */
    public function export(Builder $query, string $filename = 'students.csv'): StreamedResponse {
        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($query) {
            $file = fopen('php://output', 'w');
            if ($file === false) {
                throw new \RuntimeException('Unable to open php://output for writing');
            }

            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Write headers
            fputcsv($file, [
                'ID',
                'First Name',
                'Second Name',
                'Third Name',
                'Last Name',
                'phone1',
                'phone2',
                'address',
                'Gender',
                'Governorate',
                'Class',
                'School Name',
                'Grade',
                'certificate_image',
                'Qiyes_grade',
                'SAAT_grade',
                'SAAT_cert_image',
                'Qiyes_cert_image',
                'additional_cert_image',
                'Created At',
                'Updated At',
            ]);

            // Write student data (eager-load additional images)
            $query->with('additionalImages')->chunk(100, function ($students) use ($file) {
                foreach ($students as $student) {
                    // Build additional image links array
                    $additionalLinks = $student->additionalImages->map(function ($img) {
                        return 'https://vpkxfiywlhsdowqosuqi.supabase.co/storage/v1/object/public/certificates/' . $img->image_path;
                    })->values()->toArray();

                    fputcsv($file, [
                        $student->id,
                        $student->first_name,
                        $student->second_name,
                        $student->third_name,
                        $student->last_name,
                        $student->phone1,
                        $student->phone2,
                        $student->address,
                        $student->gender,
                        // Convert governorate code to name
                        Governorate::getName($student->governorate),
                        $student->class,
                        $student->school_name,
                        $student->grade,
                        $student->cert_image ? 'https://vpkxfiywlhsdowqosuqi.supabase.co/storage/v1/object/public/certificates/' . $student->cert_image : null,
                        $student->qiyes_grade,
                        $student->SAAT_grade,
                        $student->SAAT_cert_image ? 'https://vpkxfiywlhsdowqosuqi.supabase.co/storage/v1/object/public/certificates/' . $student->SAAT_cert_image : null,
                        $student->qiyes_cert_image ? 'https://vpkxfiywlhsdowqosuqi.supabase.co/storage/v1/object/public/certificates/' . $student->qiyes_cert_image : null,
                        // JSON-encode the array of additional cert image links
                        json_encode($additionalLinks, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),

                        $student->created_at,
                        $student->updated_at,
                    ]);
                }
            });

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Build query with filters
     */
    public function buildQuery(array $filters): Builder {
        $query = Student::query();

        if (isset($filters['class'])) {
            $query->where('class', $filters['class']);
        }

        if (isset($filters['governorate'])) {
            $query->where('governorate', $filters['governorate']);
        }

        if (isset($filters['grade'])) {
            $query->where('grade', $filters['grade']);
        }

        if (isset($filters['school_name'])) {
            $query->where('school_name', 'like', '%' . $filters['school_name'] . '%');
        }

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', '%' . $search . '%')
                    ->orWhere('last_name', 'like', '%' . $search . '%')
                    ->orWhere('school_name', 'like', '%' . $search . '%');
            });
        }

        // Sort
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';

        if (in_array($sortBy, ['class', 'grade', 'created_at', 'first_name', 'last_name', 'governorate'])) {
            $query->orderBy($sortBy, in_array($sortOrder, ['asc', 'desc']) ? $sortOrder : 'desc');
        }

        return $query;
    }
}
