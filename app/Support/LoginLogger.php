<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LoginLogger
{
    public static function log(string $aksi, ?User $user = null, ?Request $request = null, array $metadata = []): void
    {
        try {
            if (!\Illuminate\Support\Facades\Schema::hasTable('tb_login_logs')) {
                return;
            }

            if ($aksi === 'logout') {
                $updated = DB::table('tb_login_logs')
                    ->where('id_user', $user?->id)
                    ->whereNull('logout_at')
                    ->where('is_active', 1)
                    ->orderByDesc('id_login_log')
                    ->limit(1)
                    ->update([
                        'logout_at' => now(),
                        'last_activity_at' => now(),
                        'is_active' => 0,
                    ]);

                if ($updated > 0) {
                    return;
                }
            }

            DB::table('tb_login_logs')->insert([
                'id_user' => $user?->id,
                'session_id' => $request?->session()->getId(),
                'ip_address' => $request?->ip(),
                'user_agent' => $request?->userAgent(),
                'login_at' => now(),
                'last_activity_at' => now(),
                'logout_at' => null,
                'is_active' => 1,
            ]);
        } catch (\Throwable $exception) {
            // Logging login tidak boleh menghentikan alur utama aplikasi.
        }
    }
}
