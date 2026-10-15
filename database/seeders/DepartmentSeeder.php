<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();

        $departments = [
            [
                'code' => 'UMUM',
                'name' => 'Bagian Umum dan Keuangan',
                'description' => 'Pelayanan administrasi umum, perlengkapan, tata usaha, kepegawaian, dan pengelolaan keuangan Sekretariat DPRD.',
                'sort_order' => 10,
            ],
            [
                'code' => 'PERSIDANGAN',
                'name' => 'Bagian Persidangan dan Perundang-Undangan',
                'description' => 'Fasilitasi rapat paripurna, risalah sidang, perumusan produk hukum daerah, dan dokumentasi perundang-undangan.',
                'sort_order' => 20,
            ],
            [
                'code' => 'WASGAR',
                'name' => 'Bagian Fasilitasi Penganggaran dan Pengawasan',
                'description' => 'Fasilitasi fungsi penganggaran (budgeting) dan pengawasan (controlling) DPRD terhadap pelaksanaan APBD.',
                'sort_order' => 30,
            ],
            [
                'code' => 'PIMPINAN',
                'name' => 'Pimpinan DPRD & Sekretaris DPRD',
                'description' => 'Audiensi dan koordinasi kedinasan dengan Ketua, Wakil Ketua, dan Sekretaris DPRD Kota Binjai.',
                'sort_order' => 40,
            ],
            [
                'code' => 'KOMISI-A',
                'name' => 'Komisi A - Pemerintahan dan Hukum',
                'description' => 'Bidang pemerintahan, ketertiban umum, kepegawaian, kependudukan, hukum, dan perizinan.',
                'sort_order' => 50,
            ],
            [
                'code' => 'KOMISI-B',
                'name' => 'Komisi B - Perekonomian dan Keuangan',
                'description' => 'Bidang perekonomian, keuangan daerah, perpajakan, perdagangan, perindustrian, dan BUMD.',
                'sort_order' => 60,
            ],
            [
                'code' => 'KOMISI-C',
                'name' => 'Komisi C - Pembangunan dan Kesejahteraan Rakyat',
                'description' => 'Bidang pekerjaan umum, tata kota, lingkungan hidup, pendidikan, kesehatan, dan kesejahteraan sosial.',
                'sort_order' => 70,
            ],
            [
                'code' => 'FRAKSI',
                'name' => 'Fraksi-Fraksi DPRD Kota Binjai',
                'description' => 'Sekretariat dan ruang fraksi partai politik DPRD Kota Binjai.',
                'sort_order' => 80,
            ],
        ];

        foreach ($departments as $dept) {
            $existing = DB::table('departments')->where('code', $dept['code'])->first();

            if ($existing) {
                DB::table('departments')->where('id', $existing->id)->update([
                    'name' => $dept['name'],
                    'description' => $dept['description'],
                    'sort_order' => $dept['sort_order'],
                    'is_active' => true,
                    'updated_at' => $now,
                ]);
            } else {
                DB::table('departments')->insert([
                    'public_id' => (string) Str::ulid(),
                    'code' => $dept['code'],
                    'name' => $dept['name'],
                    'description' => $dept['description'],
                    'sort_order' => $dept['sort_order'],
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }
}
