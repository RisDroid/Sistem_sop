<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        if (!Schema::hasTable('tb_activity_logs')) {
            return view('pages.admin.activity-log.index', [
                'logs' => $this->paginateCollection(collect(), $request),
                'moduls' => collect(),
                'aksis' => collect(),
            ]);
        }

        $rows = DB::table('tb_activity_logs as logs')
            ->leftJoin('tb_user as users', 'users.id_user', '=', 'logs.id_user')
            ->select([
                'logs.id_activity_log',
                'logs.activity_time',
                'logs.activity',
                'logs.detail',
                'logs.ip_address',
                'logs.device',
                'logs.route_name',
                'logs.http_method',
                'users.nama',
                'users.username',
                'users.role',
            ])
            ->orderByDesc('logs.activity_time')
            ->get()
            ->map(function ($row) {
                [$modul, $aksi] = $this->splitActivity($row->activity);

                $row->created_date = $row->activity_time ? Carbon::parse($row->activity_time) : null;
                $row->nama_user = $row->nama ?: $row->username ?: 'Guest';
                $row->role_user = $row->role ?: '-';
                $row->modul = $modul;
                $row->aksi = $aksi;
                $row->deskripsi = $row->detail ?: '-';
                $row->subjek_tipe = $row->route_name ?: '-';
                $row->subjek_id = $row->http_method ?: ($row->device ?: '-');

                return $row;
            });

        $moduls = $rows->pluck('modul')->filter()->unique()->sort()->values();
        $aksis = $rows->pluck('aksi')->filter()->unique()->sort()->values();

        if ($request->filled('modul')) {
            $rows = $rows->where('modul', $request->modul);
        }

        if ($request->filled('aksi')) {
            $rows = $rows->where('aksi', $request->aksi);
        }

        $logs = $this->paginateCollection($rows->values(), $request);

        return view('pages.admin.activity-log.index', compact('logs', 'moduls', 'aksis'));
    }

    private function splitActivity(?string $activity): array
    {
        $value = trim((string) $activity);

        if ($value === '') {
            return ['Sistem', '-'];
        }

        $parts = preg_split('/\s*-\s*/', $value, 2);

        if (count($parts) === 2) {
            return [ucfirst($parts[0]), ucfirst($parts[1])];
        }

        return ['Sistem', ucfirst($value)];
    }

    private function paginateCollection(Collection $items, Request $request): LengthAwarePaginator
    {
        $perPage = 20;
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $pagedItems = $items->slice(($currentPage - 1) * $perPage, $perPage)->values();

        return new LengthAwarePaginator(
            $pagedItems,
            $items->count(),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );
    }
}
