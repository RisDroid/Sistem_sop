<?php

namespace App\Http\Controllers;

use App\Models\Evaluasi;
use App\Models\Sop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

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
        return $this->routePrefix() === 'operator';
    }

    private function applyRoleScope($query)
    {
        if (!$this->isScopedRole()) {
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

        if (!$this->isScopedRole()) {
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
        $evaluasis = Evaluasi::with(['sop', 'user'])
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

        return view('pages.evaluasi.create', [
            'sops' => $sops,
            'kriteriaOptions' => self::KRITERIA,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_sop' => ['required', Rule::in($this->visibleSopIds())],
            'kriteria_evaluasi' => 'required|array|min:1',
            'kriteria_evaluasi.*' => 'required|string|in:' . implode(',', self::KRITERIA),
            'hasil_evaluasi' => 'required|string',
            'catatan' => 'nullable|string',
        ], [
            'kriteria_evaluasi.required' => 'Pilih minimal satu kriteria evaluasi.',
            'kriteria_evaluasi.min' => 'Pilih minimal satu kriteria evaluasi.',
            'hasil_evaluasi.required' => 'Hasil evaluasi wajib diisi.',
        ]);

        Evaluasi::create([
            'id_sop' => $request->id_sop,
            'id_user' => Auth::id(),
            'tanggal' => now(),
            'kriteria_evaluasi' => array_values($request->kriteria_evaluasi),
            'hasil_evaluasi' => $request->hasil_evaluasi,
            'catatan' => $request->catatan,
        ]);

        $prefix = $this->routePrefix();

        return redirect()
            ->route($prefix . '.evaluasi.index')
            ->with('success', 'Data evaluasi berhasil disimpan!');
    }

    public function destroy($id)
    {
        $evaluasi = $this->findVisibleEvaluasiOrFail((int) $id);
        $evaluasi->delete();

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
