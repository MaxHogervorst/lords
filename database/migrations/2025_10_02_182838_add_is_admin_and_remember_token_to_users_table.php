<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_admin')->default(false)->after('email');
            $table->string('remember_token', 100)->nullable()->after('password');
        });

        // Migrate existing admin users from Sentinel roles
        if (Schema::hasTable('role_users') && Schema::hasTable('roles')) {
            $adminRoleId = \DB::table('roles')->where('slug', 'admin')->value('id');
            if ($adminRoleId) {
                $adminUserIds = \DB::table('role_users')
                    ->where('role_id', $adminRoleId)
                    ->pluck('user_id');

                \DB::table('users')
                    ->whereIn('id', $adminUserIds)
                    ->update(['is_admin' => true]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['is_admin', 'remember_token']);
        });
    }
};
