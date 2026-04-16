<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subjek;
use App\Models\Timkerja;
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
            'id_timkerja' => 'required|exists:tb_timkerja,id_timkerja',
            'nama_subjek' => 'required|string|max:150',
        ]);
        Subjek::create([
            'id_timkerja'  => $request->id_timkerja,
            'nama_subjek'  => $request->nama_subjek,
            'deskripsi'    => $request->deskripsi,
            'status'       => 'aktif',
            'created_by'   => Auth::user()->name ?? 'system',
            'created_date' => now(),
        ]);

        return redirect()->route('admin.subjek.index')
            ->with('success', 'Subjek berhasil ditambahkan!');
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
            'modified_by'   => Auth::user()->name ?? 'system',
            'modified_date' => now(),
        ]);

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

        $data->delete();

        return back()->with('success', 'Subjek berhasil dihapus!');
    }
}
