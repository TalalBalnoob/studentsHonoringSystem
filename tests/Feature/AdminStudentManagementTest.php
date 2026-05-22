<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminStudentManagementTest extends TestCase {
	use RefreshDatabase;

	private User $adminUser;

	protected function setUp(): void {
		parent::setUp();

		$this->adminUser = User::factory()->create([
			'is_admin' => true,
			'email' => 'admin@test.com',
		]);
	}

	/**
	 * Test unauthenticated user cannot access admin endpoints.
	 */
	public function test_unauthenticated_user_cannot_list_students(): void {
		$response = $this->getJson('/api/admin/students');

		$response->assertStatus(401);
	}

	/**
	 * Test non-admin user cannot access admin endpoints.
	 */
	public function test_non_admin_user_cannot_list_students(): void {
		$user = User::factory()->create(['is_admin' => false]);
		$token = $user->createToken('test')->plainTextToken;

		$response = $this->withHeader('Authorization', "Bearer $token")
			->getJson('/api/admin/students');

		$response->assertStatus(403);
		$response->assertJson(['message' => 'Unauthorized. Admin access required.']);
	}

	/**
	 * Test admin can list students.
	 */
	public function test_admin_can_list_students(): void {
		Student::factory()->count(3)->create();
		$token = $this->adminUser->createToken('test')->plainTextToken;

		$response = $this->withHeader('Authorization', "Bearer $token")
			->getJson('/api/admin/students');

		$response->assertStatus(200);
		$response->assertJsonStructure([
			'data' => [
				'*' => ['id', 'first_name', 'last_name', 'class', 'school_name', 'grade'],
			],
		]);
	}

	/**
	 * Test admin can view single student.
	 */
	public function test_admin_can_view_single_student(): void {
		$student = Student::factory()->create();
		$token = $this->adminUser->createToken('test')->plainTextToken;

		$response = $this->withHeader('Authorization', "Bearer $token")
			->getJson("/api/admin/students/{$student->id}");

		$response->assertStatus(200);
		$response->assertJson([
			'data' => [
				'id' => $student->id,
				'first_name' => $student->first_name,
			],
		]);
	}

	/**
	 * Test admin can filter students by class.
	 */
	public function test_admin_can_filter_students_by_class(): void {
		Student::factory()->create(['class' => 10]);
		Student::factory()->create(['class' => 11]);
		Student::factory()->create(['class' => 12]);

		$token = $this->adminUser->createToken('test')->plainTextToken;

		$response = $this->withHeader('Authorization', "Bearer $token")
			->getJson('/api/admin/students?class=10');

		$response->assertStatus(200);
		$this->assertCount(1, $response->json('data'));
		$this->assertEquals(10, $response->json('data.0.class'));
	}

	/**
	 * Test admin can filter students by grade.
	 */
	public function test_admin_can_filter_students_by_grade(): void {
		Student::factory()->create(['grade' => 90]);
		Student::factory()->create(['grade' => 85]);
		Student::factory()->create(['grade' => 92]);

		$token = $this->adminUser->createToken('test')->plainTextToken;

		$response = $this->withHeader('Authorization', "Bearer $token")
			->getJson('/api/admin/students?grade=90');

		$response->assertStatus(200);
		$this->assertCount(1, $response->json('data'));
		$this->assertEquals(90, $response->json('data.0.grade'));
	}

	/**
	 * Test admin can search students by name.
	 */
	public function test_admin_can_search_students_by_name(): void {
		Student::factory()->create(['first_name' => 'John', 'last_name' => 'Doe']);
		Student::factory()->create(['first_name' => 'Jane', 'last_name' => 'Smith']);
		Student::factory()->create(['first_name' => 'Johnny', 'last_name' => 'Walker']);

		$token = $this->adminUser->createToken('test')->plainTextToken;

		$response = $this->withHeader('Authorization', "Bearer $token")
			->getJson('/api/admin/students?search=John');

		$response->assertStatus(200);
		$this->assertCount(2, $response->json('data'));
	}

	/**
	 * Test admin can sort students by class.
	 */
	public function test_admin_can_sort_students_by_class(): void {
		Student::factory()->create(['class' => 12]);
		Student::factory()->create(['class' => 10]);
		Student::factory()->create(['class' => 11]);

		$token = $this->adminUser->createToken('test')->plainTextToken;

		$response = $this->withHeader('Authorization', "Bearer $token")
			->getJson('/api/admin/students?sort_by=class&sort_order=asc');

		$response->assertStatus(200);
		$this->assertEquals(10, $response->json('data.0.class'));
		$this->assertEquals(11, $response->json('data.1.class'));
		$this->assertEquals(12, $response->json('data.2.class'));
	}

	/**
	 * Test admin can sort students by grade.
	 */
	public function test_admin_can_sort_students_by_grade(): void {
		Student::factory()->create(['grade' => 92]);
		Student::factory()->create(['grade' => 85]);
		Student::factory()->create(['grade' => 90]);

		$token = $this->adminUser->createToken('test')->plainTextToken;

		$response = $this->withHeader('Authorization', "Bearer $token")
			->getJson('/api/admin/students?sort_by=grade&sort_order=desc');

		$response->assertStatus(200);
		$this->assertEquals(92, $response->json('data.0.grade'));
		$this->assertEquals(90, $response->json('data.1.grade'));
		$this->assertEquals(85, $response->json('data.2.grade'));
	}

	/**
	 * Test admin can create student manually.
	 */
	public function test_admin_can_create_student(): void {
		$token = $this->adminUser->createToken('test')->plainTextToken;

		$response = $this->withHeader('Authorization', "Bearer $token")
			->postJson('/api/admin/students', [
				'first_name' => 'Test',
				'second_name' => 'Admin',
				'third_name' => 'Created',
				'last_name' => 'Student',
				'gender' => 'male',
				'governorate' => 'Riyadh',
				'class' => 10,
				'school_name' => 'Test School',
				'grade' => 95,
			]);

		$response->assertStatus(201);
		$this->assertDatabaseHas('students', [
			'first_name' => 'Test',
			'last_name' => 'Student',
		]);
	}

	/**
	 * Test admin can update student.
	 */
	public function test_admin_can_update_student(): void {
		$student = Student::factory()->create();
		$token = $this->adminUser->createToken('test')->plainTextToken;

		$response = $this->withHeader('Authorization', "Bearer $token")
			->putJson("/api/admin/students/{$student->id}", [
				'first_name' => 'Updated',
				'last_name' => 'Name',
				'grade' => 88,
			]);

		$response->assertStatus(200);
		$this->assertDatabaseHas('students', [
			'id' => $student->id,
			'first_name' => 'Updated',
			'last_name' => 'Name',
			'grade' => 88,
		]);
	}

	/**
	 * Test admin can delete student.
	 */
	public function test_admin_can_delete_student(): void {
		$student = Student::factory()->create();
		$token = $this->adminUser->createToken('test')->plainTextToken;

		$response = $this->withHeader('Authorization', "Bearer $token")
			->deleteJson("/api/admin/students/{$student->id}");

		$response->assertStatus(200);
		$this->assertDatabaseMissing('students', ['id' => $student->id]);
	}

	/**
	 * Test unauthenticated user cannot export CSV.
	 */
	public function test_unauthenticated_user_cannot_export_csv(): void {
		$response = $this->get('/api/admin/students/export/csv');

		// Should return 401 for unauthenticated
		$this->assertNotEquals(200, $response->status());
	}

	/**
	 * Test non-admin user cannot export CSV.
	 */
	public function test_non_admin_user_cannot_export_csv(): void {
		$user = User::factory()->create(['is_admin' => false]);
		$token = $user->createToken('test')->plainTextToken;

		$response = $this->withHeader('Authorization', "Bearer $token")
			->get('/api/admin/students/export/csv');

		$response->assertStatus(403);
	}

	/**
	 * Test admin can export all students to CSV.
	 */
	public function test_admin_can_export_all_students_to_csv(): void {
		Student::factory()->count(5)->create();
		$token = $this->adminUser->createToken('test')->plainTextToken;

		$response = $this->withHeader('Authorization', "Bearer $token")
			->get('/api/admin/students/export/csv');

		$response->assertStatus(200);
	}

	/**
	 * Test admin can export students filtered by class to CSV.
	 */
	public function test_admin_can_export_students_filtered_by_class_to_csv(): void {
		Student::factory()->create(['class' => 10]);
		Student::factory()->create(['class' => 11]);
		Student::factory()->create(['class' => 10]);

		$token = $this->adminUser->createToken('test')->plainTextToken;

		$response = $this->withHeader('Authorization', "Bearer $token")
			->get('/api/admin/students/export/csv?class=10');

		$response->assertStatus(200);
	}

	/**
	 * Test admin can export students filtered by governorate to CSV.
	 */
	public function test_admin_can_export_students_filtered_by_governorate_to_csv(): void {
		Student::factory()->create(['governorate' => 'Riyadh']);
		Student::factory()->create(['governorate' => 'Mecca']);
		Student::factory()->create(['governorate' => 'Riyadh']);

		$token = $this->adminUser->createToken('test')->plainTextToken;

		$response = $this->withHeader('Authorization', "Bearer $token")
			->get('/api/admin/students/export/csv?governorate=Riyadh');

		$response->assertStatus(200);
	}
}
