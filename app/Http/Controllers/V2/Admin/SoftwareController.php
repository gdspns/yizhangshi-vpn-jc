<?php

namespace App\Http\Controllers\V2\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class SoftwareController extends Controller
{
    public function config()
    {
        return $this->success(DB::table('software_settings')->where('id', 1)->first());
    }

    public function save(Request $request)
    {
        $data = $request->validate([
            'notice_enabled' => 'required|boolean',
            'notice' => 'nullable|string|max:300',
            'api_domain' => ['nullable', 'string', 'max:253', 'regex:/^[a-z0-9.-]+(?::\d+)?$/i'],
            'popup_enabled' => 'required|boolean',
            'popup_content' => 'nullable|string|max:1000',
            'popup_version' => 'nullable|string|max:50',
            'popup_display_count' => 'required|integer|min:1|max:20',
            'update_enabled' => 'required|boolean',
            'latest_version' => 'nullable|string|max:30',
            'windows_download_url' => 'nullable|url:https|max:2000',
            'macos_download_url' => 'nullable|url:https|max:2000',
            'update_notes' => 'nullable|string|max:3000',
            'force_update' => 'required|boolean',
        ]);

        $data['api_domain'] = preg_replace('#^https?://#i', '', trim($data['api_domain'] ?? ''));
        $data['latest_version'] = preg_replace('/^v/i', '', trim($data['latest_version'] ?? ''));
        if ($data['update_enabled'] && (!$data['latest_version'] || empty($data['windows_download_url']))) {
            return response()->json(['message' => '启用更新前请填写版本号和 Windows HTTPS 下载地址'], 422);
        }
        $data['updated_at'] = now();
        DB::table('software_settings')->updateOrInsert(['id' => 1], $data);
        return $this->config();
    }

    public function telemetry()
    {
        $users = DB::table('software_online_users')->orderByDesc('last_seen')->limit(500)->get();
        $cutoff = now()->subSeconds(90);
        $users->transform(function ($user) use ($cutoff) {
            $user->online = (bool) $user->is_online && Carbon::parse($user->last_seen)->gte($cutoff);
            return $user;
        });
        return $this->success(['users' => $users]);
    }
}
