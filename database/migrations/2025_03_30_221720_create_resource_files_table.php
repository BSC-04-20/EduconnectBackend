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
        Schema::create('resource_files', function (Blueprint $table) {
            $table->uuid('id')->primary(); // UUID primary key
            $table->uuid('resource_id'); // Foreign key to the resources table
            $table->string('file_path'); // Path to the uploaded file
            $table->timestamps(); // Timestamps for created_at and updated_at
            // Foreign key constraint
            $table->foreign('resource_id')->references('id')->on('resources')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resource_files');
    }
};
