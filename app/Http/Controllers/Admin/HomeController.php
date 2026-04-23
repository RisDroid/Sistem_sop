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
        $teamScopedRole = in_array($role, ['operator', 'viewer'], true)
            && !($role === 'viewer' && !$teamId);
        $teamName = $user->timkerja?->nama_timkerja;

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

        $activeSopQuery = clone $sopQuery;
        $activeSopQuery->where('status', 'aktif');

        $subjekData = $subjekQuery->with('timkerja')->get();
        $labels = $subjekData->pluck('nama_subjek')->toArray();
        $sopCountsBySubjek = (clone $activeSopQuery)
            ->selectRaw('id_subjek, COUNT(*) as total')
            ->groupBy('id_subjek')
            ->pluck('total', 'id_subjek');
        $dataCounts = $subjekData
            ->map(fn (Subjek $subjek) => (int) ($sopCountsBySubjek[$subjek->id_subjek] ?? 0))
            ->toArray();

        $totalSop = (clone $activeSopQuery)->count();
        $totalSubjek = $subjekData->count();
        $totalMonitoring = (clone $activeSopQuery)->has('monitorings')->count();
        $totalEvaluasi = (clone $activeSopQuery)->has('evaluasis')->count();
        $belumMonitoring = max(0, $totalSop - $totalMonitoring);
        $monitoringBelumEvaluasi = max(0, $totalMonitoring - $totalEvaluasi);
        $sudahEvaluasi = $totalEvaluasi;

        $recentSops = (clone $activeSopQuery)
            ->with('subjek.timkerja')
            ->orderByDesc('id_sop')
            ->limit(5)
            ->get();

        $pendingEvaluasiSops = (clone $activeSopQuery)
            ->with(['subjek.timkerja', 'latestMonitoring'])
            ->has('monitorings')
            ->doesntHave('evaluasis')
            ->get()
            ->sortByDesc(function (Sop $sop) {
                return optional($sop->latestMonitoring)->tanggal;
            })
            ->take(5)
            ->values();

        $recentActivities = collect()
            ->concat(
                (clone $monitoringQuery)
                    ->with(['sop.subjek.timkerja', 'user'])
                    ->orderByDesc('tanggal')
                    ->limit(4)
                    ->get()
                    ->map(fn (Monitoring $monitoring) => [
                        'type' => 'Monitoring',
                        'title' => $monitoring->sop?->nama_sop ?? 'SOP',
                        'subtitle' => $monitoring->sop?->subjek?->timkerja?->nama_timkerja ?? 'Internal',
                        'date' => $monitoring->tanggal,
                        'actor' => $monitoring->user?->nama ?? 'Pengguna',
                    ])
            )
            ->concat(
                (clone $evaluasiQuery)
                    ->with(['sop.subjek.timkerja', 'user'])
                    ->orderByDesc('tanggal')
                    ->limit(4)
                    ->get()
                    ->map(fn (Evaluasi $evaluasi) => [
                        'type' => 'Evaluasi',
                        'title' => $evaluasi->sop?->nama_sop ?? 'SOP',
                        'subtitle' => $evaluasi->sop?->subjek?->timkerja?->nama_timkerja ?? 'Internal',
                        'date' => $evaluasi->tanggal,
                        'actor' => $evaluasi->user?->nama ?? 'Pengguna',
                    ])
            )
            ->sortByDesc('date')
            ->take(6)
            ->values();

        $scopeLabel = match ($role) {
            'admin' => 'Semua tim kerja dan seluruh repositori SOP',
            'operator' => 'Ringkasan operasional untuk tim kerja ' . ($teamName ?: 'Anda'),
            default => $teamName
                ? 'Mode baca untuk tim kerja ' . $teamName
                : 'Mode baca untuk seluruh repositori SOP',
        };

        return view('pages.admin.dashboard_role', compact(
            'role',
            'teamName',
            'labels',
            'dataCounts',
            'totalSop',
            'totalSubjek',
            'totalMonitoring',
            'totalEvaluasi',
            'belumMonitoring',
            'monitoringBelumEvaluasi',
            'sudahEvaluasi',
            'recentSops',
            'pendingEvaluasiSops',
            'recentActivities',
            'scopeLabel'
        ));
    }
}
