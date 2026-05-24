<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('faqs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $t->string('intent', 80)->index();
            $t->json('keywords')->nullable();
            $t->string('question', 300);
            $t->longText('answer_ne');
            $t->longText('answer_en')->nullable();
            $t->integer('sort_order')->default(0);
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('faqs'); }
};
