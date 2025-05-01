<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('offers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('card_id')->nullable()
                ->constrained('cards')
                ->cascadeOnDelete();
            $table->string('identifier')->unique();
            $table->integer('price')->nullable();
            $table->boolean('in_sale');
            $table->date('put_in_sale_at')->nullable();
            $table->date('sold_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('offers');
    }
};
