<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subjek;
use App\Models\Sop;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // 1. Ambil subjek aktif sekaligus HITUNG jumlah SOP terkait (Otomatis & Efisien)
        // withCount('sops') akan membuat atribut baru bernama 'sops_count' pada tiap objek subjek
        $subjekData = Subjek::where('status', 'aktif')
                            ->withCount('sops')
                            ->get();

        // 2. Siapkan Label untuk Chart (Nama-nama Subjek)
        $labels = $subjekData->pluck('nama_subjek')->toArray();

        // 3. Siapkan Data untuk Grafik (Jumlah SOP per Subjek)
        // Kita ambil dari atribut 'sops_count' hasil dari withCount tadi
        $dataCounts = $subjekData->pluck('sops_count')->toArray();

        // 4. Hitung total ringkasan untuk card dashboard
        $totalSubjek = $subjekData->count();
        $totalSop = Sop::count();

        // Tambahan: Ambil data subjek lengkap untuk dikirim ke view (jika card katalog ada di halaman ini)
        $subjek = $subjekData;

        return view('pages.admin.dashboard', compact(
            'labels',
            'dataCounts',
            'totalSubjek',
            'totalSop',
            'subjek'
        ));
    }
}
