<?php

namespace App\Models;

use CodeIgniter\Model;

class MahasiswaModel extends Model
{
    protected $table = 'mahasiswa';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'nim',
        'nama',
        'jenis_kelamin',
        'tempat_lahir',
        'tanggal_lahir',
        'alamat',
        'telepon',
        'email',
        'prodi',
        'semester',
        'tahun_masuk',
        'nama_ibu',
        'nama_bapak',
        'ipk',
        'penghasilan_ortu',
        'jumlah_tanggungan',
        'prestasi_non_akademik',
    ];
}
