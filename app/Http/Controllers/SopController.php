<?php

namespace App\Http\Controllers;

use App\Models\Sop;
use App\Models\Subjek;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SopController extends Controller
{
    /**
     * 1. TAMPILKAN DAFTAR SOP (INDEX)
     * Menampilkan hanya SOP yang aktif agar tidak terjadi duplikasi di tabel utama.
     */
    public function index(Request $request)
    {
        $query = Sop::query();

        // Fitur Pencarian
        if ($request->has('search') && $request->search != '') {
            $query->where(function($q) use ($request) {
                $q->where('nama_sop', 'like', '%' . $request->search . '%')
                  ->orWhere('nomor_sop', 'like', '%' . $request->search . '%');
            });
        }

        // Fit his Filter Subjek
        if ($request->has('id_subjek') && $request->id_subjek != '') {
            $query->where('id_subjek', $request->id_subjek);
        }

        // LOGIKA BARU: Tampilkan Riwayat Berdasarkan Nama SOP
        if ($request->has('show_history') && $request->show_history != '') {
            // Menampilkan semua versi (aktif & non-aktif) untuk SOP yang diklik
            $query->where('nama_sop', $request->show_history)
                  ->orderBy(DB::raw("CASE WHEN revisi_ke = '-' THEN 0 ELSE CAST(revisi_ke AS UNSIGNED) END"), 'desc');
        } else {
            // Default: Hanya tampilkan SOP yang berstatus Aktif (1)
            $query->where('status_active', 1);
        }

        $allSop = $query->orderBy('id_sop', 'desc')->paginate(10);
        $subjek = Subjek::all();
        $units = Unit::all();

        return view('pages.admin.sop.index', compact('allSop', 'subjek', 'units'));
    }

    /**
     * 2. TAMPILKAN HALAMAN AKSES CEPAT
     */
    public function aksesCepat()
    {
        $subjek = Subjek::where('status', 'aktif')->withCount('sops')->get();
        return view('pages.admin.sop.akses_cepat', compact('subjek'));
    }

    /**
     * 3. TAMPILKAN FORM TAMBAH
     */
    public function create()
    {
        $units = Unit::all();
        $subjek = Subjek::all();
        return view('pages.admin.sop.create', compact('units', 'subjek'));
    }

    /**
     * 4. PROSES SIMPAN DATA BARU (PERDANA)
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_sop'  => 'required|string|max:255',
            'nomor_sop' => 'required|string|max:100',
            'link_sop'  => 'required|mimes:pdf|max:10240',
            'id_subjek' => 'required',
            'tahun'     => 'required|numeric',
        ]);

        $path = $request->file('link_sop')->store('uploads/sop', 'public');

        Sop::create([
            'nama_sop'      => $request->nama_sop,
            'nomor_sop'     => $request->nomor_sop,
            'id_subjek'     => $request->id_subjek,
            'id_unit'       => $request->id_unit,
            'revisi_ke'     => '-',
            'link_sop'      => $path,
            'status_active' => 1,
            'tahun'         => $request->tahun . "-01-01 00:00:00",
            'created_date'  => now(),
            'created_by'    => Auth::id(),
        ]);

        return redirect()->route('admin.sop.index')->with('success', 'Data SOP telah berhasil ditambahkan.');
    }

    /**
     * 5. TAMPILKAN FORM EDIT
     */
    public function edit($id)
    {
        $sop = Sop::findOrFail($id);
        $subjek = Subjek::all();
        $units = Unit::all();
        return view('pages.admin.sop.edit', compact('sop', 'subjek', 'units'));
    }

    /**
     * 6. PROSES UPDATE DATA (EDIT)
     */
    public function update(Request $request, $id)
    {
        $sop = Sop::findOrFail($id);

        $request->validate([
            'nama_sop'  => 'required|string|max:255',
            'nomor_sop' => 'required|string|max:100',
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
            'id_unit'       => $request->id_unit,
            'tahun'         => $request->tahun . "-01-01 00:00:00",
            'modified_date' => now(),
            'modify_by'     => Auth::id(),
        ]);

        return redirect()->route('admin.sop.index')->with('success', 'Perubahan data SOP telah berhasil diperbarui.');
    }

    /**
     * 7. PROSES SIMPAN REVISI (LOGIKA ROLLING REVISION MAX 6 DATA)
     */
    public function storeRevisi(Request $request)
    {
        $request->validate([
            'nama_sop'          => 'required|string|max:255',
            'nomor_sop'         => 'required|string|max:100',
            'link_sop'          => 'required|mimes:pdf|max:10240',
            'keterangan_revisi' => 'required|string',
            'sop_terkait'       => 'nullable|array',
            'sop_terkait.*'     => 'exists:tb_sop,id_sop',
        ]);

        DB::transaction(function () use ($request) {
            // 1. Non-aktifkan semua SOP yang dipilih di dynamic select
            if ($request->has('sop_terkait')) {
                // Kita ambil ID unik untuk menghindari error jika user pilih SOP yang sama dua kali
                $idsToDeactivate = array_unique($request->sop_terkait);

                Sop::whereIn('id_sop', $idsToDeactivate)->update([
                    'status_active' => 0,
                    'modified_date' => now(),
                    'modify_by'     => Auth::id(),
                ]);
            }

            // 2. Tentukan nomor revisi berdasarkan NAMA BARU yang diinput
            $lastRev = Sop::where('nama_sop', $request->nama_sop)
                        ->orderBy(DB::raw("CASE WHEN revisi_ke = '-' THEN 0 ELSE CAST(revisi_ke AS UNSIGNED) END"), 'desc')
                        ->first();

            $newRevisionNumber = 1;
            if ($lastRev) {
                $lastNum = ($lastRev->revisi_ke === '-') ? 0 : (int)$lastRev->revisi_ke;
                $newRevisionNumber = $lastNum + 1;
            }

            // 3. Simpan file PDF
            $path = $request->file('link_sop')->store('uploads/sop', 'public');

            // 4. Ambil meta data dari SOP pertama yang dipilih (untuk id_subjek & id_unit)
            $induk = Sop::find($request->sop_terkait[0] ?? null);

            // 5. Buat data SOP Baru (Status Aktif)
            Sop::create([
                'nama_sop'      => $request->nama_sop,
                'nomor_sop'     => $request->nomor_sop,
                'id_subjek'     => $induk ? $induk->id_subjek : 1,
                'id_unit'       => $induk ? $induk->id_unit : 1,
                'tahun'         => now()->format('Y-01-01 00:00:00'),
                'link_sop'      => $path,
                'revisi_ke'     => (string)$newRevisionNumber,
                'status_active' => 1,
                'keterangan'    => $request->keterangan_revisi,
                'created_date'  => now(),
                'created_by'    => Auth::id(),
            ]);

            // 6. Jalankan Rolling Max 6
            $this->applyRollingRevision($request->nama_sop);
        });

        return redirect()->route('admin.sop.index')->with('success', 'SOP Berhasil direvisi. Riwayat lama otomatis dinonaktifkan.');
    }

    // Pindahkan logika rolling ke fungsi private agar rapi
    private function applyRollingRevision($namaSop) {
        $allRevisions = Sop::where('nama_sop', $namaSop)
                            ->where('revisi_ke', '!=', '-')
                            ->orderBy('revisi_ke', 'asc')
                            ->get();

        if ($allRevisions->count() > 6) {
            $toDelete = $allRevisions->take($allRevisions->count() - 6);
            foreach ($toDelete as $old) {
                if ($old->link_sop) Storage::disk('public')->delete($old->link_sop);
                $old->delete();
            }

            // Re-index penomoran
            $remaining = Sop::where('nama_sop', $namaSop)->where('revisi_ke', '!=', '-')->orderBy('revisi_ke', 'asc')->get();
            foreach ($remaining as $index => $item) {
                $item->update(['revisi_ke' => (string)($index + 1)]);
            }
        }
    }

    /**
     * 8. PROSES HAPUS
     */
    public function destroy($id)
    {
        $sop = Sop::findOrFail($id);
        $namaSop = $sop->nama_sop;
        $statusHapus = $sop->status_active;

        if ($sop->link_sop) {
            Storage::disk('public')->delete($sop->link_sop);
        }
        $sop->delete();

        // Jika yang dihapus adalah data aktif, aktifkan versi sebelumnya (jika ada)
        if ($statusHapus == 1) {
            $latest = Sop::where('nama_sop', $namaSop)
                         ->orderBy('id_sop', 'desc')
                         ->first();
            if ($latest) {
                $latest->update(['status_active' => 1]);
            }
        }

        return redirect()->route('admin.sop.index')->with('success', 'Data SOP telah berhasil dihapus.');
    }

    /**
     * 9. AJAX DROPDOWN UNIT
     */
    public function getUnits($id_subjek)
    {
        $units = Unit::where('id_subjek', $id_subjek)->get(['id_unit', 'nama_unit']);
        return response()->json($units);
    }
}
