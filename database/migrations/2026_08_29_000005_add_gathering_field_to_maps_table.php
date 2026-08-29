<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Общий флаг «на этой карте показывать слой добычи ресурсов» +
     * картинка-подложка поля сбора (см. MAP_RESOURCE.md — верхний вид,
     * рельеф/кусты/тропинки), отдельная от игровых фонов локаций.
     * Конкретные ресурсы/узлы уже настраиваются через map_gathering_resources
     * (2026_08_29_000001) — этот флаг лишь включает отображение слоя.
     */
    public function up(): void
    {
        Schema::table('maps', function (Blueprint $table): void {
            $table->boolean('has_gathering_field')->default(false)->after('resp_location_id');
            $table->string('gathering_field_image')->nullable()->after('has_gathering_field');
        });
    }

    public function down(): void
    {
        Schema::table('maps', function (Blueprint $table): void {
            $table->dropColumn(['has_gathering_field', 'gathering_field_image']);
        });
    }
};
