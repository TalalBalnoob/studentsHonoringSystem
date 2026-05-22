<?php

namespace Tests\Feature;

use App\Models\DynamicField;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminFieldManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create(['is_admin' => true]);
    }

    /**
     * Test admin can list dynamic fields.
     */
    public function test_admin_can_list_dynamic_fields(): void
    {
        DynamicField::factory()->count(3)->create();
        $token = $this->adminUser->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/admin/fields');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => ['id', 'field_name', 'field_type', 'is_visible', 'order'],
            ],
        ]);
    }

    /**
     * Test admin can create dynamic field.
     */
    public function test_admin_can_create_dynamic_field(): void
    {
        $token = $this->adminUser->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/admin/fields', [
                'field_name' => 'achievements',
                'field_type' => 'text',
                'is_visible' => true,
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('dynamic_fields', [
            'field_name' => 'achievements',
            'field_type' => 'text',
        ]);
    }

    /**
     * Test admin cannot create duplicate field names.
     */
    public function test_admin_cannot_create_duplicate_field_name(): void
    {
        DynamicField::create([
            'field_name' => 'achievements',
            'field_type' => 'text',
        ]);

        $token = $this->adminUser->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/admin/fields', [
                'field_name' => 'achievements',
                'field_type' => 'number',
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['field_name']);
    }

    /**
     * Test admin can delete dynamic field.
     */
    public function test_admin_can_delete_dynamic_field(): void
    {
        $field = DynamicField::factory()->create();
        $token = $this->adminUser->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->deleteJson("/api/admin/fields/{$field->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('dynamic_fields', ['id' => $field->id]);
    }

    /**
     * Test non-admin cannot create dynamic fields.
     */
    public function test_non_admin_cannot_create_dynamic_field(): void
    {
        $user = User::factory()->create(['is_admin' => false]);
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/admin/fields', [
                'field_name' => 'achievements',
                'field_type' => 'text',
            ]);

        $response->assertStatus(403);
    }
}
