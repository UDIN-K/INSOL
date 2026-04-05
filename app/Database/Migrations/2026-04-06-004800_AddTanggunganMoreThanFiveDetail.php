<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTanggunganMoreThanFiveDetail extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('kriteria') || ! $this->db->tableExists('detail_kriteria')) {
            return;
        }

        $tanggungan = $this->db->table('kriteria')
            ->like('LOWER(kriteria)', 'tanggungan')
            ->get()
            ->getRowArray();

        if ($tanggungan === null) {
            return;
        }

        $kriteriaId = (int) $tanggungan['id'];

        $exists = $this->db
            ->query("SELECT COUNT(*) AS total FROM detail_kriteria WHERE kriteria_id = ? AND REPLACE(LOWER(sub_kriteria), ' ', '') = '>5'", [$kriteriaId])
            ->getRowArray();

        if (((int) ($exists['total'] ?? 0)) === 0) {
            $this->db->table('detail_kriteria')->insert([
                'kriteria_id' => $kriteriaId,
                'sub_kriteria' => '>5',
                'nilai' => 1.0000,
            ]);
        }
    }

    public function down()
    {
        if (! $this->db->tableExists('kriteria') || ! $this->db->tableExists('detail_kriteria')) {
            return;
        }

        $tanggungan = $this->db->table('kriteria')
            ->like('LOWER(kriteria)', 'tanggungan')
            ->get()
            ->getRowArray();

        if ($tanggungan === null) {
            return;
        }

        $kriteriaId = (int) $tanggungan['id'];
        $this->db->query("DELETE FROM detail_kriteria WHERE kriteria_id = ? AND REPLACE(LOWER(sub_kriteria), ' ', '') = '>5'", [$kriteriaId]);
    }
}
