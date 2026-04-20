<?php

namespace App\Http\Controllers;

use App\Models\Sop;
use App\Models\Subjek;
use App\Models\Timkerja;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class SopController extends Controller
{
    private const SOP_FILE_MAX_KB = 51200;

    private function routePrefix(): string
    {
        return strtolower((string) Auth::user()?->role ?: 'admin');
    }

    private function currentTeamId(): ?int
    {
        return Auth::user()?->id_timkerja;
    }

    private function isOperator(): bool
    {
        return $this->routePrefix() === 'operator';
    }

    private function applyOperatorScope($query)
    {
        if (!$this->isOperator()) {
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

    private function visibleSubjekIds(): array
    {
        $query = Subjek::query();

        if ($this->isOperator()) {
            $teamId = $this->currentTeamId();

            if (!$teamId) {
                return [];
            }

            $query->where('id_timkerja', $teamId);
        }

        return $query->pluck('id_subjek')->map(fn ($id) => (int) $id)->all();
    }

    private function visibleSubjekQuery()
    {
        $query = Subjek::query();

        if ($this->isOperator()) {
            $teamId = $this->currentTeamId();

            if (!$teamId) {
                return $query->whereRaw('1 = 0');
            }

            $query->where('id_timkerja', $teamId);
        }

        return $query;
    }

    private function visibleUnitsQuery()
    {
        $query = Timkerja::query()->orderBy('nama_timkerja');

        if ($this->isOperator()) {
            $teamId = $this->currentTeamId();

            if (!$teamId) {
                return $query->whereRaw('1 = 0');
            }

            $query->where('id_timkerja', $teamId);
        }

        return $query;
    }

    private function findVisibleSopOrFail(int $id): Sop
    {
        $query = Sop::with('subjek.timkerja')->where('id_sop', $id);
        $this->applyOperatorScope($query);

        return $query->firstOrFail();
    }

    /**
     * 1. TAMPILKAN DAFTAR SOP (INDEX)
     * Dimodifikasi agar default hanya menampilkan yang aktif.
     */
    public function index(Request $request)
    {
        $query = Sop::with(['subjek.timkerja'])
            ->withCount(['monitorings', 'evaluasis']);

        $this->applyOperatorScope($query);

        // Fitur Pencarian
        if ($request->has('search') && $request->search != '') {
            $query->where(function($q) use ($request) {
                $q->where('nama_sop', 'like', '%' . $request->search . '%')
                  ->orWhere('nomor_sop', 'like', '%' . $request->search . '%');
            });
        }

        // Filter berdasarkan Subjek
        if ($request->has('id_subjek') && $request->id_subjek != '') {
            $query->where('id_subjek', $request->id_subjek);
        }

        if ($request->has('nama_subjek') && $request->nama_subjek != '') {
            $query->whereHas('subjek', function ($q) use ($request) {
                $q->where('nama_subjek', $request->nama_subjek);
            });
        }

        if ($request->has('id_unit') && $request->id_unit != '') {
            $query->whereHas('subjek', function ($q) use ($request) {
                $q->where('id_timkerja', $request->id_unit);
            });
        }

        /**
         * LOGIKA TAMPILAN:
         * Jika sedang melihat riwayat (show_history), tampilkan semua versi untuk SOP tersebut.
         * Jika tidak, maka HANYA tampilkan yang aktif (status_active = 1).
         */
        if ($request->has('show_history') && $request->show_history != '') {
            $query->where('nama_sop', $request->show_history)
                  ->orderBy('revisi_ke', 'desc');
        } else {
            $query->where('status', 'aktif');
        }

        $allSop = $query->orderBy('id_sop', 'desc')->paginate(10);
        $subjek = $this->visibleSubjekQuery()->get();
        $units = $this->visibleUnitsQuery()->get();

        return view('pages.admin.sop.index', compact('allSop', 'subjek', 'units'));
    }

    public function aksesCepat()
    {
        $role = strtolower((string) Auth::user()?->role ?: 'admin');
        $teamId = Auth::user()?->id_timkerja;
        $teamScopedRole = $role === 'operator';

        $subjekQuery = Subjek::query()
            ->where('status', 'aktif')
            ->with('timkerja');

        if ($teamScopedRole) {
            if ($teamId) {
                $subjekQuery->where('id_timkerja', $teamId);
            } else {
                $subjekQuery->whereRaw('1 = 0');
            }
        }

        $subjek = $subjekQuery->get()
            ->groupBy(function (Subjek $item) {
                return mb_strtolower(trim((string) $item->nama_subjek));
            })
            ->map(function ($items) {
                /** @var \Illuminate\Support\Collection $items */
                $first = $items->first();

                return (object) [
                    'nama_subjek' => $first->nama_subjek,
                    'deskripsi' => $items->pluck('deskripsi')->filter()->first(),
                    'visible_sop_count' => Sop::whereIn('id_subjek', $items->pluck('id_subjek'))
                        ->where('status', 'aktif')
                        ->count(),
                ];
            })
            ->sortBy('nama_subjek', SORT_NATURAL | SORT_FLAG_CASE)
            ->values();

        $summary = [
            'total_subjek' => $subjek->count(),
            'total_sop' => $subjek->sum('visible_sop_count'),
        ];

        return view('pages.admin.sop.akses_cepat', compact('subjek', 'summary', 'role'));
    }

    public function history(int $id): JsonResponse
    {
        $sop = $this->findVisibleSopOrFail($id);

        $history = Sop::with('subjek.timkerja')
            ->where('nama_sop', $sop->nama_sop)
            ->orderBy('revisi_ke', 'desc')
            ->orderBy('id_sop', 'desc')
            ->get()
            ->map(function (Sop $item) {
                return [
                    'id_sop' => $item->id_sop,
                    'nama_sop' => $item->nama_sop,
                    'nomor_sop' => $item->nomor_sop,
                    'revisi_ke' => (int) $item->revisi_ke,
                    'revisi_label' => (int) $item->revisi_ke === 0 ? 'Versi Awal' : 'Revisi ke-' . $item->revisi_ke,
                    'status' => $item->status,
                    'status_label' => blank($item->status) ? '-' : ucfirst($item->status),
                    'tahun' => $item->tahun,
                    'subjek' => $item->subjek?->nama_subjek ?? 'Tanpa Subjek',
                    'timkerja' => $item->subjek?->timkerja?->nama_timkerja ?? 'Internal',
                    'keterangan' => $item->keterangan,
                    'view_url' => $item->link_sop ? route('view.pdf', basename($item->link_sop)) : null,
                ];
            })
            ->values();

        return response()->json([
            'latest' => $history->first(),
            'history' => $history,
        ]);
    }

    public function create()
    {
        $subjek = $this->visibleSubjekQuery()->get();
        $units = $this->visibleUnitsQuery()->get();
        return view('pages.admin.sop.create', compact('subjek', 'units'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_sop'  => 'required|string|max:255',
            'nomor_sop' => 'required|string|max:100',
            'link_sop'  => 'required|mimes:pdf|max:' . self::SOP_FILE_MAX_KB,
            'id_subjek' => ['required', Rule::in($this->visibleSubjekIds())],
            'tahun'     => 'required|numeric',
        ]);

        $path = $request->file('link_sop')->store('uploads/sop', 'public');

        Sop::create([
            'nama_sop'      => $request->nama_sop,
            'nomor_sop'     => $request->nomor_sop,
            'id_subjek'     => $request->id_subjek,
            'revisi_ke'     => 0,
            'link_sop'      => $path,
            'status'        => 'aktif',
            'tahun'         => $request->tahun,
            'created_date'  => now(),
            'created_by'    => Auth::id(),
        ]);

        return redirect()->route($this->routePrefix() . '.sop.index')->with('success', 'Data SOP telah berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $sop = $this->findVisibleSopOrFail((int) $id);
        $subjek = $this->visibleSubjekQuery()->get();
        $units = $this->visibleUnitsQuery()->get();
        return view('pages.admin.sop.edit', compact('sop', 'subjek', 'units'));
    }

    public function update(Request $request, $id)
    {
        $sop = $this->findVisibleSopOrFail((int) $id);

        $request->validate([
            'nama_sop'  => 'required|string|max:255',
            'nomor_sop' => 'required|string|max:100',
            'id_subjek' => ['required', Rule::in($this->visibleSubjekIds())],
            'tahun'     => 'required|numeric',
            'link_sop'  => 'nullable|mimes:pdf|max:' . self::SOP_FILE_MAX_KB,
        ]);

        if ($request->hasFile('link_sop')) {
            if ($sop->link_sop) {
                Storage::disk('public')->delete($sop->link_sop);
            }
            $path = $request->file('link_sop')->store('uploads/sop', 'public');
            $sop->link_sop = $path;
        }

        $sop->update([
            'nama_sop'      => $request->nama_sop,
            'nomor_sop'     => $request->nomor_sop,
            'id_subjek'     => $request->id_subjek,
            'status'        => $request->status ?? $sop->status,
            'tahun'         => $request->tahun,
            'modified_date' => now(),
            'modified_by'   => Auth::id(),
        ]);

        return redirect()->route($this->routePrefix() . '.sop.index')->with('success', 'Perubahan data SOP telah berhasil diperbarui.');
    }

    /**
     * 7. PROSES SIMPAN REVISI (FIXED)
     */
    public function storeRevisi(Request $request)
    {
        $request->validate([
            'id_sop_induk'       => 'required|exists:tb_sop,id_sop',
            'link_sop'           => 'required|mimes:pdf|max:' . self::SOP_FILE_MAX_KB,
            'keterangan_revisi'  => 'required|string',
        ]);

        DB::transaction(function () use ($request) {
            $sopInduk = $this->findVisibleSopOrFail((int) $request->id_sop_induk);

            $lastRevisi = Sop::where('nama_sop', $sopInduk->nama_sop)
                ->orderBy('revisi_ke', 'desc')
                ->first();

            $revisiBaru = $lastRevisi ? (int)$lastRevisi->revisi_ke + 1 : 1;

            $path = $request->file('link_sop')->store('uploads/sop', 'public');

            $newSop = Sop::create([
                'nama_sop'      => $sopInduk->nama_sop,
                'nomor_sop'     => $sopInduk->nomor_sop,
                'id_subjek'     => $sopInduk->id_subjek,
                'tahun'         => $sopInduk->tahun,
                'link_sop'      => $path,
                'revisi_ke'     => $revisiBaru,
                'status'        => 'aktif',
                'keterangan'    => $request->keterangan_revisi,
                'created_date'  => now(),
                'created_by'    => Auth::id(),
            ]);

            $this->normalizeRevisionStatuses($sopInduk->nama_sop);

            DB::table('tb_log_revisi')->insert([
                'id_sop'         => $newSop->id_sop,
                'tanggal_revisi' => now(),
                'revisi_ke'      => $revisiBaru,
                'keterangan'     => $request->keterangan_revisi,
                'created_by'     => Auth::id(),
                'created_at'     => now(),
            ]);
        });

        return redirect()->route($this->routePrefix() . '.sop.index')
            ->with('success', 'Revisi SOP berhasil disimpan.');
    }

    private function normalizeRevisionStatuses(string $namaSop): void
    {
        $historyData = Sop::where('nama_sop', $namaSop)
            ->orderBy('revisi_ke', 'desc')
            ->orderBy('id_sop', 'desc')
            ->get();

        if ($historyData->isEmpty()) {
            return;
        }

        $latestRevision = (int) $historyData->first()->revisi_ke;
        $oldestRevisionToKeepVisible = max(1, $latestRevision - 5);

        foreach ($historyData as $index => $history) {
            $targetStatus = 'nonaktif';

            if ($index === 0) {
                $targetStatus = 'aktif';
            } elseif ($latestRevision > 6 && (int) $history->revisi_ke < $oldestRevisionToKeepVisible) {
                $targetStatus = null;
            }

            $history->update([
                'status' => $targetStatus,
                'modified_date' => now(),
                'modified_by' => Auth::id(),
            ]);
        }
    }

    public function destroy($id)
    {
        $sop = $this->findVisibleSopOrFail((int) $id);
        $namaSop = $sop->nama_sop;
        $statusHapus = $sop->status;

        if ($sop->link_sop) {
            Storage::disk('public')->delete($sop->link_sop);
        }
        $sop->delete();

        $this->normalizeRevisionStatuses($namaSop);

        return redirect()->route($this->routePrefix() . '.sop.index')->with('success', 'Data SOP telah berhasil dihapus.');
    }

    public function getUnits($id_subjek)
    {
        $subjek = Subjek::with('timkerja')->find($id_subjek);

        if (!$subjek || !$subjek->timkerja) {
            return response()->json([]);
        }

        return response()->json([[
            'id_unit' => $subjek->timkerja->id_timkerja,
            'nama_unit' => $subjek->timkerja->nama_timkerja,
        ]]);
    }

    /**
     * FUNGSI HAPUS SEMUA (BULK DELETE)
     * Menggunakan Redirect agar halaman refresh dan dashboard sinkron
     */
    public function bulkDelete(Request $request)
    {
        $ids = $request->ids;

        if (!$ids || count($ids) == 0) {
            return redirect()->back()->with('error', 'Tidak ada data yang dipilih.');
        }

        try {
            $sops = Sop::whereIn('id_sop', $ids)->get();
            $affectedNames = $sops->pluck('nama_sop')->unique()->filter()->values();

            foreach ($sops as $sop) {
                if ($sop->link_sop) {
                    Storage::disk('public')->delete($sop->link_sop);
                }
                $sop->delete();
            }

            foreach ($affectedNames as $namaSop) {
                $this->normalizeRevisionStatuses($namaSop);
            }

            // Redirect kembali ke index agar angka dashboard & tabel terupdate
            return redirect()->route($this->routePrefix() . '.sop.index')->with('success', 'Data terpilih berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        return redirect()->route($this->routePrefix() . '.sop.index');
    }
}
