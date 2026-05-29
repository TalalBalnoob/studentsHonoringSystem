<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreStudentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Anonymous students can submit
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:255'],
            'second_name' => ['required', 'string', 'max:255'],
            'third_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'gender' => ['required', 'in:male,female'],
            'governorate' => ['required', 'integer', 'min:1', 'max:12'],
            'class' => ['required', 'integer', 'min:1', 'max:12'],
            'school_name' => ['required', 'string', 'max:255'],
            'grade' => ['required', 'numeric', 'min:0', 'max:100'],
            'cert_image' => ['required', 'file', 'image', 'mimes:jpeg,png,jpg,gif', 'max:51200'],
            'phone1' => ['nullable', 'string', 'max:20'],
            'phone2' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:255'],
            'qiyes_grade' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'SAAT_grade' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'SAAT_cert_image' => ['nullable', 'file', 'image', 'mimes:jpeg,png,jpg,gif', 'max:51200'],
            'qiyes_cert_image' => ['nullable', 'file', 'image', 'mimes:jpeg,png,jpg,gif', 'max:51200'],
            'other_cert_image' => ['nullable', 'file', 'image', 'mimes:jpeg,png,jpg,gif', 'max:51200'],
        ];
    }
}
