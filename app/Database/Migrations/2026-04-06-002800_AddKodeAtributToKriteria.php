<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddKodeAtributToKriteria extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('kriteria')) {
            return;
        }

        $newColumns = [];

        if (! $this->db->fieldExists('kode', 'kriteria')) {
            $newColumns['kode'] = [
                'type' => 'VARCHAR',
                'constraint' => 10,
                'null' => true,
                'after' => 'id',
            ];
        }

        if (! $this->db->fieldExists('atribut', 'kriteria')) {
            $newColumns['atribut'] = [
                'type' => "ENUM('benefit','cost')",
                'null' => false,
                'default' => 'benefit',
                'after' => 'bobot',
            ];
        }

        if (! empty($newColumns)) {
            $this->forge->addColumn('kriteria', $newColumns);
        }

        $rows = $this->db->table('kriteria')->orderBy('id', 'ASC')->get()->getResultArray();
        foreach ($rows as $i => $row) {
            $kode = strtoupper((string) ($row['kode'] ?? ''));
            if ($kode === '') {
                $kode = 'C' . ($i + 1);
            }

            $this->db->table('kriteria')
                ->where('id', $row['id'])
                ->update([
                    'kode' => $kode,
                    'atribut' => ($row['atribut'] ?? 'benefit') ?: 'benefit',
                ]);
        }

        $indexNames = array_column($this->db->query('SHOW INDEX FROM kriteria')->getResultArray(), 'Key_name');
        if (! in_array('uq_kriteria_kode', $indexNames, true)) {
            $this->db->query('ALTER TABLE kriteria ADD UNIQUE KEY uq_kriteria_kode (kode)');
        }

        $this->db->query('ALTER TABLE kriteria MODIFY kode VARCHAR(10) NOT NULL');
    }

    public function down()
    {
        if (! $this->db->tableExists('kriteria')) {
            return;
        }

        $indexNames = array_column($this->db->query('SHOW INDEX FROM kriteria')->getResultArray(), 'Key_name');
        if (in_array('uq_kriteria_kode', $indexNames, true)) {
            $this->db->query('ALTER TABLE kriteria DROP INDEX uq_kriteria_kode');
        }

        if ($this->db->fieldExists('kode', 'kriteria')) {
            $this->forge->dropColumn('kriteria', 'kode');
        }

        if ($this->db->fieldExists('atribut', 'kriteria')) {
            $this->forge->dropColumn('kriteria', 'atribut');
        }
    }
}
