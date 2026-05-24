<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('departments', function (Blueprint $t) {
            $t->id();
            $t->string('slug', 80)->unique();
            $t->string('name', 200);
            $t->string('name_en', 200)->nullable();
            $t->text('summary')->nullable();
            $t->string('icon', 80)->nullable();

            $t->string('room', 80)->nullable();
            $t->string('floor', 80)->nullable();

            $t->string('contact_person', 200)->nullable();
            $t->string('contact_designation', 200)->nullable();
            $t->string('phone', 40)->nullable();
            $t->string('email', 200)->nullable();

            $t->string('timings', 200)->nullable();
            $t->text('charter')->nullable();
            $t->string('public_url', 500)->nullable();

            $t->json('services')->nullable();
            $t->json('required_documents')->nullable();
            $t->json('process')->nullable();
            $t->json('fees')->nullable();
            $t->json('forms')->nullable();

            $t->integer('sort_order')->default(0);
            $t->boolean('is_active')->default(true);
            $t->timestamps();

            $t->index(['is_active', 'sort_order']);
        });
    }

    public function down(): void { Schema::dropIfExists('departments'); }
};
