<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DynamicField;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminFieldController extends Controller
{
    /**
     * Display a listing of all dynamic fields.
     */
    public function index(): JsonResponse
    {
        $fields = DynamicField::orderBy('order')->get();

        return response()->json([
            'data' => $fields,
        ]);
    }

    /**
     * Store a newly created dynamic field.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'field_name' => ['required', 'string', 'max:255', 'unique:dynamic_fields'],
            'field_type' => ['required', 'string', 'in:text,number,date,email,url'],
            'is_visible' => ['sometimes', 'boolean'],
        ]);

        $field = DynamicField::create([
            'field_name' => $validated['field_name'],
            'field_type' => $validated['field_type'],
            'is_visible' => $validated['is_visible'] ?? true,
            'order' => DynamicField::max('order') + 1,
        ]);

        return response()->json([
            'message' => 'Dynamic field created successfully',
            'data' => $field,
        ], 201);
    }

    /**
     * Remove the specified dynamic field.
     */
    public function destroy(string $id): JsonResponse
    {
        $field = DynamicField::findOrFail($id);
        $field->delete();

        return response()->json([
            'message' => 'Dynamic field deleted successfully',
        ]);
    }
}
