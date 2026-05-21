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
        Schema::create('kyc_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->unsignedTinyInteger('current_step')->default(1);

            $table->enum('status', [
                'draft',
                'submitted',
                'verified',
                'face_required',
                'face_enrolled',
                'active',
                'rejected',
            ])->default('draft');

            $table->timestamp('submitted_at')->nullable();

            /* ======================
             * Step 1 – Personal
             * ====================== */
            $table->string('full_name_ar')->nullable();
            $table->string('full_name_en')->nullable();

            $table->date('date_of_birth')->nullable();

            $table->string('nationality_ar')->nullable();
            $table->string('nationality_en')->nullable();

            $table->string('occupation_ar')->nullable();
            $table->string('occupation_en')->nullable();

            $table->string('employer_or_business_ar')->nullable();
            $table->string('employer_or_business_en')->nullable();

            $table->string('contact_number')->nullable();
            $table->string('email_address')->nullable();

            $table->text('residential_address_ar')->nullable();
            $table->text('residential_address_en')->nullable();

            /* ======================
             * Step 2 – ID
             * ====================== */
            $table->string('id_number')->nullable();
            $table->string('issuing_authority_ar')->nullable();
            $table->string('issuing_authority_en')->nullable();

            $table->date('id_issue_date')->nullable();
            $table->date('id_expiry_date')->nullable();

            /* ======================
             * Step 3 – Income
             * ====================== */
            $table->string('source_of_income_ar')->nullable();
            $table->string('source_of_income_en')->nullable();

            $table->string('income_range')->nullable();

            $table->string('purpose_of_relationship_ar')->nullable();
            $table->string('purpose_of_relationship_en')->nullable();

            /* ======================
             * Step 4 – PEP
             * ====================== */
            $table->boolean('is_pep')->nullable();
            $table->text('pep_details_ar')->nullable();
            $table->text('pep_details_en')->nullable();

            /* ======================
             * Step 5 – UBO
             * ====================== */
            $table->boolean('acting_on_behalf')->nullable();
            $table->text('ubo_details_ar')->nullable();
            $table->text('ubo_details_en')->nullable();

            /* ======================
             * Step 6 – Declaration
             * ====================== */
            $table->boolean('declaration_accepted')->default(false);
            $table->timestamp('declaration_accepted_at')->nullable();

            /* ======================
             * Office
             * ====================== */
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->text('office_remarks')->nullable();
            $table->text('rejection_reason')->nullable();

            $table->unique('user_id');
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kyc_profiles');
    }
};
