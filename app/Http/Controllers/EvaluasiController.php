<?php

namespace App\Http\Controllers;

use App\Models\Evaluasi;
use App\Models\Sop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Support\ActivityLogger;

class EvaluasiController extends Controller
{
    private const KRITERIA = [
        'Mampu mendorong peningkatan kinerja',
        'Mudah dipahami',
        'Mudah dilaksanakan',
        'Semua orang dapat menjalankan perannya masing-masing',
        'Mampu mengatasi permasalahan yang berkaitan dengan proses',
        'Mampu menjawab kebutuhan peningkatan kinerja organisasi',
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
        $query = Sop::query()
            ->where('status', 'aktif')
            ->whereHas('monitorings')
            ->orderBy('nama_sop');

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

    private function findVisibleEvaluasiOrFail(int $id): Evaluasi
    {
        $query = Evaluasi::query()->where('id_evaluasi', $id);
        $this->applyRoleScope($query);

        return $query->firstOrFail();
    }

    public function index()
    {
        $evaluasis = Evaluasi::with(['sop.subjek.timkerja', 'user'])
            ->orderBy('id_evaluasi', 'desc');

        $this->applyRoleScope($evaluasis);

        $evaluasis = $evaluasis
            ->get();

        return view('pages.evaluasi.index', [
            'evaluasis' => $evaluasis,
            'kriteriaOptions' => self::KRITERIA,
        ]);
    }

    public function create()
    {
        $sops = $this->visibleSopQuery()->get();
        $currentTimkerja = Auth::user()?->timkerja?->nama_timkerja;

        return view('pages.evaluasi.create', [
            'sops' => $sops,
            'kriteriaOptions' => self::KRITERIA,
            'currentTimkerja' => $currentTimkerja,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_sop' => ['required', Rule::in($this->visibleSopIds())],
            'kriteria_evaluasi' => 'required|array|min:1',
            'kriteria_evaluasi.*' => 'required|string|in:' . implode(',', self::KRITERIA),
        ], [
            'kriteria_evaluasi.required' => 'Pilih minimal satu kriteria evaluasi.',
            'kriteria_evaluasi.min' => 'Pilih minimal satu kriteria evaluasi.',
        ]);

        $evaluasi = Evaluasi::create([
            'id_sop' => $request->id_sop,
            'id_user' => Auth::id(),
            'tanggal' => now(),
            'kriteria_evaluasi' => array_values($request->kriteria_evaluasi),
            'hasil_evaluasi' => 'Evaluasi tersimpan berdasarkan kriteria penilaian.',
            'catatan' => null,
        ]);

        ActivityLogger::log(
            'Evaluasi',
            'create',
            'Menambahkan data evaluasi untuk SOP.',
            'Evaluasi',
            $evaluasi->id_evaluasi,
            ['id_sop' => $evaluasi->id_sop, 'jumlah_kriteria' => count($evaluasi->kriteria_evaluasi ?? [])],
            $request
        );

        $prefix = $this->routePrefix();

        return redirect()
            ->route($prefix . '.evaluasi.index')
            ->with('success', 'Data evaluasi berhasil disimpan!');
    }

    public function destroy($id)
    {
        $evaluasi = $this->findVisibleEvaluasiOrFail((int) $id);
        $idEvaluasi = $evaluasi->id_evaluasi;
        $idSop = $evaluasi->id_sop;
        $evaluasi->delete();

        ActivityLogger::log(
            'Evaluasi',
            'delete',
            'Menghapus data evaluasi.',
            'Evaluasi',
            $idEvaluasi,
            ['id_sop' => $idSop]
        );

        return redirect()->back()->with('success', 'Data evaluasi berhasil dihapus!');
    }

    public function show($id)
    {
        return redirect()->route($this->routePrefix() . '.evaluasi.index');
    }

    public function edit($id)
    {
        return redirect()->route($this->routePrefix() . '.evaluasi.index');
    }

    public function update(Request $request, $id)
    {
        return redirect()->route($this->routePrefix() . '.evaluasi.index');
    }
}
