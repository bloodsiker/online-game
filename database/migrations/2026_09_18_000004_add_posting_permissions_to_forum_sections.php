<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('forum_sections', function (Blueprint $table): void {
            $table->boolean('allow_topics')->default(true)->after('is_active');
            $table->boolean('allow_comments')->default(true)->after('allow_topics');
        });
    }

    public function down(): void
    {
        Schema::table('forum_sections', function (Blueprint $table): void {
            $table->dropColumn(['allow_topics', 'allow_comments']);
        });
    }
};
