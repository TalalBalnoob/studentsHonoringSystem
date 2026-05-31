<?php

namespace App\Repositories;

use App\Enums\Governorate;
use App\Models\Student;

class StudentRepository {
    public function getFiltered(array $filters, string $sortBy, string $sortOrder, int $perPage) {
        $query = Student::query()->with('additionalImages');

        if (! empty($filters['gender'])) {
            $query->where('gender', $filters['gender']);
        }

        if (isset($filters['governorate']) && is_numeric($filters['governorate'])) {
            $query->where('governorate', $filters['governorate']);
        }

        // Filter by class
        if (! empty($filters['class'])) {
            $query->where('class', $filters['class']);
        }

        // Filter by grade range
        if (! empty($filters['gradeMoreThen'])) {
            $query->where('grade', '>=', $filters['gradeMoreThen']);
        }
        if (! empty($filters['gradeLessThen'])) {
            $query->where('grade', '<=', $filters['gradeLessThen']);
        }

        // Filter by school name
        if (! empty($filters['school_name'])) {
            $query->where('school_name', 'like', '%' . $filters['school_name'] . '%');
        }

        // Search across name fields and school name
        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('first_name',  'like', '%' . $search . '%')
                    ->orWhere('second_name', 'like', '%' . $search . '%')
                    ->orWhere('third_name',  'like', '%' . $search . '%')
                    ->orWhere('last_name',   'like', '%' . $search . '%')
                    ->orWhere('school_name', 'like', '%' . $search . '%');
            });
        }

        $paginated = $query->orderBy($sortBy, $sortOrder)->paginate($perPage);

        $paginated->getCollection()->transform(function ($student) {
            $student->governorate = Governorate::getName($student->governorate);
            return $student;
        });

        return $paginated;
    }

    public function delete(Student $student): void {
        $student->delete();
    }

    public function update(Student $student, array $data): Student {
        $student->update($data);
        return $student;
    }

    public function create(array $data): Student {
        return Student::create($data);
    }

    public function findOrFail(int $id): Student {
        return Student::with('additionalImages')->findOrFail($id);
    }
}
