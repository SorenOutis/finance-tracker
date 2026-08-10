<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('terms_accepted_at')->nullable()->after('email_verified_at');
            $table->string('terms_version', 30)->nullable()->after('terms_accepted_at');
            $table->boolean('must_reset_password')->default(false)->after('password');
        });

        DB::table('users')->update(['must_reset_password' => true]);
        DB::table('users')
            ->whereIn('id', DB::table('accounts')->select('user_id')->distinct())
            ->update(['is_onboarded' => true]);
        DB::table('sessions')->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['terms_accepted_at', 'terms_version', 'must_reset_password']);
        });
    }
};
