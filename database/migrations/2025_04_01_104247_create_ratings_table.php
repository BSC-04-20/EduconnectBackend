<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ratings', function (Blueprint $table) {
            $table->uuid("id")->primary();
            $table->uuid("student_id");
            $table->uuid("lecture_id");
            $table->integer("rating");
            $table->timestamps();

            // Foreign key constraints
            $table->foreign("student_id")->references("id")->on("students")->onDelete("cascade");
            $table->foreign("lecture_id")->references("id")->on("lectures")->onDelete("cascade");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ratings');
    }
};
