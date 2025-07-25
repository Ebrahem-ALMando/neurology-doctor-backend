<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consultations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('patient_id');
            $table->unsignedBigInteger('doctor_id')->nullable();
            $table->enum('status', ['open','waiting_response','answered','closed','cancelled'])->default('open');
            $table->timestamp('closed_at')->nullable();
            $table->timestamp('last_message_at')->nullable();
            $table->unsignedBigInteger('last_sender_id')->nullable();
            $table->timestamps();

            $table->foreign('patient_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('doctor_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('last_sender_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consultations');
    }
};
