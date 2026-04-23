<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LoginLogController extends Controller
{
    public function index(Request $request)
    {
        if (!Schema::hasTable('tb_login_logs')) {
            return view('pages.admin.login-log.index', [
                'logs' => $this->paginateCollection(collect(), $request),
                'aksis' => collect(),
            ]);
        }

        $rows = DB::table('tb_login_logs as logs')
            ->leftJoin('tb_user as users', 'users.id_user', '=', 'logs.id_user')
            ->select([
                'logs.id_login_log',
                'logs.ip_address',
                'logs.login_at',
                'logs.last_activity_at',
                'logs.logout_at',
                'logs.is_active',
                'users.nama',
                'users.username',
                'users.role',
            ])
            ->orderByDesc('logs.login_at')
            ->get()
            ->map(function ($row) {
                $row->created_date = $row->login_at ? Carbon::parse($row->login_at) : null;
                $row->nama_user = $row->nama ?: $row->username ?: 'Guest';
                $row->role_user = $row->role ?: '-';
                $row->aksi = $row->logout_at ? 'logout' : ($row->is_active ? 'login' : 'login');
                $row->deskripsi = $row->logout_at
                    ? 'Sesi login sudah ditutup pada ' . Carbon::parse($row->logout_at)->format('d M Y H:i:s')
                    : 'Pengguna login ke sistem.';

                return $row;
            });

        $aksis = collect(['login', 'logout']);

        if ($request->filled('aksi')) {
            $rows = $rows->where('aksi', $request->aksi);
        }

        $logs = $this->paginateCollection($rows->values(), $request);

        return view('pages.admin.login-log.index', compact('logs', 'aksis'));
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
