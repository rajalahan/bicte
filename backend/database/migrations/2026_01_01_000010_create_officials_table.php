<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('officials', function (Blueprint $t) {
            $t->id();
            $t->enum('role', ['mayor', 'deputy', 'cao', 'other'])->index();
            $t->string('name', 200);
            $t->string('designation', 200)->nullable();
            $t->string('photo', 500)->nullable();
            $t->string('phone', 40)->nullable();
            $t->string('email', 200)->nullable();
            $t->text('message')->nullable();
            $t->integer('sort_order')->default(0);
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('officials'); }
};
