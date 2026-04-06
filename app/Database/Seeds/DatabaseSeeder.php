<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call('MahasiswaSeeder');
        // Penilaian dihitung otomatis saat user input data mahasiswa via form
        // Bukan di-seed, tapi langsung di-calculate dan disimpan ke penilaian table
    }
}
