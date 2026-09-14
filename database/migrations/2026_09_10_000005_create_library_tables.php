<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('library_categories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('library_categories')->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['parent_id', 'is_active', 'sort_order'], 'library_categories_navigation_index');
        });

        Schema::create('library_articles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('category_id')->constrained('library_categories')->restrictOnDelete();
            $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('excerpt')->nullable();
            $table->longText('content');
            $table->string('cover_image')->nullable();
            $table->string('status', 20)->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->unsignedBigInteger('views_count')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['category_id', 'status', 'sort_order'], 'library_articles_category_index');
            $table->index(['status', 'published_at'], 'library_articles_publication_index');
        });

        Schema::create('library_article_links', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('article_id')->constrained('library_articles')->cascadeOnDelete();
            $table->string('entity_type', 40);
            $table->unsignedBigInteger('entity_id');
            $table->string('custom_label')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['article_id', 'entity_type', 'entity_id'], 'library_article_links_unique');
            $table->index(['entity_type', 'entity_id'], 'library_article_links_entity_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('library_article_links');
        Schema::dropIfExists('library_articles');
        Schema::dropIfExists('library_categories');
    }
};
