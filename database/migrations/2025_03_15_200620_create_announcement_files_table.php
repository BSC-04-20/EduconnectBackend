<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('announcement_files', function (Blueprint $table) {
            $table->uuid('id')->primary(); // UUID primary key
            $table->uuid('announcement_id'); // Foreign key to announcements
            $table->string('file_path'); // Path to the uploaded file
            $table->timestamps();

            // Foreign key constraint
            $table->foreign('announcement_id')->references('id')->on('announcements')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('announcement_files');
    }
};
