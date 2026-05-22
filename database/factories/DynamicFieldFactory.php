<?php

namespace Database\Factories;

use App\Models\DynamicField;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DynamicField>
 */
class DynamicFieldFactory extends Factory {
	/**
	 * Define the model's default state.
	 *
	 * @return array<string, mixed>
	 */
	public function definition(): array {
		return [
			'field_name' => $this->faker->unique()->word(),
			'field_type' => $this->faker->randomElement(['text', 'number', 'date', 'email', 'url']),
			'is_visible' => true,
			'order' => 0,
		];
	}
}
