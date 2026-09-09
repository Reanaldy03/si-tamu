<?php

namespace Tests\Feature\Admin;

use App\Domain\Access\Enums\RoleCode;
use App\Models\OrganizationSetting;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\OrganizationSettingsSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OrganizationSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $staff;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleAndPermissionSeeder::class);
        $this->seed(OrganizationSettingsSeeder::class);

        $adminRole = Role::where('slug', RoleCode::ADMIN->value)->first();
        $staffRole = Role::where('slug', RoleCode::STAFF->value)->first();

        $this->admin = User::create([
            'public_id' => '01HXYZ00000000000000000001',
            'name' => 'Admin Utama',
            'email' => 'admin@sitamu.local',
            'password' => bcrypt('AdminPass123!'),
            'is_active' => true,
        ]);
        $this->admin->roles()->attach($adminRole->id, ['assigned_at' => now()]);

        $this->staff = User::create([
            'public_id' => '01HXYZ00000000000000000002',
            'name' => 'Staff Front-Office',
            'email' => 'staff@sitamu.local',
            'password' => bcrypt('StaffPass123!'),
            'is_active' => true,
        ]);
        $this->staff->roles()->attach($staffRole->id, ['assigned_at' => now()]);
    }

    public function test_admin_can_view_settings_form(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.settings.edit'));

        $response->assertOk();
        $response->assertSee('Pengaturan & Identitas Organisasi', false);
        $response->assertSee('Identitas Instansi');
        $response->assertSee('Konfigurasi Operasional');
    }

    public function test_staff_cannot_view_settings_form(): void
    {
        $response = $this->actingAs($this->staff)->get(route('admin.settings.edit'));

        $response->assertForbidden();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get(route('admin.settings.edit'));

        $response->assertRedirect(route('login'));
    }

    public function test_admin_can_update_organization_settings_with_audit_trail(): void
    {
        $payload = [
            'organization_name' => 'Kementerian Riset dan Inovasi Digital',
            'address' => 'Jl. Kebon Sirih No. 12, Jakarta Pusat',
            'contact_phone' => '021-3456789',
            'contact_email' => 'kontak@kemenrid.go.id',
            'registration_instruction' => 'Harap tunjukkan kartu identitas resmi di loket registrasi.',
            'privacy_notice' => 'Seluruh data pengunjung dilindungi UU PDP dan digunakan hanya untuk pengamanan.',
            'timezone' => 'Asia/Makassar',
        ];

        $response = $this->actingAs($this->admin)->put(route('admin.settings.update'), $payload);

        $response->assertRedirect(route('admin.settings.edit'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('organization_settings', [
            'organization_name' => 'Kementerian Riset dan Inovasi Digital',
            'contact_email' => 'kontak@kemenrid.go.id',
            'timezone' => 'Asia/Makassar',
            'updated_by_user_id' => $this->admin->id,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'event_type' => 'settings.updated',
            'actor_type' => 'USER',
            'actor_user_id' => $this->admin->id,
            'target_type' => 'organization_settings',
        ]);
    }

    public function test_admin_can_upload_branding_logo(): void
    {
        Storage::fake('public');

        $logoFile = UploadedFile::fake()->create('instansi_logo.png', 100, 'image/png');

        $payload = [
            'organization_name' => 'Instansi Maju Bersama',
            'address' => 'Jl. Pemuda No. 1',
            'registration_instruction' => 'Instruksi registrasi tamu',
            'privacy_notice' => 'Pemberitahuan privasi tamu',
            'timezone' => 'Asia/Jakarta',
            'logo' => $logoFile,
        ];

        $response = $this->actingAs($this->admin)->put(route('admin.settings.update'), $payload);

        $response->assertRedirect(route('admin.settings.edit'));

        $setting = OrganizationSetting::current();
        $this->assertNotNull($setting->logo_path);
        Storage::disk('public')->assertExists($setting->logo_path);
    }

    public function test_staff_cannot_update_settings(): void
    {
        $payload = [
            'organization_name' => 'Perubahan Ilegal Oleh Staff',
            'registration_instruction' => 'Instruksi',
            'privacy_notice' => 'Privasi',
            'timezone' => 'Asia/Jakarta',
        ];

        $response = $this->actingAs($this->staff)->put(route('admin.settings.update'), $payload);

        $response->assertForbidden();
    }

    public function test_validation_fails_on_missing_required_fields(): void
    {
        $response = $this->actingAs($this->admin)->put(route('admin.settings.update'), [
            'organization_name' => '',
            'registration_instruction' => '',
            'privacy_notice' => '',
            'timezone' => 'Invalid/Timezone',
        ]);

        $response->assertSessionHasErrors([
            'organization_name',
            'registration_instruction',
            'privacy_notice',
            'timezone',
        ]);
    }
}
