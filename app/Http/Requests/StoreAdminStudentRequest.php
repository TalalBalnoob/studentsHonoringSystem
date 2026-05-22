<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreAdminStudentRequest extends FormRequest {
	/**
	 * Determine if the user is authorized to make this request.
	 */
	public function authorize(): bool {
		return true; // Admin-only check is handled by middleware
	}

	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array<string, ValidationRule|array<mixed>|string>
	 */
	public function rules(): array {
		return [
			'first_name' => ['required', 'string', 'max:255'],
			'second_name' => ['required', 'string', 'max:255'],
			'third_name' => ['required', 'string', 'max:255'],
			'last_name' => ['required', 'string', 'max:255'],
			'gender' => ['required', 'in:male,female'],
			'governorate' => ['required', 'string', 'max:255'],
			'class' => ['required', 'integer', 'min:1', 'max:12'],
			'school_name' => ['required', 'string', 'max:255'],
			'grade' => ['required', 'numeric', 'min:0', 'max:100'],
			'image' => ['sometimes', 'file', 'image', 'mimes:jpeg,png,jpg,gif', 'max:51200'],
		];
	}
}
