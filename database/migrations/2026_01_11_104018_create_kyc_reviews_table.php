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
        Schema::create('kyc_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kyc_profile_id')->constrained('kyc_profiles')->cascadeOnDelete();
            $table->foreignId('reviewed_by')->constrained('users')->cascadeOnDelete();

            $table->enum('action', [
                'submitted',
                'verified',
                'rejected',
                'face_required',
                'remark',
            ])->index();

            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kyc_reviews');
    }
};
