<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tokens', function (Blueprint $t) {
            $t->id();
            $t->foreignId('department_id')->constrained()->cascadeOnDelete();
            $t->string('code', 20);
            $t->integer('sequence');
            $t->string('name', 120)->nullable();
            $t->string('phone', 20)->nullable();
            $t->enum('status', ['waiting', 'serving', 'done', 'cancelled'])->default('waiting');
            $t->timestamp('issued_at');
            $t->timestamp('served_at')->nullable();
            $t->timestamp('closed_at')->nullable();
            $t->timestamps();

            $t->index(['department_id', 'issued_at']);
        });
    }

    public function down(): void { Schema::dropIfExists('tokens'); }
};
