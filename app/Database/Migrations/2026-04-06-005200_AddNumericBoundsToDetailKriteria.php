<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddNumericBoundsToDetailKriteria extends Migration
{
    private function toFloat(string $value): float
    {
        return (float) str_replace(',', '.', $value);
    }

    private function parseLabel(string $label): array
    {
        $clean = str_replace(' ', '', trim($label));

        if (preg_match('/^(-?\d+(?:[\.,]\d+)?)-(-?\d+(?:[\.,]\d+)?)$/', $clean, $m) === 1) {
            return ['jenis_kondisi' => 'range', 'batas_bawah' => $this->toFloat($m[1]), 'batas_atas' => $this->toFloat($m[2])];
        }

        if (preg_match('/^>(-?\d+(?:[\.,]\d+)?)$/', $clean, $m) === 1) {
            return ['jenis_kondisi' => 'gt', 'batas_bawah' => $this->toFloat($m[1]), 'batas_atas' => null];
        }

        if (preg_match('/^>=(-?\d+(?:[\.,]\d+)?)$/', $clean, $m) === 1) {
            return ['jenis_kondisi' => 'gte', 'batas_bawah' => $this->toFloat($m[1]), 'batas_atas' => null];
        }

        if (preg_match('/^<(-?\d+(?:[\.,]\d+)?)$/', $clean, $m) === 1) {
            return ['jenis_kondisi' => 'lt', 'batas_bawah' => $this->toFloat($m[1]), 'batas_atas' => null];
        }

        if (preg_match('/^<=(-?\d+(?:[\.,]\d+)?)$/', $clean, $m) === 1) {
            return ['jenis_kondisi' => 'lte', 'batas_bawah' => $this->toFloat($m[1]), 'batas_atas' => null];
        }

        if (preg_match('/^-?\d+(?:[\.,]\d+)?$/', $clean) === 1) {
            return ['jenis_kondisi' => 'eq', 'batas_bawah' => $this->toFloat($clean), 'batas_atas' => null];
        }

        return ['jenis_kondisi' => 'text', 'batas_bawah' => null, 'batas_atas' => null];
    }

    public function up()
    {
        if (! $this->db->tableExists('detail_kriteria')) {
            return;
        }

        $newColumns = [];

        if (! $this->db->fieldExists('jenis_kondisi', 'detail_kriteria')) {
            $newColumns['jenis_kondisi'] = [
                'type' => "ENUM('text','eq','range','gt','gte','lt','lte')",
                'null' => false,
                'default' => 'text',
                'after' => 'sub_kriteria',
            ];
        }

        if (! $this->db->fieldExists('batas_bawah', 'detail_kriteria')) {
            $newColumns['batas_bawah'] = [
                'type' => 'DECIMAL',
                'constraint' => '12,4',
                'null' => true,
                'after' => 'jenis_kondisi',
            ];
        }

        if (! $this->db->fieldExists('batas_atas', 'detail_kriteria')) {
            $newColumns['batas_atas'] = [
                'type' => 'DECIMAL',
                'constraint' => '12,4',
                'null' => true,
                'after' => 'batas_bawah',
            ];
        }

        if (! empty($newColumns)) {
            $this->forge->addColumn('detail_kriteria', $newColumns);
        }

        $rows = $this->db->table('detail_kriteria')->get()->getResultArray();
        foreach ($rows as $row) {
            $parsed = $this->parseLabel((string) ($row['sub_kriteria'] ?? ''));
            $this->db->table('detail_kriteria')
                ->where('id', $row['id'])
                ->update([
                    'jenis_kondisi' => $parsed['jenis_kondisi'],
                    'batas_bawah' => $parsed['batas_bawah'],
                    'batas_atas' => $parsed['batas_atas'],
                ]);
        }
    }

    public function down()
    {
        if (! $this->db->tableExists('detail_kriteria')) {
            return;
        }

        if ($this->db->fieldExists('batas_atas', 'detail_kriteria')) {
            $this->forge->dropColumn('detail_kriteria', 'batas_atas');
        }

        if ($this->db->fieldExists('batas_bawah', 'detail_kriteria')) {
            $this->forge->dropColumn('detail_kriteria', 'batas_bawah');
        }

        if ($this->db->fieldExists('jenis_kondisi', 'detail_kriteria')) {
            $this->forge->dropColumn('detail_kriteria', 'jenis_kondisi');
        }
    }
}
