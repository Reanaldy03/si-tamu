<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrganizationSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();

        $formConfig = [
            'contact_phone' => ['enabled' => true, 'required' => false],
            'visitor_email' => ['enabled' => true, 'required' => false],
            'visitor_address' => ['enabled' => true, 'required' => false],
            'visitor_occupation' => ['enabled' => false, 'required' => false],
            'visitor_gender' => ['enabled' => false, 'required' => false],
            'visitor_identity' => ['enabled' => false, 'required' => false],
            'party_size' => ['enabled' => true, 'required' => false],
        ];

        DB::table('organization_settings')->updateOrInsert(
            ['id' => 1],
            [
                'id' => 1,
                'organization_name' => 'Sekretariat DPRD Kota Binjai',
                'logo_path' => null,
                'address' => 'Jl. Veteran No. 1, Kel. Tangsi, Kec. Binjai Kota, Kota Binjai, Sumatera Utara 20714',
                'contact_phone' => '(061) 8821000',
                'contact_email' => 'setwan@binjaikota.go.id',
                'registration_instruction' => 'Silakan mengisi formulir kunjungan, simpan/cetak QR Code tiket Anda, dan tunjukkan kepada petugas resepsionis di lobi gedung DPRD Kota Binjai.',
                'privacy_notice' => 'Data pribadi tamu dikelola oleh Sekretariat DPRD Kota Binjai untuk keperluan dokumentasi kedinasan dan keamanan kantor sesuai ketentuan perundang-undangan.',
                'timezone' => 'Asia/Jakarta',
                'registration_form_config' => json_encode($formConfig),
                'updated_by_user_id' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );
    }
}
