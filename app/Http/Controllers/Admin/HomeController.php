<?php

namespace App\Http\Controllers\Admin; // Harus ada \Admin

use App\Http\Controllers\Controller; // Wajib diimport karena letaknya beda folder
use Illuminate\Http\Request;
use App\Models\Sop;
use App\Models\Subjek;

class HomeController extends Controller
{
    public function index()
    {
        // Data Grafik
        $subjekData = Subjek::where('status', 'aktif')->get();
        $labels = $subjekData->pluck('nama_subjek')->toArray();
        $dataCounts = [];
        foreach ($subjekData as $s) {
            $dataCounts[] = Sop::where('id_subjek', $s->id_subjek)->count();
        }

        // Data Card Ringkasan
        $totalSop    = Sop::count();
        $totalSubjek = Subjek::count();
        $aman        = Sop::whereYear('tahun', '>=', date('Y'))->count();
        $kritis      = Sop::whereYear('tahun', '<=', date('Y') - 2)->count();
        $review      = Sop::whereYear('tahun', '=', date('Y') - 1)->count();

        return view('pages.admin.dashboard', compact(
            'labels', 'dataCounts', 'totalSop', 'totalSubjek', 'aman', 'kritis', 'review'
        ));
    }
}
