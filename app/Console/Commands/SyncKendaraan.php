<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncKendaraan extends Command
{
    protected $signature = 'sync:kendaraan';
    protected $description = 'Sync kendaraan from remote server';

    public function handle()
    {
        $mapKonfigurasiSumbu = [
            '1.1' => 1,
            '1.1.2' => 2,
            '1.2' => 3,
            '1.2.2' => 4,
            '1.1.2.2' => 5,
            '2.2' => 6,
            '2.2.2' => 7,
            '1.1.1' => 8,
            '1.2.1' => 9,
        ];

        $data = DB::connection('remote_pgsql')
            ->table('v_kendaraan')
            ->get();

        foreach ($data as $row) {
            $status = $row->umum ? 'UMUM' : 'BUKAN UMUM';
            $id = $row->id_kendaraan;

            /**
             * KONFIGURASI SUMBU
             */
            $konfigurasi_sumbu_id = $mapKonfigurasiSumbu[$row->conf_sumbu] ?? 1;


            // insert master kendaraan
            $id = DB::table('m_kendaraans')->updateOrInsert(
                ['id' => $id],
                [
                    'id' => $id,
                    'no_sut' => $row->no_tipe,
                    'penerbit_sut' => $row->oleh_tipe,
                    'tanggal_sut' => $row->tgl_tipe,
                    'no_srut' => $row->no_regis,
                    'penerbit_srut' => $row->oleh_regis,
                    'tanggal_srut' => $row->tgl_regis,
                    'no_srb' => $row->no_rancang,
                    'penerbit_srb' => $row->oleh_rancang,
                    'tanggal_srb' => $row->tgl_rancang,
                    'tanggal_stnk' => $row->awal_pakai,
                    'tahun_kendaraan' => $row->tahun,
                    'tanggal_mati_uji' => $row->tgl_mati_uji,
                    'no_uji' => $row->no_uji,
                    'no_kendaraan' => $row->no_kendaraan,
                    'identitas' => $row->identitas,
                    'no_identitas' => $row->no_identitas,
                    'nama_pemilik' => $row->nama_pemilik,
                    'alamat' => $row->alamat,
                    'rt' => $row->rt,
                    'rw' => $row->rw,
                    'provinsi_id' => 35,
                    'kota_id' => 3515,
                    'kecamatan_id' => 351501,
                    'kelurahan_id' => 3515012001,
                    'no_rangka' => $row->no_chasis,
                    'no_mesin' => $row->no_mesin,
                    'status' => $status,
                    'merk_id' => $row->vehicle_brand_id,
                    'varian_merk_id' => $row->vehicle_varian_type_id,
                    'tipe_varian_merk_id' => $row->vehicle_varian_id,
                    'jenis_kendaraan_id' => $row->vehicle_type_id,
                    'sub_jenis_kendaraan_id' => $row->vehicle_sub_id,
                    'warna_cabin' => $row->warna,
                    'warna_bak' => $row->warna_bak,
                    // 'bahan_utama_id' => $row->karoseri_bahan,
                    'jumlah_duduk' => 0,
                    'jumlah_berdiri' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            // insert spesifikasi
            DB::table('m_kendaraan_spesifikasis')->updateOrInsert(
                ['id' => $id],
                [
                    'kendaraan_id' => $id,
                    'isi_silinder' => $this->toIntOrNull($row->isi_silinder),
                    'daya_motor' => $this->toDecimalOrNull($row->daya_motor),
                    'bahan_bakar_id' => $row->fuel_id,
                    'panjang_utama' => $this->toIntOrNull($row->ukuran_panjang),
                    'lebar_utama' => $this->toIntOrNull($row->ukuran_lebar),
                    'tinggi_utama' => $this->toIntOrNull($row->ukuran_tinggi),
                    'panjang_bak' => $this->toIntOrNull($row->dimpanjang),
                    'lebar_bak' => $this->toIntOrNull($row->dimlebar),
                    'tinggi_bak' => $this->toIntOrNull($row->dimtinggi),
                    'roh' => $this->toIntOrNull($row->bagian_belakang),
                    'foh' => $this->toIntOrNull($row->bagian_depan),
                    'jarak_terendah' => $this->toIntOrNull($row->bagian_jterendah),
                    'konfigurasi_sumbu_id' => $konfigurasi_sumbu_id,
                    'jarak_sumbu_1_2' => $this->toIntOrNull($row->jsumbu1),
                    'jarak_sumbu_2_3' => $this->toIntOrNull($row->jsumbu2),
                    'jarak_sumbu_3_4' => $this->toIntOrNull($row->jsumbu3),
                    'jarak_sumbu_4_5' => $this->toIntOrNull($row->jsumbu4),
                    'berat_sumbu_1' => $this->toIntOrNull($row->bsumbu1),
                    'berat_sumbu_2' => $this->toIntOrNull($row->bsumbu2),
                    'berat_sumbu_3' => $this->toIntOrNull($row->bsumbu3),
                    'berat_sumbu_4' => $this->toIntOrNull($row->bsumbu4),
                    'berat_sumbu_5' => $this->toIntOrNull($row->bsumbu5),
                    'pemakaian_sumbu_1' => $row->psumbu1,
                    'pemakaian_sumbu_2' => $row->psumbu2,
                    'pemakaian_sumbu_3' => $row->psumbu3,
                    'pemakaian_sumbu_4' => $row->psumbu4,
                    'daya_dukung_sumbu_1' => $this->toIntOrNull($row->dydukpab1),
                    'daya_dukung_sumbu_2' => $this->toIntOrNull($row->dydukpab2),
                    'daya_dukung_sumbu_3' => $this->toIntOrNull($row->dydukpab3),
                    'daya_dukung_sumbu_4' => $this->toIntOrNull($row->dydukpab4),
                    'daya_dukung_sumbu_5' => $this->toIntOrNull($row->dydukpab5),
                    'jbb' => $this->toIntOrNull($row->kemjbb),
                    'jbkb' => $this->toIntOrNull($row->kemjbkb),
                    'jbki' => $this->toIntOrNull($row->kemjbki),
                    'mst' => $this->toIntOrNull($row->mst),
                    'daya_angkut_orang' => $this->toIntOrNull($row->kemorang),
                    'daya_angkut_barang' => $this->toIntOrNull($row->kembarang),
                    'kelas_jalan_id' => $row->kelasjalan_id1,
                    'ukuran_qr' => $this->toIntOrNull($row->ukq),
                    'ukuran_p1' => $this->toIntOrNull($row->ukp),
                    'ukuran_p2' => $this->toIntOrNull($row->ukp2),
                    'volume_tera' => 0,
                    'jenis_muatan' => null,
                    'berat_jenis_muatan' => 0,
                    'volume_muatan' => 0,
                ]
            );
        }

        $this->info('Sync selesai: ' . count($data));
    }

    private function toIntOrNull($value)
    {
        return is_numeric($value) ? (int) $value : 0;
    }

    private function toDecimalOrNull($value)
    {
        return is_numeric($value) ? (float) $value : 0;
    }
}
