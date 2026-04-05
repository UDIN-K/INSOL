<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class SeedTemplateDetailKriteria extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('kriteria') || ! $this->db->tableExists('detail_kriteria')) {
            return;
        }

        $kriteriaRows = $this->db->table('kriteria')->get()->getResultArray();

        $map = [
            'ipk' => null,
            'penghasilan' => null,
            'tanggungan' => null,
            'prestasi' => null,
        ];

        foreach ($kriteriaRows as $row) {
            $nama = strtolower((string) ($row['kriteria'] ?? ''));
            if (str_contains($nama, 'ipk')) {
                $map['ipk'] = $row;
            } elseif (str_contains($nama, 'penghasilan')) {
                $map['penghasilan'] = $row;
            } elseif (str_contains($nama, 'tanggungan')) {
                $map['tanggungan'] = $row;
            } elseif (str_contains($nama, 'prestasi')) {
                $map['prestasi'] = $row;
            }
        }

        foreach ($map as $item) {
            if ($item !== null) {
                $this->db->table('detail_kriteria')->where('kriteria_id', $item['id'])->delete();
            }
        }

        if ($map['penghasilan'] !== null) {
            $this->db->table('kriteria')->where('id', $map['penghasilan']['id'])->update(['atribut' => 'cost']);
        }

        foreach (['ipk', 'tanggungan', 'prestasi'] as $benefitKey) {
            if ($map[$benefitKey] !== null) {
                $this->db->table('kriteria')->where('id', $map[$benefitKey]['id'])->update(['atribut' => 'benefit']);
            }
        }

        $insertRows = [];

        if ($map['ipk'] !== null) {
            $kid = (int) $map['ipk']['id'];
            $insertRows[] = ['kriteria_id' => $kid, 'sub_kriteria' => '0-2.50', 'nilai' => 0.1000];
            $insertRows[] = ['kriteria_id' => $kid, 'sub_kriteria' => '2.50-3.00', 'nilai' => 0.5000];
            $insertRows[] = ['kriteria_id' => $kid, 'sub_kriteria' => '3.01-3.50', 'nilai' => 0.7500];
            $insertRows[] = ['kriteria_id' => $kid, 'sub_kriteria' => '3.51-4.00', 'nilai' => 1.0000];
        }

        if ($map['penghasilan'] !== null) {
            $kid = (int) $map['penghasilan']['id'];
            $insertRows[] = ['kriteria_id' => $kid, 'sub_kriteria' => '0-500000', 'nilai' => 0.2500];
            $insertRows[] = ['kriteria_id' => $kid, 'sub_kriteria' => '500000-1500000', 'nilai' => 0.5000];
            $insertRows[] = ['kriteria_id' => $kid, 'sub_kriteria' => '1500000-3000000', 'nilai' => 0.7500];
            $insertRows[] = ['kriteria_id' => $kid, 'sub_kriteria' => '3000000-10000000', 'nilai' => 1.0000];
        }

        if ($map['tanggungan'] !== null) {
            $kid = (int) $map['tanggungan']['id'];
            $insertRows[] = ['kriteria_id' => $kid, 'sub_kriteria' => '1', 'nilai' => 0.1000];
            $insertRows[] = ['kriteria_id' => $kid, 'sub_kriteria' => '2', 'nilai' => 0.2500];
            $insertRows[] = ['kriteria_id' => $kid, 'sub_kriteria' => '3', 'nilai' => 0.5000];
            $insertRows[] = ['kriteria_id' => $kid, 'sub_kriteria' => '4', 'nilai' => 0.7500];
        }

        if ($map['prestasi'] !== null) {
            $kid = (int) $map['prestasi']['id'];
            $insertRows[] = ['kriteria_id' => $kid, 'sub_kriteria' => 'Tidak berprestasi', 'nilai' => 0.2500];
            $insertRows[] = ['kriteria_id' => $kid, 'sub_kriteria' => 'Berprestasi tingkat universitas', 'nilai' => 0.5000];
            $insertRows[] = ['kriteria_id' => $kid, 'sub_kriteria' => 'Berprestasi tingkat Provinsi', 'nilai' => 0.7500];
            $insertRows[] = ['kriteria_id' => $kid, 'sub_kriteria' => 'Berprestasi tingkat Nasional', 'nilai' => 1.0000];
        }

        if (! empty($insertRows)) {
            $this->db->table('detail_kriteria')->insertBatch($insertRows);
        }
    }

    public function down()
    {
        if (! $this->db->tableExists('kriteria') || ! $this->db->tableExists('detail_kriteria')) {
            return;
        }

        $kriteriaRows = $this->db->table('kriteria')->get()->getResultArray();
        foreach ($kriteriaRows as $row) {
            $nama = strtolower((string) ($row['kriteria'] ?? ''));
            if (
                str_contains($nama, 'ipk')
                || str_contains($nama, 'penghasilan')
                || str_contains($nama, 'tanggungan')
                || str_contains($nama, 'prestasi')
            ) {
                $this->db->table('detail_kriteria')->where('kriteria_id', $row['id'])->delete();
            }
        }
    }
}
