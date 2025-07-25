<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consultation_attachments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('consultation_message_id')->nullable();
            $table->unsignedBigInteger('consultation_id');
            $table->text('file_name')->nullable();
            $table->text('original_name')->nullable();
            $table->string('file_path', 255)->nullable();
            $table->string('file_type', 50)->nullable();
            $table->timestamps();

            $table->foreign('consultation_message_id')->references('id')->on('consultation_messages')->onDelete('set null');
            $table->foreign('consultation_id')->references('id')->on('consultations')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consultation_attachments');
    }
};
