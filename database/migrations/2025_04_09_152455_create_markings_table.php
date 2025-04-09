<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('markings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('submission_id');  // Reference to the submission table
            $table->decimal('marks', 5, 2); // Marks can be decimal (e.g., 85.50)
            $table->text('feedback')->nullable(); // Optional feedback for the student
            $table->timestamps();

            $table->foreign('submission_id')->references('id')->on('submissions')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('markings');
    }
};
