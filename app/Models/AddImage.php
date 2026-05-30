<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AddImage extends Model
{
    /**
     * @var array<string, string>
     */
    protected $fillable = [
        'student_id',
        'image_path',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
