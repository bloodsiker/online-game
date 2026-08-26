<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('party_invites', function (Blueprint $table): void {
            $table->uuid('uuid')->nullable()->unique()->after('invited_user_id');
        });

        DB::table('party_invites')->orderBy('id')->each(function (object $invite): void {
            DB::table('party_invites')->where('id', $invite->id)->update(['uuid' => (string) Str::uuid()]);
        });
    }

    public function down(): void
    {
        Schema::table('party_invites', function (Blueprint $table): void {
            $table->dropUnique(['uuid']);
            $table->dropColumn('uuid');
        });
    }
};
