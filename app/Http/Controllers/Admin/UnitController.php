<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\timkerja;
use App\Models\Subjek;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UnitController extends Controller
{
    public function index()
    {
        $units = Timkerja::with('subjek')->get();
        $subjeks = Subjek::all();
        return view('pages.admin.unit.index', compact('units', 'subjeks'));
    }

    public function store(Request $request)
    {
        Timkerja::create([
            'id_subjek' => $request->id_subjek,
            'nama_unit' => $request->nama_unit,
            'status' => 'aktif',
            'created_by' => Auth::user()->id
        ]);

        return redirect()->route('admin.unit.index')->with('success', 'Data Berhasil Disimpan');
    }

    public function destroy($id)
    {
        Timkerja::destroy($id);
        return back()->with('success', 'Data Berhasil Dihapus');
    }
}
