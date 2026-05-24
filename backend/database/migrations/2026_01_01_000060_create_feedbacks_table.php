<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feedbacks', function (Blueprint $t) {
            $t->id();
            $t->string('ticket', 40)->unique();
            $t->string('name', 120)->nullable();
            $t->string('phone', 20)->nullable();
            $t->string('email', 200)->nullable();
            $t->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $t->enum('type', ['complaint', 'suggestion', 'appreciation', 'info']);
            $t->string('subject', 200);
            $t->text('message');
            $t->enum('status', ['new', 'in_progress', 'resolved', 'closed'])->default('new');
            $t->string('ip_address', 45)->nullable();
            $t->timestamp('submitted_at')->nullable();
            $t->timestamp('resolved_at')->nullable();
            $t->timestamps();

            $t->index(['status', 'submitted_at']);
        });
    }

    public function down(): void { Schema::dropIfExists('feedbacks'); }
};
