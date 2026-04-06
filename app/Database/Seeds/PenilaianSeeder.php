<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use App\Models\MahasiswaModel;
use App\Models\KriteriaModel;
use App\Models\DetailKriteriaModel;

class PenilaianSeeder extends Seeder
{
    public function run()
    {
        $mahasiswaModel = new MahasiswaModel();
        $kriteriaModel = new KriteriaModel();
        $detailKriteriaModel = new DetailKriteriaModel();

        // Get all mahasiswa
        $mahasiswa = $mahasiswaModel->findAll();
        $kriteria = $kriteriaModel->findAll();

        $penilaianData = [];

        foreach ($mahasiswa as $m) {
            foreach ($kriteria as $k) {
                $nilai = $this->calculateNilai(
                    $k['id'],
                    $k['atribut'],
                    (float) $m['ipk'],
                    (int) $m['penghasilan_ortu'],
                    (int) $m['jumlah_tanggungan'],
                    $m['prestasi_non_akademik'],
                    $detailKriteriaModel
                );

                $penilaianData[] = [
                    'mahasiswa_id' => $m['id'],
                    'kriteria_id' => $k['id'],
                    'penilaian_ke' => 1,
                    'nilai' => $nilai,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ];
            }
        }

        // Insert semua penilaian sekaligus
        if (!empty($penilaianData)) {
            $this->db->table('penilaian')->insertBatch($penilaianData);
        }
    }

    /**
     * Calculate nilai berdasarkan kriteria dan value mahasiswa
     */
    private function calculateNilai(
        int $kriteriaId,
        string $atribut,
        float $ipk,
        int $penghasilan,
        int $tanggungan,
        string $prestasi,
        $detailModel
    ): float {
        $details = $detailModel->where('kriteria_id', $kriteriaId)->findAll();

        if (empty($details)) {
            return 0;
        }

        // Calculate berdasarkan kriteria ID
        if ($kriteriaId == 1) {
            // IPK (Benefit) - range
            return $this->findNilaiByCriteria($details, $ipk, 'range');
        } elseif ($kriteriaId == 2) {
            // Penghasilan (Cost) - range
            return $this->findNilaiByCriteria($details, $penghasilan, 'range');
        } elseif ($kriteriaId == 3) {
            // Tanggungan (Cost) - exact match
            return $this->findNilaiByCriteria($details, $tanggungan, 'exact');
        } elseif ($kriteriaId == 4) {
            // Prestasi (Benefit) - text match
            return $this->findNilaiByPrestasi($details, $prestasi);
        }

        return 0;
    }

    /**
     * Find nilai by range atau exact match
     */
    private function findNilaiByCriteria(array $details, float|int $value, string $type): float
    {
        foreach ($details as $detail) {
            if ($type === 'range') {
                $batasBawah = (float) $detail['batas_bawah'];
                $batasAtas = (float) $detail['batas_atas'];
                if ($value >= $batasBawah && $value <= $batasAtas) {
                    return (float) $detail['nilai'];
                }
            } elseif ($type === 'exact') {
                $batasan = (int) $detail['batas_bawah'];
                $jenis = trim($detail['jenis_kondisi'] ?? '');

                if ($jenis === 'gt' && (int) $value > $batasan) {
                    return (float) $detail['nilai'];
                } elseif ($jenis === 'eq' && (int) $value === $batasan) {
                    return (float) $detail['nilai'];
                }
            }
        }

        return 0;
    }

    /**
     * Find nilai by prestasi
     */
    private function findNilaiByPrestasi(array $details, string $prestasi): float
    {
        $prestasi = strtolower(trim($prestasi));

        foreach ($details as $detail) {
            if (strpos(strtolower($detail['sub_kriteria']), $prestasi) !== false) {
                return (float) $detail['nilai'];
            }
        }

        // Fallback
        foreach ($details as $detail) {
            if (strpos(strtolower($detail['sub_kriteria']), 'tidak') !== false) {
                return (float) $detail['nilai'];
            }
        }

        return 0;
    }
}
