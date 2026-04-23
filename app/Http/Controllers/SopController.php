<?php

namespace App\Http\Controllers;

use App\Models\Sop;
use App\Models\Subjek;
use App\Models\Timkerja;
use App\Models\Monitoring;
use App\Models\Evaluasi;
use App\Models\SopRevisionAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use App\Support\ActivityLogger;

class SopController extends Controller
{
    private const SOP_FILE_MAX_KB = 51200;
    private const BULK_SOP_UPLOAD_LIMIT = 50;
    private const BULK_REVISION_FILE_LIMIT = 50;

    private function isAdmin(): bool
    {
        return $this->routePrefix() === 'admin';
    }

    private function canManageSop(): bool
    {
        return in_array($this->routePrefix(), ['admin', 'operator'], true);
    }

    private function createSopRecord(array $attributes, string $storedPath): Sop
    {
        return Sop::create([
            'nama_sop' => $attributes['nama_sop'],
            'nomor_sop' => $attributes['nomor_sop'],
            'id_subjek' => $attributes['id_subjek'],
            'revisi_ke' => 0,
            'link_sop' => $storedPath,
            'status' => 'aktif',
            'tahun' => $attributes['tahun'],
            'created_date' => now(),
            'created_by' => Auth::id(),
        ]);
    }

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

