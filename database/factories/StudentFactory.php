<?php

namespace Database\Factories;

use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Student>
 */
class StudentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $genders = ['male', 'female'];
        $governorates = ['Riyadh', 'Mecca', 'Medina', 'Eastern Province', 'Asir', 'Qassim', 'Hail', 'Northern Borders', 'Jazan', 'Najran', 'Baha', 'Al-Jouf'];

        return [
            'first_name' => $this->faker->firstName(),
            'second_name' => $this->faker->firstName(),
            'third_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'gender' => $this->faker->randomElement($genders),
            'governorate' => $this->faker->randomElement($governorates),
            'class' => $this->faker->numberBetween(1, 12),
            'school_name' => $this->faker->word().' School',
            'grade' => $this->faker->numberBetween(0, 100),
            'cert_image' => $this->faker->uuid().'.jpg',
            'phone1' => $this->faker->phoneNumber(),
            'phone2' => $this->faker->phoneNumber(),
            'address' => $this->faker->address(),
            'qiyes_grade' => $this->faker->numberBetween(0, 100),
            'SAAT_grade' => $this->faker->numberBetween(0, 100),
            'SAAT_cert_image' => null,
            'qiyes_cert_image' => null,
        ];
    }
}
