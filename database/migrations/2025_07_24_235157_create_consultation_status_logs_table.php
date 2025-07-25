<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consultation_status_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('consultation_id');
            $table->enum('from_status', ['open','waiting_response','answered','closed','cancelled']);
            $table->enum('to_status', ['open','waiting_response','answered','closed','cancelled']);
            $table->unsignedBigInteger('changed_by_id')->nullable();
            $table->enum('changed_by_type', ['doctor', 'patient', 'admin']);
            $table->text('note')->nullable();
            $table->timestamps();

            $table->foreign('consultation_id')->references('id')->on('consultations')->onDelete('cascade');
            $table->foreign('changed_by_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consultation_status_logs');
    }
};
