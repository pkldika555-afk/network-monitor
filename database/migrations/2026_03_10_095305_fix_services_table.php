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
    Schema::table('services', function (Blueprint $table) {
        $table->enum('auth_type', ['none','bearer','basic'])->default('none');
        $table->text('auth_value')->nullable()->after('auth_type');
    });
}

public function down(): void
{
    Schema::table('services', function (Blueprint $table) {
        $table->dropColumn(['auth_type', 'auth_value']);
    });
}
};
