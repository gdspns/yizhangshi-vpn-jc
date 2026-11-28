<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('v2_user', function (Blueprint $table) {
            // 「当前订阅」指针：记在父账号上，指向自己或某条子订阅的 id。
            // 后台列表的订阅 / 已用流量 / 总流量 / 到期时间都按它显示，
            // 「切换订阅」改的就是这个字段；为空时回落到第一条有效订阅。
            $table->unsignedInteger('current_subscription_id')->nullable()->after('subscription_name');
        });
    }

    public function down(): void
    {
        Schema::table('v2_user', fn (Blueprint $table) => $table->dropColumn('current_subscription_id'));
    }
};
