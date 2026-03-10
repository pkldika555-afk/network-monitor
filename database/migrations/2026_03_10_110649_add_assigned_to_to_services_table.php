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
        $table->string('assigned_to')->nullable()->after('department');
        $table->timestamp('assigned_at')->nullable()->after('assigned_to');
    });
}

public function down(): void
{
    Schema::table('services', function (Blueprint $table) {
        $table->dropColumn(['assigned_to', 'assigned_at']);
    });
}
};
