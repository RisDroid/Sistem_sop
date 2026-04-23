<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ActivityLogger
{
    public static function log(
        string $modul,
        string $aksi,
        string $deskripsi,
        ?string $subjekTipe = null,
        $subjekId = null,
        array $metadata = [],
        ?Request $request = null
    ): void {
        try {
            if (!\Illuminate\Support\Facades\Schema::hasTable('tb_activity_logs')) {
                return;
            }

            $user = Auth::user();
            $route = $request?->route();

            DB::table('tb_activity_logs')->insert([
                'id_user' => $user?->id,
                'activity_time' => now(),
                'activity' => strtolower(trim($modul . ' - ' . $aksi, ' -')),
                'detail' => $deskripsi,
                'ip_address' => $request?->ip(),
                'device' => self::resolveDevice($request?->userAgent()),
                'user_agent' => $request?->userAgent(),
                'route_name' => $route?->getName(),
                'http_method' => $request?->method(),
            ]);
        } catch (\Throwable $exception) {
            // Logging aktivitas tidak boleh menghentikan alur utama aplikasi.
        }
    }

    private static function resolveDevice(?string $userAgent): ?string
    {
        if (!$userAgent) {
            return null;
        }

        $normalized = strtolower($userAgent);

        if (str_contains($normalized, 'mobile')) {
            return 'mobile';
        }

        if (str_contains($normalized, 'tablet')) {
            return 'tablet';
        }

        return 'desktop';
    }
}
