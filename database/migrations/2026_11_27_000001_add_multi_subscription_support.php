<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('v2_user', function (Blueprint $table) {
            $table->unsignedInteger('owner_user_id')->nullable()->after('id')->index();
            $table->string('subscription_name', 100)->nullable()->after('owner_user_id');
        });
        Schema::table('v2_order', function (Blueprint $table) {
            $table->unsignedInteger('subscription_user_id')->nullable()->after('user_id')->index();
        });
    }

    public function down(): void
    {
        Schema::table('v2_order', fn (Blueprint $table) => $table->dropColumn('subscription_user_id'));
        Schema::table('v2_user', fn (Blueprint $table) => $table->dropColumn(['owner_user_id', 'subscription_name']));
    }
};
