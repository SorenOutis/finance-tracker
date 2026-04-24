<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('incomes', function (Blueprint $table) {
            $table->foreignId('account_id')->nullable()->after('user_id')->constrained('accounts')->nullOnDelete();
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->foreignId('account_id')->nullable()->after('user_id')->constrained('accounts')->nullOnDelete();
        });

        Schema::table('recurring_transactions', function (Blueprint $table) {
            $table->foreignId('account_id')->nullable()->after('user_id')->constrained('accounts')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('incomes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('account_id');
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->dropConstrainedForeignId('account_id');
        });

        Schema::table('recurring_transactions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('account_id');
        });
    }
};
