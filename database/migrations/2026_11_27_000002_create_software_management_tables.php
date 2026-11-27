<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('software_settings', function (Blueprint $table) {
            $table->unsignedTinyInteger('id')->primary();
            $table->boolean('notice_enabled')->default(false);
            $table->string('notice', 300)->default('');
            $table->string('api_domain', 253)->default('');
            $table->boolean('popup_enabled')->default(false);
            $table->text('popup_content')->nullable();
            $table->string('popup_version', 50)->default('');
            $table->unsignedTinyInteger('popup_display_count')->default(1);
            $table->boolean('update_enabled')->default(false);
            $table->string('latest_version', 30)->default('');
            $table->text('windows_download_url')->nullable();
            $table->text('macos_download_url')->nullable();
            $table->text('update_notes')->nullable();
            $table->boolean('force_update')->default(false);
            $table->boolean('emergency_nodes_enabled')->default(false);
            $table->text('emergency_subscription_url')->nullable();
            $table->timestamps();
        });

        DB::table('software_settings')->insert(['id' => 1, 'created_at' => now(), 'updated_at' => now()]);

        Schema::create('software_online_users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('user_identifier', 200)->unique();
            $table->string('user_remark', 200)->default('');
            $table->string('app_version', 50)->default('');
            $table->string('ip_address', 100)->default('');
            $table->string('current_node', 300)->default('');
            $table->boolean('is_online')->default(true);
            $table->timestamp('first_seen')->useCurrent();
            $table->timestamp('last_seen')->useCurrent()->index();
        });

        Schema::create('software_subscription_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->text('subscription_url')->unique();
            $table->string('subscription_label', 200)->default('');
            $table->string('user_identifier', 200)->default('');
            $table->string('ip_address', 100)->default('');
            $table->timestamp('first_seen')->useCurrent()->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('software_subscription_logs');
        Schema::dropIfExists('software_online_users');
        Schema::dropIfExists('software_settings');
    }
};
