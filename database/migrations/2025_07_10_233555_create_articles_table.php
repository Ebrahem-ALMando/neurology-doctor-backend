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
        Schema::create('articles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('doctor_id')->nullable();
            $table->unsignedBigInteger('category_id');
            $table->string('title');
            $table->text('short_description')->nullable();
            $table->longText('content'); // HTML
            $table->unsignedBigInteger('views_count')->default(0);
            $table->boolean('is_published')->default(true);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->foreign('doctor_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('category_id')->references('id')->on('article_categories')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