    private function applyOperatorScope($query)
    {
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

    private function visibleSubjekIds(): array
    {
        $query = Subjek::query();

        if ($this->shouldScopeToCurrentTeam()) {
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

        if ($this->shouldScopeToCurrentTeam()) {
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

        if ($this->shouldScopeToCurrentTeam()) {
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

    private function visibleActiveSopQuery()
    {
        $query = Sop::with(['subjek.timkerja', 'latestMonitoring', 'latestEvaluasi'])
            ->where('status', 'aktif');

        $this->applyOperatorScope($query);

        return $query;
    }

    private function revisionReadiness(Sop $sop): array
    {
        $latestMonitoring = $sop->relationLoaded('latestMonitoring')
            ? $sop->latestMonitoring
            : $sop->latestMonitoring()->first();

        $latestEvaluasi = $sop->relationLoaded('latestEvaluasi')
            ? $sop->latestEvaluasi
            : $sop->latestEvaluasi()->first();

        if (!$latestMonitoring) {
            return [
                'can_revise' => false,
                'message' => 'SOP harus dimonitoring terlebih dahulu.',
            ];
        }

        if (!$latestEvaluasi) {
            return [
                'can_revise' => false,
                'message' => 'SOP harus dievaluasi terlebih dahulu.',
            ];
        }

        if (($latestMonitoring->tindakan_yang_harus_diambil ?? null) !== 'Perlu Revisi') {
            return [
                'can_revise' => false,
                'message' => 'Monitoring terakhir menyatakan SOP tidak perlu revisi.',
            ];
        }

        return [
            'can_revise' => true,
            'message' => 'SOP sudah memenuhi syarat revisi.',
        ];
    }

    /**
     * 1. TAMPILKAN DAFTAR SOP (INDEX)
     * Dimodifikasi agar default hanya menampilkan yang aktif.
     */
    public function index(Request $request)
    {
        $query = Sop::with(['subjek.timkerja'])
            ->withCount(['monitorings', 'evaluasis'])
            ->with(['latestMonitoring', 'latestEvaluasi']);

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
        $allSop->getCollection()->transform(function (Sop $item) {
            $readiness = $this->revisionReadiness($item);
            $item->setAttribute('can_revise', $readiness['can_revise']);
            $item->setAttribute('revision_message', $readiness['message']);

            return $item;
        });
        $subjek = $this->visibleSubjekQuery()->get();
        $units = $this->visibleUnitsQuery()->get();

        return view('pages.admin.sop.index', compact('allSop', 'subjek', 'units'));
    }

    public function laporanTahunan(Request $request)
    {
        abort_unless($this->routePrefix() === 'viewer', 403);

        $years = Monitoring::query()
            ->selectRaw('YEAR(tanggal) as tahun')
            ->whereNotNull('tanggal');

        if ($this->shouldScopeToCurrentTeam()) {
            $teamId = $this->currentTeamId();

            if ($teamId) {
                $years->whereHas('sop.subjek', function ($query) use ($teamId) {
                    $query->where('id_timkerja', $teamId);
                });
            } else {
                $years->whereRaw('1 = 0');
            }
        }

        $years = $years
            ->distinct()
            ->orderByDesc(DB::raw('YEAR(tanggal)'))
            ->pluck('tahun')
            ->map(fn ($year) => (int) $year)
            ->filter()
            ->values();

        $selectedYear = (int) ($request->input('tahun') ?: ($years->first() ?? now()->year));

        $monitoredSopIds = Monitoring::query()
            ->select('id_sop')
            ->whereYear('tanggal', $selectedYear)
            ->distinct();

        if ($this->shouldScopeToCurrentTeam()) {
            $teamId = $this->currentTeamId();

            if ($teamId) {
                $monitoredSopIds->whereHas('sop.subjek', function ($query) use ($teamId) {
                    $query->where('id_timkerja', $teamId);
                });
            } else {
                $monitoredSopIds->whereRaw('1 = 0');
            }
        }

        $yearlyBaseQuery = Sop::query()
            ->whereIn('id_sop', $monitoredSopIds);
        $this->applyOperatorScope($yearlyBaseQuery);

        $summary = [
            'total_sop' => (clone $yearlyBaseQuery)->count(),
            'total_subjek' => (clone $yearlyBaseQuery)->distinct('id_subjek')->count('id_subjek'),
            'sudah_monitoring' => (clone $yearlyBaseQuery)->count(),
            'sudah_evaluasi' => Evaluasi::query()
                ->whereYear('tanggal', $selectedYear)
                ->whereIn('id_sop', $monitoredSopIds)
                ->when($this->shouldScopeToCurrentTeam(), function ($query) {
                    $teamId = $this->currentTeamId();

                    if ($teamId) {
                        $query->whereHas('sop.subjek', function ($subQuery) use ($teamId) {
                            $subQuery->where('id_timkerja', $teamId);
                        });
                    } else {
                        $query->whereRaw('1 = 0');
                    }
                })
                ->distinct('id_sop')
                ->count('id_sop'),
        ];

        $reportSops = (clone $yearlyBaseQuery)
            ->with(['subjek.timkerja'])
            ->orderBy('id_subjek')
            ->orderBy('nomor_sop')
            ->orderBy('nama_sop')
            ->get();

        $sopIds = $reportSops->pluck('id_sop')->map(fn ($id) => (int) $id)->all();

        $monitoringBySopId = empty($sopIds)
            ? collect()
            : Monitoring::query()
                ->whereIn('id_sop', $sopIds)
                ->whereYear('tanggal', $selectedYear)
                ->orderByDesc('tanggal')
                ->orderByDesc('id_monitoring')
                ->get()
                ->groupBy('id_sop')
                ->map(fn ($items) => $items->first());

        $evaluasiBySopId = empty($sopIds)
            ? collect()
            : Evaluasi::query()
                ->whereIn('id_sop', $sopIds)
                ->whereYear('tanggal', $selectedYear)
                ->orderByDesc('tanggal')
                ->orderByDesc('id_evaluasi')
                ->get()
                ->groupBy('id_sop')
                ->map(fn ($items) => $items->first());

        $reportRows = $reportSops->map(function (Sop $sop) use ($monitoringBySopId, $evaluasiBySopId) {
            $monitoring = $monitoringBySopId->get($sop->id_sop);
            $evaluasi = $evaluasiBySopId->get($sop->id_sop);

            return (object) [
                'sop' => $sop,
                'monitoring' => $monitoring,
                'evaluasi' => $evaluasi,
                'subjek_label' => $sop->subjek?->nama_subjek ?? 'Tanpa Subjek',
                'unit_label' => $sop->subjek?->timkerja?->nama_timkerja ?? 'Tanpa Tim Kerja',
            ];
        });

        $groupedRows = $reportRows->groupBy('unit_label');

        return view('pages.admin.sop.laporan_tahunan', [
            'availableYears' => $years,
            'selectedYear' => $selectedYear,
            'summary' => $summary,
            'groupedRows' => $groupedRows,
        ]);
    }

    public function aksesCepat()
    {
        $role = strtolower((string) Auth::user()?->role ?: 'admin');

        $subjekQuery = Subjek::query()
            ->where('status', 'aktif')
            ->with('timkerja');

        if ($this->shouldScopeToCurrentTeam()) {
            $teamId = Auth::user()?->id_timkerja;

            if ($teamId) {
                $subjekQuery->where('id_timkerja', $teamId);
            } else {
                $subjekQuery->whereRaw('1 = 0');
            }
        }

        $subjek = $subjekQuery
            ->orderBy('nama_subjek')
            ->get();

        $visibleSopCountBySubjekId = $subjek->isEmpty()
            ? collect()
            : Sop::query()
                ->select('id_subjek', DB::raw('COUNT(*) as total'))
                ->where('status', 'aktif')
                ->whereIn('id_subjek', $subjek->pluck('id_subjek')->all())
                ->groupBy('id_subjek')
                ->pluck('total', 'id_subjek');

        $subjekSummary = $subjek
            ->groupBy(function (Subjek $item) {
                return mb_strtolower(trim((string) $item->nama_subjek));
            })
            ->map(function ($items) use ($visibleSopCountBySubjekId) {
                /** @var \Illuminate\Support\Collection $items */
                $first = $items->first();

                return (object) [
                    'nama_subjek' => $first->nama_subjek,
                    'deskripsi' => $items->pluck('deskripsi')->filter()->first(),
                    'visible_sop_count' => $items->sum(function (Subjek $item) use ($visibleSopCountBySubjekId) {
                        return (int) ($visibleSopCountBySubjekId[$item->id_subjek] ?? 0);
                    }),
                ];
            })
            ->sortBy('nama_subjek', SORT_NATURAL | SORT_FLAG_CASE)
            ->values();

        $summary = [
            'total_subjek' => $subjekSummary->count(),
            'total_sop' => $subjekSummary->sum('visible_sop_count'),
        ];

        $sourceSops = $this->visibleActiveSopQuery()
            ->orderBy('nama_sop')
            ->get()
            ->map(function (Sop $item) {
                $readiness = $this->revisionReadiness($item);
                $item->setAttribute('can_revise', $readiness['can_revise']);
                $item->setAttribute('revision_message', $readiness['message']);

                return $item;
            })
            ->groupBy(function (Sop $item) {
                return $item->subjek?->nama_subjek ?? 'Tanpa Subjek';
            });

        return view('pages.admin.sop.akses_cepat', compact('subjek', 'summary', 'role', 'sourceSops'));
    }

    public function storeAksesCepat(Request $request)
    {
        abort_unless($this->canManageSop(), 403);

        $visibleSubjekIds = $this->visibleSubjekIds();

        $request->validate([
            'source_sop_ids' => 'required|array|min:2',
            'source_sop_ids.*' => 'required|integer',
            'nama_sop' => 'required|string|max:255',
            'nomor_sop' => 'required|string|max:100',
            'id_subjek' => ['required', Rule::in($visibleSubjekIds)],
            'tahun' => 'required|numeric',
            'link_sop' => 'required|mimes:pdf|max:' . self::SOP_FILE_MAX_KB,
            'keterangan_revisi' => 'required|string',
        ]);

        $sourceIds = collect($request->input('source_sop_ids', []))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        if ($sourceIds->count() < 2) {
            throw ValidationException::withMessages([
                'source_sop_ids' => 'Pilih minimal 2 SOP yang akan digabung untuk revisi.',
            ]);
        }

        $storedPath = null;

        try {
            DB::transaction(function () use ($request, $sourceIds, &$storedPath) {
                $sourceQuery = $this->visibleActiveSopQuery()
                    ->whereIn('id_sop', $sourceIds)
                    ->lockForUpdate();

                $sourceSops = $sourceQuery->get();
                $scopedSourceIds = $sourceSops->pluck('id_sop')->map(fn ($id) => (int) $id)->values();
                $missingIds = $sourceIds->diff($scopedSourceIds);

                if ($sourceSops->count() !== $sourceIds->count() || $missingIds->isNotEmpty()) {
                    throw ValidationException::withMessages([
                        'source_sop_ids' => 'Ada SOP sumber yang tidak valid, sudah dipakai user lain, atau tidak bisa diakses.',
                    ]);
                }

                foreach ($sourceSops as $sourceSop) {
                    $readiness = $this->revisionReadiness($sourceSop);

                    if (!$readiness['can_revise']) {
                        throw ValidationException::withMessages([
                            'source_sop_ids' => 'SOP "' . $sourceSop->nama_sop . '" belum memenuhi syarat revisi: ' . $readiness['message'],
                        ]);
                    }
                }

                $targetSubjek = $this->visibleSubjekQuery()
                    ->where('id_subjek', (int) $request->id_subjek)
                    ->firstOrFail();

                $storedPath = $request->file('link_sop')->store('uploads/sop', 'public');

                $newSop = Sop::create([
                    'nama_sop' => $request->nama_sop,
                    'nomor_sop' => $request->nomor_sop,
                    'id_subjek' => (int) $request->id_subjek,
                    'tahun' => $request->tahun,
                    'revisi_ke' => 0,
                    'link_sop' => $storedPath,
                    'status' => 'aktif',
                    'keterangan' => $request->keterangan_revisi,
                    'created_date' => now(),
                    'created_by' => Auth::id(),
                ]);

                foreach ($sourceSops as $sourceSop) {
                    $sourceSop->update([
                        'status' => 'nonaktif',
                        'modified_date' => now(),
                        'modified_by' => Auth::id(),
                    ]);
                }

                $sourceNames = $sourceSops->pluck('nama_sop')->implode(', ');

                ActivityLogger::log(
                    'SOP',
                    'revisi-gabungan',
                    'Membuat SOP baru "' . $newSop->nama_sop . '" dari gabungan SOP: ' . $sourceNames,
                    'Sop',
                    $newSop->id_sop,
                    [
                        'sumber' => $sourceSops->pluck('id_sop')->values()->all(),
                        'target_subjek' => $targetSubjek->nama_subjek,
                    ],
                    $request
                );
            });
        } catch (\Throwable $exception) {
            if ($storedPath) {
                Storage::disk('public')->delete($storedPath);
            }

            throw $exception;
        }

        return redirect()->route($this->routePrefix() . '.sop.index')
            ->with('success', 'Revisi gabungan SOP berhasil disimpan dan SOP sumber sudah dinonaktifkan.');
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
        $subjek = $this->visibleSubjekQuery()->with('timkerja')->get();
        $units = $this->visibleUnitsQuery()->get();
        return view('pages.admin.sop.create', compact('subjek', 'units'));
    }

    public function bulkCreate()
    {
        abort_unless($this->isAdmin(), 403);

        $subjek = $this->visibleSubjekQuery()->with('timkerja')->get();
        $units = $this->visibleUnitsQuery()->get();

        return view('pages.admin.sop.bulk_create', compact('subjek', 'units'));
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

        $sop = $this->createSopRecord([
            'nama_sop' => $request->nama_sop,
            'nomor_sop' => $request->nomor_sop,
            'id_subjek' => $request->id_subjek,
            'tahun' => $request->tahun,
        ], $path);

        ActivityLogger::log(
            'SOP',
            'create',
            'Menambahkan SOP baru: ' . $sop->nama_sop,
            'Sop',
            $sop->id_sop,
            ['nomor_sop' => $sop->nomor_sop, 'id_subjek' => $sop->id_subjek],
            $request
        );

        return redirect()->route($this->routePrefix() . '.sop.index')->with('success', 'Data SOP telah berhasil ditambahkan.');
    }

    public function bulkStore(Request $request)
    {
        abort_unless($this->isAdmin(), 403);

        $visibleSubjekIds = $this->visibleSubjekIds();

        $request->validate([
            'entries' => 'required|array|min:1|max:' . self::BULK_SOP_UPLOAD_LIMIT,
            'entries.*.nama_sop' => 'required|string|max:255',
            'entries.*.nomor_sop' => 'required|string|max:100',
            'entries.*.id_subjek' => ['required', 'integer', Rule::in($visibleSubjekIds)],
            'entries.*.tahun' => 'required|numeric',
            'entries.*.link_sop' => 'required|file|mimes:pdf|max:' . self::SOP_FILE_MAX_KB,
            'entries.*.revision_files' => 'nullable|array|max:' . self::BULK_REVISION_FILE_LIMIT,
            'entries.*.revision_files.*' => 'nullable|file|mimes:pdf|max:' . self::SOP_FILE_MAX_KB,
        ], [
            'entries.required' => 'Minimal ada 1 SOP untuk input massal.',
            'entries.min' => 'Minimal ada 1 SOP untuk input massal.',
            'entries.max' => 'Maksimal 50 SOP dapat diunggah dalam sekali proses.',
            'entries.*.nama_sop.required' => 'Nama SOP pada setiap baris wajib diisi.',
            'entries.*.nomor_sop.required' => 'Nomor SOP pada setiap baris wajib diisi.',
            'entries.*.id_subjek.required' => 'Subjek pada setiap baris wajib dipilih.',
            'entries.*.tahun.required' => 'Tahun pada setiap baris wajib diisi.',
            'entries.*.link_sop.required' => 'File PDF pada setiap baris wajib diunggah.',
            'entries.*.revision_files.max' => 'Setiap SOP massal maksimal memiliki 50 file revisi.',
        ]);

        $storedPaths = [];
        $createdSops = collect();

        try {
            DB::beginTransaction();

            foreach ($request->input('entries', []) as $index => $entry) {
                $uploadedFile = $request->file("entries.$index.link_sop");
                $storedPath = $uploadedFile->store('uploads/sop', 'public');
                $storedPaths[] = $storedPath;

                $sop = $this->createSopRecord([
                    'nama_sop' => $entry['nama_sop'],
                    'nomor_sop' => $entry['nomor_sop'],
                    'id_subjek' => (int) $entry['id_subjek'],
                    'tahun' => $entry['tahun'],
                ], $storedPath);

                $createdSops->push($sop);

                foreach ($request->file("entries.$index.revision_files", []) ?? [] as $revisionFile) {
                    $revisionStoredPath = $revisionFile->store('uploads/sop/revisi', 'public');
                    $storedPaths[] = $revisionStoredPath;

                    SopRevisionAttachment::create([
                        'id_sop' => $sop->id_sop,
                        'original_name' => $revisionFile->getClientOriginalName(),
                        'file_path' => $revisionStoredPath,
                        'created_date' => now(),
                        'created_by' => Auth::id(),
                    ]);
                }
            }

            ActivityLogger::log(
                'SOP',
                'bulk-create',
                'Menambahkan SOP baru secara massal.',
                'Sop',
                $createdSops->pluck('id_sop')->implode(','),
                [
                    'total' => $createdSops->count(),
                    'nomor_sop' => $createdSops->pluck('nomor_sop')->values()->all(),
                    'total_revision_files' => SopRevisionAttachment::query()
                        ->whereIn('id_sop', $createdSops->pluck('id_sop')->all())
                        ->count(),
                ],
                $request
            );

            DB::commit();
        } catch (\Throwable $exception) {
            DB::rollBack();

            foreach ($storedPaths as $storedPath) {
                Storage::disk('public')->delete($storedPath);
            }

            throw $exception;
        }

        return redirect()
            ->route('admin.sop.index')
            ->with('success', $createdSops->count() . ' SOP berhasil ditambahkan sekaligus.');
    }

    public function edit($id)
    {
        $sop = $this->findVisibleSopOrFail((int) $id);
        $subjek = $this->visibleSubjekQuery()->with('timkerja')->get();
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

        ActivityLogger::log(
            'SOP',
            'update',
            'Memperbarui SOP: ' . $sop->nama_sop,
            'Sop',
            $sop->id_sop,
            ['nomor_sop' => $sop->nomor_sop, 'status' => $sop->status],
            $request
        );

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
            $readiness = $this->revisionReadiness($sopInduk);

            if (!$readiness['can_revise']) {
                throw ValidationException::withMessages([
                    'id_sop_induk' => $readiness['message'],
                ]);
            }

            $recentDuplicate = Sop::where('nama_sop', $sopInduk->nama_sop)
                ->where('created_by', Auth::id())
                ->where('keterangan', trim((string) $request->keterangan_revisi))
                ->where('created_date', '>=', now()->subSeconds(15))
                ->exists();

            if ($recentDuplicate) {
                throw ValidationException::withMessages([
                    'link_sop' => 'Revisi yang sama baru saja tersimpan. Mohon cek daftar SOP terlebih dahulu.',
                ]);
            }

            $lastRevisi = Sop::where('nama_sop', $sopInduk->nama_sop)
                ->lockForUpdate()
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

            ActivityLogger::log(
                'SOP',
                'revisi',
                'Menyimpan revisi SOP: ' . $newSop->nama_sop,
                'Sop',
                $newSop->id_sop,
                ['revisi_ke' => $newSop->revisi_ke],
                $request
            );
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
        $idSop = $sop->id_sop;

        if ($sop->link_sop) {
            Storage::disk('public')->delete($sop->link_sop);
        }
        $sop->delete();

        $this->normalizeRevisionStatuses($namaSop);

        ActivityLogger::log(
            'SOP',
            'delete',
            'Menghapus SOP: ' . $namaSop,
            'Sop',
            $idSop
        );

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

            ActivityLogger::log(
                'SOP',
                'bulk-delete',
                'Menghapus beberapa data SOP sekaligus.',
                'Sop',
                implode(',', $ids),
                ['total' => count($ids)],
                $request
            );

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
