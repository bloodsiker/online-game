<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('bank_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('related_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action'); // deposit / withdraw
            $table->unsignedBigInteger('amount');
            $table->unsignedBigInteger('balance_after');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_logs');
    }
};
