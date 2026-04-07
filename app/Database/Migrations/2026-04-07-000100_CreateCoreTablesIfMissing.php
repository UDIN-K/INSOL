<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCoreTablesIfMissing extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('users')) {
            $this->forge->addField([
                'id' => [
                    'type' => 'INT',
                    'constraint' => 10,
                    'unsigned' => true,
                    'auto_increment' => true,
                ],
                'username' => [
                    'type' => 'VARCHAR',
                    'constraint' => 50,
                ],
                'password' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                ],
                'nama' => [
                    'type' => 'VARCHAR',
                    'constraint' => 100,
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
            $this->forge->addUniqueKey('username', 'uq_users_username');
            $this->forge->createTable('users');
        }

        if ($this->db->tableExists('users')) {
            $admin = $this->db->table('users')->where('username', 'admin')->get()->getRowArray();
            if ($admin === null) {
                $this->db->table('users')->insert([
                    'username' => 'admin',
                    'password' => password_hash('admin123', PASSWORD_DEFAULT),
                    'nama' => 'Administrator',
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
            }
        }

        if (! $this->db->tableExists('mahasiswa')) {
            $this->forge->addField([
                'id' => [
                    'type' => 'INT',
                    'constraint' => 10,
                    'unsigned' => true,
                    'auto_increment' => true,
                ],
                'nim' => [
                    'type' => 'VARCHAR',
                    'constraint' => 30,
                ],
                'nama' => [
                    'type' => 'VARCHAR',
                    'constraint' => 120,
                ],
                'jenis_kelamin' => [
                    'type' => 'VARCHAR',
                    'constraint' => 20,
                    'null' => true,
                ],
                'tempat_lahir' => [
                    'type' => 'VARCHAR',
                    'constraint' => 100,
                    'null' => true,
                ],
                'tanggal_lahir' => [
                    'type' => 'DATE',
                    'null' => true,
                ],
                'alamat' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'telepon' => [
                    'type' => 'VARCHAR',
                    'constraint' => 30,
                    'null' => true,
                ],
                'email' => [
                    'type' => 'VARCHAR',
                    'constraint' => 120,
                    'null' => true,
                ],
                'prodi' => [
                    'type' => 'VARCHAR',
                    'constraint' => 120,
                    'null' => true,
                ],
                'semester' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'null' => true,
                ],
                'tahun_masuk' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'null' => true,
                ],
                'nama_ibu' => [
                    'type' => 'VARCHAR',
                    'constraint' => 100,
                    'null' => true,
                ],
                'nama_bapak' => [
                    'type' => 'VARCHAR',
                    'constraint' => 100,
                    'null' => true,
                ],
                'ipk' => [
                    'type' => 'DECIMAL',
                    'constraint' => '4,2',
                    'null' => true,
                ],
                'penghasilan_ortu' => [
                    'type' => 'BIGINT',
                    'null' => true,
                ],
                'jumlah_tanggungan' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'null' => true,
                ],
                'prestasi_non_akademik' => [
                    'type' => 'VARCHAR',
                    'constraint' => 100,
                    'null' => true,
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
            $this->forge->addUniqueKey('nim', 'uq_mahasiswa_nim');
            $this->forge->createTable('mahasiswa');
        }

        if (! $this->db->tableExists('kriteria')) {
            $this->forge->addField([
                'id' => [
                    'type' => 'INT',
                    'constraint' => 10,
                    'unsigned' => true,
                    'auto_increment' => true,
                ],
                'kode' => [
                    'type' => 'VARCHAR',
                    'constraint' => 10,
                ],
                'kriteria' => [
                    'type' => 'VARCHAR',
                    'constraint' => 120,
                ],
                'bobot' => [
                    'type' => 'DECIMAL',
                    'constraint' => '8,4',
                    'default' => 0,
                ],
                'atribut' => [
                    'type' => 'VARCHAR',
                    'constraint' => 20,
                    'default' => 'benefit',
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
            $this->forge->addUniqueKey('kode', 'uq_kriteria_kode');
            $this->forge->createTable('kriteria');

            $this->db->table('kriteria')->insertBatch([
                ['kode' => 'C1', 'kriteria' => 'IPK', 'bobot' => 0.25, 'atribut' => 'benefit'],
                ['kode' => 'C2', 'kriteria' => 'Penghasilan Orang Tua', 'bobot' => 0.25, 'atribut' => 'cost'],
                ['kode' => 'C3', 'kriteria' => 'Jumlah Tanggungan', 'bobot' => 0.25, 'atribut' => 'benefit'],
                ['kode' => 'C4', 'kriteria' => 'Prestasi Non Akademik', 'bobot' => 0.25, 'atribut' => 'benefit'],
            ]);
        }

        if (! $this->db->tableExists('penilaian')) {
            $this->forge->addField([
                'id' => [
                    'type' => 'INT',
                    'constraint' => 10,
                    'unsigned' => true,
                    'auto_increment' => true,
                ],
                'mahasiswa_id' => [
                    'type' => 'INT',
                    'constraint' => 10,
                    'unsigned' => true,
                ],
                'kriteria_id' => [
                    'type' => 'INT',
                    'constraint' => 10,
                    'unsigned' => true,
                ],
                'nilai' => [
                    'type' => 'DECIMAL',
                    'constraint' => '12,4',
                    'default' => 0,
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
            $this->forge->addForeignKey('mahasiswa_id', 'mahasiswa', 'id', 'CASCADE', 'CASCADE');
            $this->forge->addForeignKey('kriteria_id', 'kriteria', 'id', 'CASCADE', 'CASCADE');
            $this->forge->addUniqueKey(['mahasiswa_id', 'kriteria_id'], 'uq_penilaian_mahasiswa_kriteria');
            $this->forge->createTable('penilaian');
        }

        if (! $this->db->tableExists('hasil')) {
            $this->forge->addField([
                'id' => [
                    'type' => 'INT',
                    'constraint' => 10,
                    'unsigned' => true,
                    'auto_increment' => true,
                ],
                'mahasiswa_id' => [
                    'type' => 'INT',
                    'constraint' => 10,
                    'unsigned' => true,
                ],
                'penilaian_ke' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'default' => 1,
                ],
                'skor' => [
                    'type' => 'DECIMAL',
                    'constraint' => '12,6',
                    'default' => 0,
                ],
                'ranking' => [
                    'type' => 'INT',
                    'constraint' => 11,
                ],
                'status_lolos' => [
                    'type' => 'VARCHAR',
                    'constraint' => 20,
                    'default' => 'Tidak Lolos',
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
            $this->forge->addForeignKey('mahasiswa_id', 'mahasiswa', 'id', 'CASCADE', 'CASCADE');
            $this->forge->addUniqueKey(['penilaian_ke', 'mahasiswa_id'], 'uq_hasil_penilaian_mahasiswa');
            $this->forge->createTable('hasil');
        }
    }

    public function down()
    {
        if ($this->db->tableExists('hasil')) {
            $this->forge->dropTable('hasil', true);
        }

        if ($this->db->tableExists('penilaian')) {
            $this->forge->dropTable('penilaian', true);
        }

        if ($this->db->tableExists('kriteria')) {
            $this->forge->dropTable('kriteria', true);
        }

        if ($this->db->tableExists('mahasiswa')) {
            $this->forge->dropTable('mahasiswa', true);
        }

        if ($this->db->tableExists('users')) {
            $this->forge->dropTable('users', true);
        }
    }
}
