<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notices', function (Blueprint $t) {
            $t->id();
            $t->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $t->string('title');
            $t->string('summary', 500)->nullable();
            $t->longText('body')->nullable();
            $t->string('category', 80)->nullable();
            $t->string('attachment_url', 500)->nullable();
            $t->boolean('is_active')->default(true);
            $t->boolean('is_urgent')->default(false);
            $t->timestamp('published_at')->nullable();
            $t->timestamp('expires_at')->nullable();
            $t->timestamps();

            $t->index(['is_active', 'published_at']);
        });
    }

    public function down(): void { Schema::dropIfExists('notices'); }
};
