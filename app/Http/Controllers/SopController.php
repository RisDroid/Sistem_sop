<?php

namespace App\Http\Controllers;

use App\Models\Sop;
use App\Models\Subjek;
use App\Models\Timkerja;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SopController extends Controller
{
    /**
     * 1. TAMPILKAN DAFTAR SOP (INDEX)
     * Dimodifikasi agar default hanya menampilkan yang aktif.
     */
    public function index(Request $request)
    {
        $query = Sop::with(['subjek.timkerja']);

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
        $subjek = Subjek::all();
        $units = Timkerja::orderBy('nama_timkerja')->get();

        return view('pages.admin.sop.index', compact('allSop', 'subjek', 'units'));
    }

    public function aksesCepat()
    {
        $subjek = Subjek::where('status', 'aktif')->withCount('sop')->get();
        return view('pages.admin.sop.akses_cepat', compact('subjek'));
    }

    public function create()
    {
        $subjek = Subjek::all();
        $units = Timkerja::all();
        return view('pages.admin.sop.create', compact('subjek', 'units'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_sop'  => 'required|string|max:255',
            'nomor_sop' => 'required|string|max:100',
            'link_sop'  => 'required|mimes:pdf|max:10240',
            'id_subjek' => 'required|exists:tb_subjek,id_subjek',
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

        return redirect()->route('admin.sop.index')->with('success', 'Data SOP telah berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $sop = Sop::findOrFail($id);
        $subjek = Subjek::all();
        $units = Timkerja::all();
        return view('pages.admin.sop.edit', compact('sop', 'subjek', 'units'));
    }

    public function update(Request $request, $id)
    {
        $sop = Sop::findOrFail($id);

        $request->validate([
            'nama_sop'  => 'required|string|max:255',
            'nomor_sop' => 'required|string|max:100',
            'id_subjek' => 'required|exists:tb_subjek,id_subjek',
            'tahun'     => 'required|numeric',
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

        return redirect()->route('admin.sop.index')->with('success', 'Perubahan data SOP telah berhasil diperbarui.');
    }

    /**
     * 7. PROSES SIMPAN REVISI (FIXED)
     */
    public function storeRevisi(Request $request)
    {
        $request->validate([
            'id_sop_induk'       => 'required|exists:tb_sop,id_sop',
            'link_sop'           => 'required|mimes:pdf|max:10240',
            'keterangan_revisi'  => 'required|string',
        ]);

        DB::transaction(function () use ($request) {
            $sopInduk = Sop::findOrFail($request->id_sop_induk);

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

        return redirect()->route('admin.sop.index')
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
        $sop = Sop::findOrFail($id);
        $namaSop = $sop->nama_sop;
        $statusHapus = $sop->status;

        if ($sop->link_sop) {
            Storage::disk('public')->delete($sop->link_sop);
        }
        $sop->delete();

        $this->normalizeRevisionStatuses($namaSop);

        return redirect()->route('admin.sop.index')->with('success', 'Data SOP telah berhasil dihapus.');
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
            return redirect()->route('admin.sop.index')->with('success', 'Data terpilih berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        return redirect()->route('admin.sop.index');
    }
}
