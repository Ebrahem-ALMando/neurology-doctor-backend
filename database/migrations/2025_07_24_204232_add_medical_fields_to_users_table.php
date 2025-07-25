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
        Schema::table('users', function (Blueprint $table) {
            $table->string('blood_type')->nullable()->after('birthdate');
            $table->text('allergy')->nullable()->after('blood_type');
            $table->text('chronic_diseases')->nullable()->after('allergy');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['blood_type', 'allergy', 'chronic_diseases']);
        });
    }
};
