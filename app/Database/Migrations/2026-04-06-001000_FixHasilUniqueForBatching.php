<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class FixHasilUniqueForBatching extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('hasil')) {
            return;
        }

        $this->db->query('CREATE INDEX IF NOT EXISTS idx_hasil_mahasiswa_id ON hasil (mahasiswa_id)');

        $indexes = $this->db->query('SHOW INDEX FROM hasil')->getResultArray();
        $hasOldUnique = false;
        $hasNewUnique = false;

        foreach ($indexes as $index) {
            if (($index['Key_name'] ?? '') === 'uq_hasil_mahasiswa') {
                $hasOldUnique = true;
            }

            if (($index['Key_name'] ?? '') === 'uq_hasil_penilaian_mahasiswa') {
                $hasNewUnique = true;
            }
        }

        if ($hasOldUnique) {
            $this->db->query('ALTER TABLE hasil DROP INDEX uq_hasil_mahasiswa');
        }

        if (! $hasNewUnique) {
            $this->db->query('ALTER TABLE hasil ADD UNIQUE KEY uq_hasil_penilaian_mahasiswa (penilaian_ke, mahasiswa_id)');
        }
    }

    public function down()
    {
        if (! $this->db->tableExists('hasil')) {
            return;
        }

        $this->db->query('CREATE INDEX IF NOT EXISTS idx_hasil_mahasiswa_id ON hasil (mahasiswa_id)');

        $indexes = $this->db->query('SHOW INDEX FROM hasil')->getResultArray();
        $hasOldUnique = false;
        $hasNewUnique = false;

        foreach ($indexes as $index) {
            if (($index['Key_name'] ?? '') === 'uq_hasil_mahasiswa') {
                $hasOldUnique = true;
            }

            if (($index['Key_name'] ?? '') === 'uq_hasil_penilaian_mahasiswa') {
                $hasNewUnique = true;
            }
        }

        if ($hasNewUnique) {
            $this->db->query('ALTER TABLE hasil DROP INDEX uq_hasil_penilaian_mahasiswa');
        }

        if (! $hasOldUnique) {
            $this->db->query('ALTER TABLE hasil ADD UNIQUE KEY uq_hasil_mahasiswa (mahasiswa_id)');
        }
    }
}
