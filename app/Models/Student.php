<?php

namespace App\Models;

use Database\Factories\StudentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model {
    use HasFactory;

    protected $fillable = [
        'first_name',
        'second_name',
        'third_name',
        'last_name',
        'class',
        'school_name',
        'grade',
        'cert_image',
        'gender',
        'governorate',
        'phone1',
        'phone2',
        'address',
        'qiyes_grade',
        'SAAT_grade',
        'SAAT_cert_image',
        'qiyes_cert_image',
    ];

    protected $casts = [
        'grade' => 'decimal:2',
        'qiyes_grade' => 'decimal:2',
        'SAAT_grade' => 'decimal:2',
        'class' => 'integer',
    ];

    public function additionalImages(): HasMany {
        return $this->hasMany(AddImage::class);
    }
}
