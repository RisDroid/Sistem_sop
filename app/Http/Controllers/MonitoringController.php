<?php

namespace App\Http\Controllers;

use App\Models\Monitoring;
use App\Models\Sop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Support\ActivityLogger;

class MonitoringController extends Controller
{
    private const TINDAKAN = [
        'Tidak Perlu Revisi',
        'Perlu Revisi',
    ];

    private function routePrefix(): string
    {
        return strtolower((string) Auth::user()?->role ?: 'admin');
    }

    private function currentTeamId(): ?int
    {
        return Auth::user()?->id_timkerja;
    }

    private function isScopedRole(): bool
    {
        return in_array($this->routePrefix(), ['operator', 'viewer'], true);
    }

    private function shouldScopeToCurrentTeam(): bool
    {
        if (!$this->isScopedRole()) {
            return false;
        }

        $role = $this->routePrefix();
        $teamId = $this->currentTeamId();

        if ($role === 'viewer' && !$teamId) {
            return false;
        }

        return true;
    }

    private function applyRoleScope($query)
    {
        if (!$this->shouldScopeToCurrentTeam()) {
            return $query;
        }

        $teamId = $this->currentTeamId();

        if (!$teamId) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereHas('sop.subjek', function ($subQuery) use ($teamId) {
            $subQuery->where('id_timkerja', $teamId);
        });
    }

    private function visibleSopQuery()
    {
        $query = Sop::query()->where('status', 'aktif')->orderBy('nama_sop');

        if (!$this->shouldScopeToCurrentTeam()) {
            return $query;
        }

        $teamId = $this->currentTeamId();

        if (!$teamId) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereHas('subjek', function ($subQuery) use ($teamId) {
            $subQuery->where('id_timkerja', $teamId);
        });
    }

    private function visibleSopIds(): array
    {
        return $this->visibleSopQuery()->pluck('id_sop')->map(fn ($id) => (int) $id)->all();
    }

    private function findVisibleMonitoringOrFail(int $id): Monitoring
    {
        $query = Monitoring::query()->where('id_monitoring', $id);
        $this->applyRoleScope($query);

        return $query->firstOrFail();
    }

    public function index()
    {
        $monitorings = Monitoring::with(['sop.subjek.timkerja', 'user'])
            ->orderBy('id_monitoring', 'desc');

        $this->applyRoleScope($monitorings);

        $monitorings = $monitorings
            ->get();

        return view('pages.monitoring.index', compact('monitorings'));
    }

    public function create()
    {
        $sops = $this->visibleSopQuery()->get();
        $currentTimkerja = Auth::user()?->timkerja?->nama_timkerja;

        return view('pages.monitoring.create', [
            'sops' => $sops,
            'tindakanOptions' => self::TINDAKAN,
            'currentTimkerja' => $currentTimkerja,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_sop' => ['required', Rule::in($this->visibleSopIds())],
            'kriteria_penilaian' => 'required|in:Berjalan dengan baik,Tidak berjalan dengan baik',
            'hasil_monitoring' => 'required|string',
            'tindakan_yang_harus_diambil' => 'required|string|in:' . implode(',', self::TINDAKAN),
        ]);

        $monitoring = Monitoring::create([
            'id_sop' => $request->id_sop,
            'id_user' => Auth::id(),
            'tanggal' => now(),
            'kriteria_penilaian' => $request->kriteria_penilaian,
            'hasil_monitoring' => $request->hasil_monitoring,
            'tindakan_yang_harus_diambil' => $request->tindakan_yang_harus_diambil,
            'catatan' => null,
        ]);

        ActivityLogger::log(
            'Monitoring',
            'create',
            'Menambahkan data monitoring untuk SOP.',
            'Monitoring',
            $monitoring->id_monitoring,
            ['id_sop' => $monitoring->id_sop, 'tindakan' => $monitoring->tindakan_yang_harus_diambil],
            $request
        );

        $prefix = $this->routePrefix();

        return redirect()->route($prefix . '.monitoring.index')->with('success', 'Data Monitoring berhasil disimpan!');
    }

    public function destroy($id)
    {
        $monitoring = $this->findVisibleMonitoringOrFail((int) $id);
        $idMonitoring = $monitoring->id_monitoring;
        $idSop = $monitoring->id_sop;
        $monitoring->delete();

        ActivityLogger::log(
            'Monitoring',
            'delete',
            'Menghapus data monitoring.',
            'Monitoring',
            $idMonitoring,
            ['id_sop' => $idSop]
        );

        return redirect()->back()->with('success', 'Data Monitoring berhasil dihapus!');
    }

    public function show($id)
    {
        return redirect()->route($this->routePrefix() . '.monitoring.index');
    }

    public function edit($id)
    {
        return redirect()->route($this->routePrefix() . '.monitoring.index');
    }

    public function update(Request $request, $id)
    {
        return redirect()->route($this->routePrefix() . '.monitoring.index');
    }
}
