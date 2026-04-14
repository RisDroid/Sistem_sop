<?php

namespace App\Services;

use Carbon\Carbon;

class SopMonitoringService
{
    /**
     * Logika Inti: Cek Status berdasarkan selisih tahun
     */
    public function cekStatusSOP($tanggal)
    {
        $tahunSop = Carbon::parse($tanggal)->year;
        $tahunSekarang = Carbon::now()->year;
        $selisih = $tahunSekarang - $tahunSop;

        if ($selisih == 0) {
            return [
                'status' => 'Aman',
                'badge' => 'success',
                'pesan' => 'SOP masih berlaku dan relevan.'
            ];
        } elseif ($selisih == 1) {
            return [
                'status' => 'Perlu Review',
                'badge' => 'warning',
                'pesan' => 'SOP sudah memasuki masa review (1 tahun).'
            ];
        } else {
            return [
                'status' => 'Kritis',
                'badge' => 'danger',
                'pesan' => 'SOP kadaluwarsa, segera lakukan revisi!'
            ];
        }
    }
}
