<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('backpacks', function (Blueprint $table) {
            $table->unsignedSmallInteger('sort_order')->default(0)->after('equipped');
        });
    }

    public function down(): void
    {
        Schema::table('backpacks', function (Blueprint $table) {
            $table->dropColumn('sort_order');
        });
    }
};