<?php

namespace App\Models;

use Database\Factories\StudentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model {
	/** @use HasFactory<StudentFactory> */
	use HasFactory;

	/**
	 * @var array<string, string>
	 */
	protected $fillable = [
		'first_name',
		'second_name',
		'third_name',
		'last_name',
		'class',
		'school_name',
		'grade',
		'image',
		'gender',
		'governorate',
		'custom_data',
	];

	/**
	 * @var array<string, string>
	 */
	protected $casts = [
		'custom_data' => 'array',
		'grade' => 'integer',
		'class' => 'integer',
	];
}
