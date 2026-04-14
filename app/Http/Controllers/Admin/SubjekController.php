<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subjek;
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
        // Menggunakan created_date (huruf kecil sesuai database)
        $subjek = Subjek::orderBy('created_date', 'desc')->get();
        return view('pages.admin.subjek.index', compact('subjek'));
    }

    /**
     * AJAX Search untuk Select2
     */
    public function searchSubjek(Request $request)
    {
        $cari = $request->q;
        $data = DB::table('tb_subjek')
                ->select('id_subjek as id', 'nama_subjek as text')
                ->where('nama_subjek', 'LIKE', "%$cari%")
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
            'nama_subjek' => 'required|string|max:255',
        ]);

        Subjek::create([
            'nama_subjek'  => $request->nama_subjek,
            'deskripsi'    => $request->deskripsi,
            'status'       => 'aktif',
            'created_by'   => Auth::id(),
            'created_date' => now(), // Manual karena $timestamps = false di model
        ]);

        return redirect()->route('admin.subjek.index')->with('success', 'Subjek berhasil ditambahkan!');
    }

    /**
     * Update Data
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_subjek' => 'required|string|max:255',
            'status'      => 'required|in:aktif,nonaktif',
        ]);

        $subjek = Subjek::where('id_subjek', $id)->firstOrFail();

        $subjek->update([
            'nama_subjek'   => $request->nama_subjek,
            'deskripsi'     => $request->deskripsi,
            'status'        => $request->status,
            'modified_by'   => Auth::id(),
            'modified_date' => now(),
        ]);

        return redirect()->back()->with('success', 'Subjek berhasil diperbarui!');
    }

    /**
     * Hapus Data
     */
    public function destroy($id)
    {
        $data = Subjek::where('id_subjek', $id)->first();

        if ($data) {
            $data->delete();
            return redirect()->back()->with('success', 'Subjek berhasil dihapus!');
        }

        return redirect()->back()->with('error', 'Data tidak ditemukan!');
    }
}
