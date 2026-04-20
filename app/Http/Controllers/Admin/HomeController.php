<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Evaluasi;
use App\Models\Monitoring;
use App\Models\Sop;
use App\Models\Subjek;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $role = strtolower((string) $user->role);
        $teamId = $user->id_timkerja;
        $teamScopedRole = $role === 'operator';

        $subjekQuery = Subjek::query()->where('status', 'aktif');
        $sopQuery = Sop::query();
        $monitoringQuery = Monitoring::query();
        $evaluasiQuery = Evaluasi::query();

        if ($teamScopedRole) {
            if ($teamId) {
                $subjekQuery->where('id_timkerja', $teamId);
                $sopQuery->whereHas('subjek', function ($query) use ($teamId) {
                    $query->where('id_timkerja', $teamId);
                });
                $monitoringQuery->whereHas('sop.subjek', function ($query) use ($teamId) {
                    $query->where('id_timkerja', $teamId);
                });
                $evaluasiQuery->whereHas('sop.subjek', function ($query) use ($teamId) {
                    $query->where('id_timkerja', $teamId);
                });
            } else {
                $subjekQuery->whereRaw('1 = 0');
                $sopQuery->whereRaw('1 = 0');
                $monitoringQuery->whereRaw('1 = 0');
                $evaluasiQuery->whereRaw('1 = 0');
            }
        }

        $visibleSopQuery = clone $sopQuery;

        if ($role === 'viewer') {
            $visibleSopQuery->where('status', 'aktif');
        }

        $subjekData = $subjekQuery->with('timkerja')->get();
        $labels = $subjekData->pluck('nama_subjek')->toArray();
        $dataCounts = $subjekData
            ->map(function (Subjek $subjek) use ($role) {
                $query = Sop::where('id_subjek', $subjek->id_subjek);

                if ($role === 'viewer') {
                    $query->where('status', 'aktif');
                }

                return $query->count();
            })
            ->toArray();

        $totalSop = (clone $visibleSopQuery)->count();
        $totalSubjek = $subjekData->count();
        $aman = (clone $sopQuery)->where('status', 'aktif')->count();
        $review = (clone $sopQuery)->where('revisi_ke', '>', 0)->count();
        $kritis = (clone $sopQuery)->whereIn('status', ['kadaluarsa', 'nonaktif'])->count();
        $totalMonitoring = (clone $monitoringQuery)->count();
        $totalEvaluasi = (clone $evaluasiQuery)->count();

        $recentSops = (clone $visibleSopQuery)
            ->with('subjek.timkerja')
            ->orderByDesc('id_sop')
            ->limit(5)
            ->get();

        $scopeLabel = match ($role) {
            'admin' => 'Semua tim kerja dan seluruh repositori SOP',
            'operator' => 'Ringkasan operasional untuk tim kerja Anda',
            default => 'Tampilan baca untuk dokumen dan aktivitas tim kerja Anda',
        };

        return view('pages.admin.dashboard', compact(
            'role',
            'labels',
            'dataCounts',
            'totalSop',
            'totalSubjek',
            'aman',
            'kritis',
            'review',
            'totalMonitoring',
            'totalEvaluasi',
            'recentSops',
            'scopeLabel'
        ));
    }
}
