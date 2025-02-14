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
        Schema::create('events', function (Blueprint $table) {
            $table->uuid('id')->primary(); // UUID for event ID
            $table->uuid('user_id'); // User ID referencing the user (or lecture ID)
            $table->string('name'); // Event name
            $table->string('location'); // Event location
            $table->time('time'); // Event time
            $table->date('date'); // Event date
            $table->integer('number_of_attendees'); // Number of attendees
            $table->timestamps();

            // Foreign key constraint 
            $table->foreign('user_id')->references('id')->on('lectures')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
