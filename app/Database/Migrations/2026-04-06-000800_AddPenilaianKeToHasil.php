<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPenilaianKeToHasil extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('hasil')) {
            return;
        }

        if (! $this->db->fieldExists('penilaian_ke', 'hasil')) {
            $this->forge->addColumn('hasil', [
                'penilaian_ke' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'default' => 1,
                    'after' => 'mahasiswa_id',
                ],
            ]);

            $this->db->query('CREATE INDEX idx_hasil_penilaian_ke ON hasil (penilaian_ke)');
        }
    }

    public function down()
    {
        if (! $this->db->tableExists('hasil')) {
            return;
        }

        if ($this->db->fieldExists('penilaian_ke', 'hasil')) {
            $this->forge->dropColumn('hasil', 'penilaian_ke');
        }
    }
}
