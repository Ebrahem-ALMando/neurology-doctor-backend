<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consultation_messages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('consultation_id');
            $table->unsignedBigInteger('sender_id');
            $table->enum('sender_type', ['doctor', 'patient']);
            $table->string('subject')->nullable();
            $table->longText('message')->nullable();
            $table->boolean('read_by_patient')->default(false);
            $table->boolean('read_by_doctor')->default(false);
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();
            
            $table->foreign('consultation_id')->references('id')->on('consultations')->onDelete('cascade');
            $table->foreign('sender_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consultation_messages');
    }
};
