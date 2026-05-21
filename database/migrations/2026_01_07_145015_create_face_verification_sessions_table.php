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
        Schema::create('face_verification_sessions', function (Blueprint $table) {
            $table->id();
            $table->uuid('session_id')->unique();
            $table->string('status', 50)->default('created'); // مهم: طول كافي لتجنب truncation
            $table->decimal('confidence', 6, 4)->nullable();   // liveness confidence
            $table->decimal('match_score', 6, 4)->nullable();  // cosine match score
            $table->unsignedBigInteger('matched_face_id')->nullable();
            $table->string('fail_reason', 80)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('face_verification_sessions');
    }
};
