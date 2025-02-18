<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClassstudentsTable extends Migration
{
    public function up(): void
    {
        Schema::create('classstudents', function (Blueprint $table) {
            $table->uuid('classe_id');
            $table->uuid('student_id');
            $table->timestamps();

            // Define foreign key constraints
            $table->foreign('classe_id')->references('id')->on('classes')->onDelete('cascade');
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('classstudents');
    }
}

