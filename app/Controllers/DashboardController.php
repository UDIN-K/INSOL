<?php

namespace App\Controllers;

use App\Models\HasilModel;
use App\Models\KriteriaModel;
use App\Models\MahasiswaModel;

class DashboardController extends BaseController
{
    public function getIndex(): string
    {
        $hasilModel = new HasilModel();
        $maxPenilaian = $hasilModel->selectMax('penilaian_ke')->first();

        return view('dashboard/index', [
            'totalMahasiswa' => (new MahasiswaModel())->countAllResults(),
            'totalKriteria' => (new KriteriaModel())->countAllResults(),
            'totalLolos' => $hasilModel->where('status_lolos', 'Lolos')->countAllResults(),
            'totalPenilaian' => (int) ($maxPenilaian['penilaian_ke'] ?? 0),
        ]);
    }
}
