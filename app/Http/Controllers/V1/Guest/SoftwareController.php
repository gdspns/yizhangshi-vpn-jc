<?php

namespace App\Http\Controllers\V1\Guest;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SoftwareController extends Controller
{
    public function config()
    {
        $config = DB::table('software_settings')->where('id', 1)->first();
        unset($config->id, $config->created_at, $config->updated_at);
        return $this->success($config);
    }

    public function telemetry(Request $request)
    {
        $data = $request->validate([
            'action' => 'required|in:heartbeat,offline,record-subscription',
            'identifier' => 'nullable|string|max:200',
            'remark' => 'nullable|string|max:200',
            'app_version' => 'nullable|string|max:50',
            'current_node' => 'nullable|string|max:300',
            'subscription_url' => 'nullable|url:http,https|max:4000',
            'label' => 'nullable|string|max:200',
        ]);
        $ip = substr((string) $request->ip(), 0, 100);

        if ($data['action'] === 'heartbeat') {
            abort_if(empty($data['identifier']), 422, '缺少用户标识');
            $values = [
                'user_remark' => $data['remark'] ?? '',
                'app_version' => $data['app_version'] ?? '',
                'ip_address' => $ip,
                'current_node' => $data['current_node'] ?? '',
                'is_online' => true,
                'last_seen' => now(),
            ];
            $existing = DB::table('software_online_users')->where('user_identifier', $data['identifier'])->exists();
            if ($existing) {
                DB::table('software_online_users')->where('user_identifier', $data['identifier'])->update($values);
            } else {
                DB::table('software_online_users')->insert(array_merge($values, [
                    'id' => (string) Str::uuid(),
                    'user_identifier' => $data['identifier'],
                    'first_seen' => now(),
                ]));
            }
        } elseif ($data['action'] === 'offline') {
            if (!empty($data['identifier'])) {
                DB::table('software_online_users')->where('user_identifier', $data['identifier'])
                    ->update(['is_online' => false, 'last_seen' => now()]);
            }
        } else {
            // 「第三方订阅日志」模块已下线：客户端仍可能上报 record-subscription，
            // 这里只回成功以保持旧版客户端兼容，不再写入 software_subscription_logs。
            return $this->success(true);
        }
        return $this->success(true);
    }
}
