<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('library_categories', function (Blueprint $table): void {
            $table->string('content_type', 30)->default('articles')->after('description');
        });

        DB::table('library_categories')
            ->where('slug', 'bestiarii')
            ->update(['content_type' => 'monsters']);
    }

    public function down(): void
    {
        Schema::table('library_categories', function (Blueprint $table): void {
            $table->dropColumn('content_type');
        });
    }
};
