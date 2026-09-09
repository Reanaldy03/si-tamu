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
            'visitor_email' => ['enabled' => false, 'required' => false],
            'visitor_address' => ['enabled' => false, 'required' => false],
            'visitor_occupation' => ['enabled' => false, 'required' => false],
            'visitor_gender' => ['enabled' => false, 'required' => false],
            'visitor_identity' => ['enabled' => false, 'required' => false],
            'party_size' => ['enabled' => false, 'required' => false],
        ];

        DB::table('organization_settings')->updateOrInsert(
            ['id' => 1],
            [
                'id' => 1,
                'organization_name' => 'Nama Instansi',
                'logo_path' => null,
                'address' => null,
                'contact_phone' => null,
                'contact_email' => null,
                'registration_instruction' => 'Silakan mengisi data kunjungan dengan benar dan menunggu arahan petugas.',
                'privacy_notice' => 'Data yang Anda berikan digunakan untuk administrasi kunjungan sesuai kebijakan instansi.',
                'timezone' => 'Asia/Jakarta',
                'registration_form_config' => json_encode($formConfig),
                'updated_by_user_id' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );
    }
}
