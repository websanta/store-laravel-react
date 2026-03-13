<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->text('rejection_reason')->nullable()->after('cover_image');
            $table->timestamp('verified_at')->nullable()->after('rejection_reason');
        });
    }

    public function down(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->dropColumn(['rejection_reason', 'verified_at']);
        });
    }
};
