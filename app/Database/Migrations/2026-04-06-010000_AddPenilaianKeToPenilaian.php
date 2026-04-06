<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPenilaianKeToPenilaian extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('penilaian')) {
            return;
        }

        if (! $this->db->fieldExists('penilaian_ke', 'penilaian')) {
            $this->forge->addColumn('penilaian', [
                'penilaian_ke' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'default' => 1,
                    'after' => 'mahasiswa_id',
                ],
            ]);

            // Add index untuk query performa
            $this->db->query('CREATE INDEX idx_penilaian_penilaian_ke ON penilaian (penilaian_ke)');
            
            // Add composite unique index untuk mencegah duplikasi
            $this->db->query('CREATE UNIQUE INDEX uq_penilaian_batch ON penilaian (mahasiswa_id, kriteria_id, penilaian_ke)');
        }
    }

    public function down()
    {
        if (! $this->db->tableExists('penilaian')) {
            return;
        }

        if ($this->db->fieldExists('penilaian_ke', 'penilaian')) {
            // Drop indexes dulu
            $indexNames = array_column($this->db->query('SHOW INDEX FROM penilaian')->getResultArray(), 'Key_name');
            
            if (in_array('uq_penilaian_batch', $indexNames, true)) {
                $this->db->query('ALTER TABLE penilaian DROP INDEX uq_penilaian_batch');
            }
            
            if (in_array('idx_penilaian_penilaian_ke', $indexNames, true)) {
                $this->db->query('ALTER TABLE penilaian DROP INDEX idx_penilaian_penilaian_ke');
            }
            
            // Drop column
            $this->forge->dropColumn('penilaian', 'penilaian_ke');
        }
    }
}
