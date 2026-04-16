<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Timkerja;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TimkerjaController extends Controller
{
    /**
     * Halaman utama Tim Kerja
     */
    public function index()
    {
        $timkerja = Timkerja::with('subjek')
            ->orderBy('created_date', 'desc')
            ->get();

        return view('pages.admin.timkerja.index', compact('timkerja'));
    }

    /**
     * Simpan data baru
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_timkerja' => 'required|unique:tb_timkerja,nama_timkerja',
        ]);

        Timkerja::create([
            'nama_timkerja' => $request->nama_timkerja,
            'deskripsi'     => $request->deskripsi,
            'status'        => 'aktif',
            'created_by'    => Auth::user()->name ?? 'system',
            'created_date'  => now(),
        ]);

        return redirect()
            ->route('admin.timkerja.index')
            ->with('success', 'Tim Kerja berhasil ditambahkan!');
    }

    /**
     * Update data
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_timkerja' => 'required|string|max:150',
            'status'        => 'required|in:aktif,nonaktif',
        ]);

        $timkerja = Timkerja::where('id_timkerja', $id)->firstOrFail();

        $timkerja->update([
            'nama_timkerja' => $request->nama_timkerja,
            'deskripsi'     => $request->deskripsi,
            'status'        => $request->status,
            'modified_by'   => Auth::user()->name ?? 'system',
            'modified_date' => now(),
        ]);

        return back()->with('success', 'Tim Kerja berhasil diperbarui!');
    }

    /**
     * Hapus data
     */
    public function destroy($id)
    {
        $data = Timkerja::find($id);

        if (!$data) {
            return back()->with('error', 'Data tidak ditemukan!');
        }

        if ($data->subjek()->count() > 0) {
            return back()->with('error', 'Tim Kerja masih digunakan data subjek!');
        }

        $data->delete();

        return back()->with('success', 'Tim Kerja berhasil dihapus!');
    }
}
