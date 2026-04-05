<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdateKriteriaDetailDanMahasiswa extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('kriteria')) {
            if ($this->db->fieldExists('nama', 'kriteria') && ! $this->db->fieldExists('kriteria', 'kriteria')) {
                $this->forge->modifyColumn('kriteria', [
                    'nama' => [
                        'name'       => 'kriteria',
                        'type'       => 'VARCHAR',
                        'constraint' => 100,
                        'null'       => false,
                    ],
                ]);
            }

            if ($this->db->fieldExists('kode', 'kriteria')) {
                $this->db->query('ALTER TABLE kriteria DROP COLUMN kode');
            }

            if ($this->db->fieldExists('atribut', 'kriteria')) {
                $this->db->query('ALTER TABLE kriteria DROP COLUMN atribut');
            }

            $kriteriaCount = $this->db->table('kriteria')->countAllResults();
            if ($kriteriaCount === 0) {
                $this->db->table('kriteria')->insertBatch([
                    ['kriteria' => 'IPK', 'bobot' => 0.2500],
                    ['kriteria' => 'Penghasilan Orang Tua', 'bobot' => 0.2500],
                    ['kriteria' => 'Jumlah Tanggungan', 'bobot' => 0.2500],
                    ['kriteria' => 'Prestasi Non Akademik', 'bobot' => 0.2500],
                ]);
            }
        }

        if (! $this->db->tableExists('detail_kriteria')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 10,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'kriteria_id' => [
                    'type'       => 'INT',
                    'constraint' => 10,
                    'unsigned'   => true,
                ],
                'sub_kriteria' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 150,
                ],
                'nilai' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '8,4',
                    'default'    => 0,
                ],
                'created_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'updated_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addForeignKey('kriteria_id', 'kriteria', 'id', 'CASCADE', 'CASCADE');
            $this->forge->createTable('detail_kriteria');

            $prestasi = $this->db->table('kriteria')
                ->where('LOWER(kriteria)', 'prestasi non akademik')
                ->get()
                ->getRowArray();

            if ($prestasi !== null) {
                $this->db->table('detail_kriteria')->insertBatch([
                    ['kriteria_id' => $prestasi['id'], 'sub_kriteria' => 'Universitas', 'nilai' => 0.2000],
                    ['kriteria_id' => $prestasi['id'], 'sub_kriteria' => 'Kota', 'nilai' => 0.4000],
                    ['kriteria_id' => $prestasi['id'], 'sub_kriteria' => 'Provinsi', 'nilai' => 0.6000],
                    ['kriteria_id' => $prestasi['id'], 'sub_kriteria' => 'Nasional', 'nilai' => 0.8000],
                    ['kriteria_id' => $prestasi['id'], 'sub_kriteria' => 'Internasional', 'nilai' => 1.0000],
                ]);
            }
        }

        if ($this->db->tableExists('mahasiswa')) {
            $newColumns = [];

            if (! $this->db->fieldExists('jenis_kelamin', 'mahasiswa')) {
                $newColumns['jenis_kelamin'] = ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true, 'after' => 'nama'];
            }
            if (! $this->db->fieldExists('tempat_lahir', 'mahasiswa')) {
                $newColumns['tempat_lahir'] = ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true, 'after' => 'jenis_kelamin'];
            }
            if (! $this->db->fieldExists('tanggal_lahir', 'mahasiswa')) {
                $newColumns['tanggal_lahir'] = ['type' => 'DATE', 'null' => true, 'after' => 'tempat_lahir'];
            }
            if (! $this->db->fieldExists('alamat', 'mahasiswa')) {
                $newColumns['alamat'] = ['type' => 'TEXT', 'null' => true, 'after' => 'tanggal_lahir'];
            }
            if (! $this->db->fieldExists('telepon', 'mahasiswa')) {
                $newColumns['telepon'] = ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true, 'after' => 'alamat'];
            }
            if (! $this->db->fieldExists('email', 'mahasiswa')) {
                $newColumns['email'] = ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true, 'after' => 'telepon'];
            }
            if (! $this->db->fieldExists('prodi', 'mahasiswa')) {
                $newColumns['prodi'] = ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true, 'after' => 'email'];
            }
            if (! $this->db->fieldExists('semester', 'mahasiswa')) {
                $newColumns['semester'] = ['type' => 'INT', 'constraint' => 11, 'null' => true, 'after' => 'prodi'];
            }
            if (! $this->db->fieldExists('tahun_masuk', 'mahasiswa')) {
                $newColumns['tahun_masuk'] = ['type' => 'INT', 'constraint' => 11, 'null' => true, 'after' => 'semester'];
            }
            if (! $this->db->fieldExists('nama_ibu', 'mahasiswa')) {
                $newColumns['nama_ibu'] = ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true, 'after' => 'tahun_masuk'];
            }
            if (! $this->db->fieldExists('nama_bapak', 'mahasiswa')) {
                $newColumns['nama_bapak'] = ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true, 'after' => 'nama_ibu'];
            }
            if (! $this->db->fieldExists('ipk', 'mahasiswa')) {
                $newColumns['ipk'] = ['type' => 'DECIMAL', 'constraint' => '4,2', 'null' => true, 'after' => 'nama_bapak'];
            }
            if (! $this->db->fieldExists('penghasilan_ortu', 'mahasiswa')) {
                $newColumns['penghasilan_ortu'] = ['type' => 'BIGINT', 'null' => true, 'after' => 'ipk'];
            }
            if (! $this->db->fieldExists('jumlah_tanggungan', 'mahasiswa')) {
                $newColumns['jumlah_tanggungan'] = ['type' => 'INT', 'constraint' => 11, 'null' => true, 'after' => 'penghasilan_ortu'];
            }
            if (! $this->db->fieldExists('prestasi_non_akademik', 'mahasiswa')) {
                $newColumns['prestasi_non_akademik'] = [
                    'type' => "ENUM('universitas','kota','provinsi','nasional','internasional')",
                    'null' => true,
                    'after' => 'jumlah_tanggungan',
                ];
            }

            if (! empty($newColumns)) {
                $this->forge->addColumn('mahasiswa', $newColumns);
            }
        }
    }

    public function down()
    {
        if ($this->db->tableExists('detail_kriteria')) {
            $this->forge->dropTable('detail_kriteria', true);
        }

        if ($this->db->tableExists('kriteria')) {
            if ($this->db->fieldExists('kriteria', 'kriteria') && ! $this->db->fieldExists('nama', 'kriteria')) {
                $this->forge->modifyColumn('kriteria', [
                    'kriteria' => [
                        'name'       => 'nama',
                        'type'       => 'VARCHAR',
                        'constraint' => 100,
                        'null'       => false,
                    ],
                ]);
            }

            if (! $this->db->fieldExists('kode', 'kriteria')) {
                $this->forge->addColumn('kriteria', [
                    'kode' => ['type' => 'VARCHAR', 'constraint' => 10, 'null' => true, 'after' => 'id'],
                ]);
            }

            if (! $this->db->fieldExists('atribut', 'kriteria')) {
                $this->forge->addColumn('kriteria', [
                    'atribut' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'benefit', 'after' => 'nama'],
                ]);
            }
        }
    }
}
