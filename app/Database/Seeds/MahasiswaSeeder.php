<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class MahasiswaSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'nim' => '2020001',
                'nama' => 'Budi Santoso',
                'jenis_kelamin' => 'Laki-laki',
                'tempat_lahir' => 'Jakarta',
                'tanggal_lahir' => '2002-05-15',
                'alamat' => 'Jl. Merdeka No. 123, Jakarta Pusat',
                'telepon' => '081234567890',
                'email' => 'budi.santoso@email.com',
                'prodi' => 'Teknik Informatika',
                'semester' => 8,
                'tahun_masuk' => 2020,
                'nama_ibu' => 'Siti Nurhaliza',
                'nama_bapak' => 'Ahmad Santoso',
                'ipk' => 3.75,
                'penghasilan_ortu' => 450000,
                'jumlah_tanggungan' => 4,
                'prestasi_non_akademik' => 'nasional',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'nim' => '2020002',
                'nama' => 'Ani Wijaya',
                'jenis_kelamin' => 'Perempuan',
                'tempat_lahir' => 'Bandung',
                'tanggal_lahir' => '2002-08-22',
                'alamat' => 'Jl. Sudirman No. 456, Bandung',
                'telepon' => '082345678901',
                'email' => 'ani.wijaya@email.com',
                'prodi' => 'Sistem Informasi',
                'semester' => 8,
                'tahun_masuk' => 2020,
                'nama_ibu' => 'Rina Wijaya',
                'nama_bapak' => 'Budi Wijaya',
                'ipk' => 3.50,
                'penghasilan_ortu' => 1000000,
                'jumlah_tanggungan' => 3,
                'prestasi_non_akademik' => 'provinsi',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'nim' => '2020003',
                'nama' => 'Citra Dewi',
                'jenis_kelamin' => 'Perempuan',
                'tempat_lahir' => 'Yogyakarta',
                'tanggal_lahir' => '2002-03-10',
                'alamat' => 'Jl. Malioboro No. 789, Yogyakarta',
                'telepon' => '083456789012',
                'email' => 'citra.dewi@email.com',
                'prodi' => 'Teknik Informatika',
                'semester' => 8,
                'tahun_masuk' => 2020,
                'nama_ibu' => 'Maya Putri',
                'nama_bapak' => 'Dewi Rahman',
                'ipk' => 3.20,
                'penghasilan_ortu' => 1500000,
                'jumlah_tanggungan' => 2,
                'prestasi_non_akademik' => 'universitas',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'nim' => '2020004',
                'nama' => 'Deni Hermawan',
                'jenis_kelamin' => 'Laki-laki',
                'tempat_lahir' => 'Surabaya',
                'tanggal_lahir' => '2001-12-05',
                'alamat' => 'Jl. Tunjungan No. 321, Surabaya',
                'telepon' => '084567890123',
                'email' => 'deni.hermawan@email.com',
                'prodi' => 'Teknik Komputer',
                'semester' => 8,
                'tahun_masuk' => 2020,
                'nama_ibu' => 'Sinta Hermawan',
                'nama_bapak' => 'Hermawan Gunadi',
                'ipk' => 3.65,
                'penghasilan_ortu' => 800000,
                'jumlah_tanggungan' => 5,
                'prestasi_non_akademik' => 'kota',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'nim' => '2020005',
                'nama' => 'Eka Sari',
                'jenis_kelamin' => 'Perempuan',
                'tempat_lahir' => 'Medan',
                'tanggal_lahir' => '2002-07-18',
                'alamat' => 'Jl. Ahmad Yani No. 654, Medan',
                'telepon' => '085678901234',
                'email' => 'eka.sari@email.com',
                'prodi' => 'Sistem Informasi',
                'semester' => 8,
                'tahun_masuk' => 2020,
                'nama_ibu' => 'Retno Sari',
                'nama_bapak' => 'Sari Wijaya',
                'ipk' => 2.95,
                'penghasilan_ortu' => 3500000,
                'jumlah_tanggungan' => 1,
                'prestasi_non_akademik' => 'universitas',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];

        // Uncomment the lines below to disable all mass inserts and re-enable query builder insert.
        // foreach ($data as $row)
        // {
        //    $this->db->table('mahasiswa')->insert($row);
        // }

        $this->db->table('mahasiswa')->insertBatch($data);
    }
}
