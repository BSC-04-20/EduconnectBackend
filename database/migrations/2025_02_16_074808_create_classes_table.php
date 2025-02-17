<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('classes', function (Blueprint $table) {
            $table->uuid('id')->primary(); // Use UUID as the primary key
            $table->string('class_code')->unique(); // Unique class identifier
            $table->string('name'); // Add a name column
            $table->uuid('lecture_id'); // Foreign key for lecture
            $table->integer('number_of_students')->default(0); // Default number of students to 0
            $table->timestamps();

            // Foreign key constraint 
            $table->foreign('lecture_id')->references('id')->on('lectures')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('classes');
    }
};

