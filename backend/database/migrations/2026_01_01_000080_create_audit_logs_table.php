<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $t->string('action', 80);
            $t->string('subject', 80)->nullable();
            $t->unsignedBigInteger('subject_id')->nullable();
            $t->json('payload')->nullable();
            $t->string('ip_address', 45)->nullable();
            $t->timestamp('created_at')->useCurrent();

            $t->index(['subject', 'subject_id']);
        });
    }

    public function down(): void { Schema::dropIfExists('audit_logs'); }
};
