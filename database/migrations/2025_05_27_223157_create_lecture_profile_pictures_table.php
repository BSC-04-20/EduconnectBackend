<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lecture_profile_pictures', function (Blueprint $table) {
            $table->id('id')->primary();
            $table->uuid('identifier')->unique();
            $table->uuid('lecture_id');
            $table->string('image_path');
            $table->timestamps();

            $table->foreign('lecture_id')->references('id')->on('lectures')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lecture_profile_pictures');
    }
};
