<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call('MahasiswaSeeder');
        // PenilaianSeeder tidak perlu - nilai penilaian diisi saat user input via form
    }
}
