<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	/**
	 * Run the migrations.
	 */
	public function up(): void {
		Schema::table('students', function (Blueprint $table) {
			// Add new name fields
			$table->string('first_name')->after('id')->nullable();
			$table->string('second_name')->nullable();
			$table->string('third_name')->nullable();
			$table->string('last_name')->nullable();

			// Add gender and governorate
			$table->enum('gender', ['male', 'female'])->nullable();
			$table->string('governorate')->nullable();

			// Rename certificate_path to image
			$table->renameColumn('certificate_path', 'image');

			// Drop old full_name column
			$table->dropColumn('full_name');
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void {
		Schema::table('students', function (Blueprint $table) {
			$table->string('full_name')->nullable();
			$table->renameColumn('image', 'certificate_path');
			$table->dropColumn(['first_name', 'second_name', 'third_name', 'last_name', 'gender', 'governorate']);
		});
	}
};
