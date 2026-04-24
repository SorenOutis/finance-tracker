<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('type')->default('cash'); // gcash, maya, paypal, bank, cash, credit_card, crypto, other
            $table->string('color', 32)->nullable();
            $table->decimal('initial_balance', 14, 2)->default(0);
            $table->decimal('credit_limit', 14, 2)->nullable();
            $table->string('currency', 8)->default('PHP');
            $table->string('last4', 8)->nullable();
            $table->string('holder_name')->nullable();
            $table->boolean('is_archived')->default(false);
            $table->timestamps();

            $table->index(['user_id', 'is_archived']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
