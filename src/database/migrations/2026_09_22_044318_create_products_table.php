<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->decimal('price', 12, 2);
            $table->text('description')->nullable();
            $table->string('category');
            $table->json('images');
            $table->string('created_by');
            $table->foreignId('created_by_id')->constrained('users');
            $table->string('updated_by');
            $table->foreignId('updated_by_id')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
