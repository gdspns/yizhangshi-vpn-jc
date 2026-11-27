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
            abort_if(empty($data['subscription_url']), 422, '缺少订阅链接');
            DB::table('software_subscription_logs')->insertOrIgnore([
                'id' => (string) Str::uuid(),
                'subscription_url' => $data['subscription_url'],
                'subscription_label' => $data['label'] ?? '',
                'user_identifier' => $data['identifier'] ?? '',
                'ip_address' => $ip,
                'first_seen' => now(),
            ]);
        }
        return $this->success(true);
    }
}
