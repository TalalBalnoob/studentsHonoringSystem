<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Services\CertificateStorageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Mockery\MockInterface;
use Tests\TestCase;

class StudentSubmissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mock(CertificateStorageService::class, function (MockInterface $mock) {
            $mock->shouldReceive('uploadCertificate')
                ->andReturn('test-file-' . time() . '.jpg');
        });
    }

    /**
     * Test successful student form submission with image.
     */
    public function test_student_can_submit_form_with_image(): void
    {
        $file = UploadedFile::fake()->image('student.jpg', 100, 100);

        $response = $this->postJson('/api/students', [
            'first_name' => 'John',
            'second_name' => 'Michael',
            'third_name' => 'Samuel',
            'last_name' => 'Doe',
            'gender' => 'male',
            'governorate' => 1,
            'class' => 10,
            'school_name' => 'Test School',
            'grade' => 95,
            'cert_image' => $file,
        ]);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'message',
            'data' => ['id', 'first_name', 'last_name', 'class', 'school_name', 'grade', 'cert_image'],
        ]);

        $this->assertDatabaseHas('students', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'class' => 10,
            'school_name' => 'Test School',
            'grade' => 95,
        ]);
    }

    /**
     * Test form submission fails without required fields.
     */
    public function test_student_submission_fails_without_required_fields(): void
    {
        $response = $this->postJson('/api/students', [
            'first_name' => 'John',
            // Missing other required fields
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['second_name', 'third_name', 'last_name', 'gender', 'governorate', 'class', 'school_name', 'grade', 'cert_image']);
    }

    /**
     * Test form submission fails with invalid class.
     */
    public function test_student_submission_fails_with_invalid_class(): void
    {
        $file = UploadedFile::fake()->image('student.jpg');

        $response = $this->postJson('/api/students', [
            'first_name' => 'John',
            'second_name' => 'Michael',
            'third_name' => 'Samuel',
            'last_name' => 'Doe',
            'gender' => 'male',
            'governorate' => 1,
            'class' => 13, // Invalid, should be 1-12
            'school_name' => 'Test School',
            'grade' => 95,
            'cert_image' => $file,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['class']);
    }

    /**
     * Test form submission fails with invalid grade.
     */
    public function test_student_submission_fails_with_invalid_grade(): void
    {
        $file = UploadedFile::fake()->image('student.jpg');

        $response = $this->postJson('/api/students', [
            'first_name' => 'John',
            'second_name' => 'Michael',
            'third_name' => 'Samuel',
            'last_name' => 'Doe',
            'gender' => 'male',
            'governorate' => 1,
            'class' => 10,
            'school_name' => 'Test School',
            'grade' => 101, // Invalid, should be 0-100
            'cert_image' => $file,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['grade']);
    }

    /**
     * Test form submission fails without image file.
     */
    public function test_student_submission_fails_without_image(): void
    {
        $response = $this->postJson('/api/students', [
            'first_name' => 'John',
            'second_name' => 'Michael',
            'third_name' => 'Samuel',
            'last_name' => 'Doe',
            'gender' => 'male',
            'governorate' => 1,
            'class' => 10,
            'school_name' => 'Test School',
            'grade' => 95,
            'cert_image' => UploadedFile::fake()->create('document.pdf'),
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['cert_image']);
    }

    /**
     * Test form submission fails with oversized image.
     */
    public function test_student_submission_fails_with_oversized_image(): void
    {
        // Create a fake file larger than 50MB
        $file = UploadedFile::fake()->image('student.jpg')->size(51201);

        $response = $this->postJson('/api/students', [
            'first_name' => 'John',
            'second_name' => 'Michael',
            'third_name' => 'Samuel',
            'last_name' => 'Doe',
            'gender' => 'male',
            'governorate' => 1,
            'class' => 10,
            'school_name' => 'Test School',
            'grade' => 95,
            'cert_image' => $file,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['cert_image']);
    }
}
