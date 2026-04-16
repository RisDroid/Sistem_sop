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
        $monitorings = Monitoring::with(['sop', 'user'])->orderBy('id_monitoring', 'desc')->get();
        $sops = Sop::where('status', 'aktif')->orderBy('nama_sop')->get();

        return view('pages.monitoring.index', compact('monitorings', 'sops'));
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

    public function create()
    {
        return redirect()->route('admin.monitoring.index');
    }

    public function show($id)
    {
        return redirect()->route('admin.monitoring.index');
    }

    public function edit($id)
    {
        return redirect()->route('admin.monitoring.index');
    }

    public function update(Request $request, $id)
    {
        return redirect()->route('admin.monitoring.index');
    }
}
