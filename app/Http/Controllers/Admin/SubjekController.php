<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subjek;
use App\Models\Timkerja;
use App\Support\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SubjekController extends Controller
{
    /**
     * Menampilkan Halaman Utama Subjek
     */
    public function index()
    {
        $subjek = Subjek::with('timkerja')
            ->orderBy('created_date', 'desc')
            ->get();

        $timkerja = Timkerja::where('status', 'aktif')
            ->orderBy('nama_timkerja', 'asc')
            ->get();

        return view('pages.admin.subjek.index', compact('subjek', 'timkerja'));
    }

    /**
     * AJAX Search untuk Select2
     */
    public function searchSubjek(Request $request)
    {
        $cari = $request->q;

        $data = DB::table('tb_subjek')
            ->select('id_subjek as id', 'nama_subjek as text')
            ->where('nama_subjek', 'LIKE', '%' . $cari . '%')
            ->limit(20)
            ->get();

        return response()->json($data);
    }

    /**
     * Simpan Data Baru
     */
    public function store(Request $request)
    {
        $request->validate([
            'id_timkerja' => 'required|array|min:1',
            'id_timkerja.*' => 'required|exists:tb_timkerja,id_timkerja',
            'nama_subjek' => 'required|string|max:150',
        ]);

        DB::transaction(function () use ($request) {
            foreach (array_unique($request->id_timkerja) as $idTimkerja) {
                $subjek = Subjek::create([
                    'id_timkerja'  => $idTimkerja,
                    'nama_subjek'  => $request->nama_subjek,
                    'deskripsi'    => $request->deskripsi,
                    'status'       => $request->status ?? 'aktif',
                    'created_by'   => Auth::id(),
                    'created_date' => now(),
                ]);

                ActivityLogger::log(
                    'Subjek',
                    'create',
                    'Menambahkan subjek: ' . $subjek->nama_subjek,
                    'Subjek',
                    $subjek->id_subjek,
                    ['timkerja' => $subjek->id_timkerja],
                    $request
                );
            }
        });

        return redirect()->route('admin.subjek.index')
            ->with('success', 'Subjek berhasil ditambahkan ke tim kerja yang dipilih!');
    }

    /**
     * Update Data
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'id_timkerja' => 'required|exists:tb_timkerja,id_timkerja',
            'nama_subjek' => 'required|string|max:150',
            'status'      => 'required|in:aktif,nonaktif',
        ]);

        $subjek = Subjek::where('id_subjek', $id)->firstOrFail();

        $subjek->update([
            'id_timkerja'   => $request->id_timkerja,
            'nama_subjek'   => $request->nama_subjek,
            'deskripsi'     => $request->deskripsi,
            'status'        => $request->status,
            'modified_by'   => Auth::id(),
            'modified_date' => now(),
        ]);

        ActivityLogger::log(
            'Subjek',
            'update',
            'Memperbarui subjek: ' . $subjek->nama_subjek,
            'Subjek',
            $subjek->id_subjek,
            ['timkerja' => $subjek->id_timkerja, 'status' => $subjek->status],
            $request
        );

        return redirect()->back()
            ->with('success', 'Subjek berhasil diperbarui!');
    }

    /**
     * Hapus Data
     */
    public function destroy($id)
    {
        $data = Subjek::where('id_subjek', $id)->first();

        if (!$data) {
            return back()->with('error', 'Data tidak ditemukan!');
        }

        // cek apakah masih dipakai tabel SOP
        if ($data->sop()->count() > 0) {
            return back()->with('error', 'Subjek masih dipakai data SOP!');
        }

        $nama = $data->nama_subjek;
        $data->delete();

        ActivityLogger::log(
            'Subjek',
            'delete',
            'Menghapus subjek: ' . $nama,
            'Subjek',
            $id,
            [],
            $request
        );

        return back()->with('success', 'Subjek berhasil dihapus!');
    }

    public function create()
    {
        return redirect()->route('admin.subjek.index');
    }

    public function show($id)
    {
        return redirect()->route('admin.subjek.index');
    }

    public function edit($id)
    {
        return redirect()->route('admin.subjek.index');
    }
}
