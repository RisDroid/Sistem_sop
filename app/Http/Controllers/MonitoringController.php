<?php

namespace App\Http\Controllers;

use App\Models\Monitoring;
use App\Models\Sop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MonitoringController extends Controller
{
    public function index()
    {
        // Eager loading sop dan user
        $monitorings = Monitoring::with(['sop', 'user'])->orderBy('id_monitoring', 'desc')->get();
        $sops = Sop::all(); // Untuk dropdown saat tambah monitoring

        return view('pages.admin.monitoring.index', compact('monitorings', 'sops'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_sop' => 'required',
            'kriteria_penilaian' => 'required',
            'hasil_monitoring' => 'required',
        ]);

        Monitoring::create([
            'id_sop' => $request->id_sop,
            'id_user' => Auth::id(),
            'tanggal' => now(),
            'kriteria_penilaian' => $request->kriteria_penilaian,
            'hasil_monitoring' => $request->hasil_monitoring,
            'catatan' => $request->catatan,
        ]);

        return redirect()->back()->with('success', 'Data Monitoring berhasil disimpan!');
    }

    public function destroy($id)
    {
        Monitoring::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Data Monitoring berhasil dihapus!');
    }
}
