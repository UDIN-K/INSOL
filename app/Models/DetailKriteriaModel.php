<?php

namespace App\Models;

use CodeIgniter\Model;

class DetailKriteriaModel extends Model
{
    protected $table = 'detail_kriteria';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = ['kriteria_id', 'sub_kriteria', 'jenis_kondisi', 'batas_bawah', 'batas_atas', 'nilai'];
}
