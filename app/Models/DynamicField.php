<?php

namespace App\Models;

use Database\Factories\DynamicFieldFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DynamicField extends Model {
	/** @use HasFactory<DynamicFieldFactory> */
	use HasFactory;

	/**
	 * @var array<string, string>
	 */
	protected $fillable = [
		'field_name',
		'field_type',
		'is_visible',
		'order',
	];

	/**
	 * @var array<string, string>
	 */
	protected $casts = [
		'is_visible' => 'boolean',
		'order' => 'integer',
	];
}
