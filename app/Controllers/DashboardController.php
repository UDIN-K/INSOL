<?php

namespace App\Controllers;

use App\Models\HasilModel;
use App\Models\KriteriaModel;
use App\Models\MahasiswaModel;

class DashboardController extends BaseController
{
    public function getIndex(): string
    {
        return view('dashboard/index', [
            'totalMahasiswa' => (new MahasiswaModel())->countAllResults(),
            'totalKriteria' => (new KriteriaModel())->countAllResults(),
            'totalLolos' => (new HasilModel())->where('status_lolos', 'Lolos')->countAllResults(),
        ]);
    }
}
