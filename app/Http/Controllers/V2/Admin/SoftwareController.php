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
        ], [
            'api_domain.regex' => '接口域名只填域名，例如 www.example.com，不要带 https:// 或路径',
            'api_domain.max' => '接口域名太长了',
            'windows_download_url.url' => 'Windows 下载地址必须是完整的 https 链接',
            'macos_download_url.url' => 'macOS 下载地址必须是完整的 https 链接',
            'notice.max' => '公告内容最多 300 字',
            'popup_content.max' => '弹窗内容最多 1000 字',
        ]);

        // 全局中间件会把空字符串转成 null，而 notice / api_domain /
        // popup_version / latest_version 这几列是 NOT NULL 且默认 ''，
        // 直接写 null 会报 23502（之前「保存全部软件配置」失败就是这个）。
        foreach (['notice', 'api_domain', 'popup_version', 'latest_version'] as $column) {
            $data[$column] = trim((string) ($data[$column] ?? ''));
        }

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

    public function deleteUser(Request $request)
    {
        $data = $request->validate([
            'id' => 'required|uuid',
        ], [
            'id.required' => '缺少软件用户记录 ID',
            'id.uuid' => '软件用户记录 ID 无效',
        ]);

        $deleted = DB::table('software_online_users')->where('id', $data['id'])->delete();
        if (!$deleted) {
            return response()->json(['message' => '软件用户记录不存在或已被删除'], 404);
        }

        return $this->success(true);
    }
}
