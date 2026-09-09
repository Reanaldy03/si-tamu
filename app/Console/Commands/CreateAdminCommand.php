<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CreateAdminCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sitamu:admin:create 
                            {--name= : Nama lengkap administrator}
                            {--email= : Alamat email administrator}
                            {--password= : Kata sandi (opsional, akan ditanya secara interaktif jika kosong)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Bootstrap initial administrator account for SI-TAMU';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $name = $this->option('name') ?: $this->ask('Nama Administrator');
        $email = $this->option('email') ?: $this->ask('Email Administrator');

        // Validation for name and email
        $validator = Validator::make([
            'name' => $name,
            'email' => $email,
        ], [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'string', 'email', 'max:190', 'unique:users,email'],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return Command::FAILURE;
        }

        $password = $this->option('password');
        if (empty($password)) {
            $password = $this->secret('Password Administrator (minimal 8 karakter)');
            $confirmation = $this->secret('Ulangi Password Administrator');

            if ($password !== $confirmation) {
                $this->error('Konfirmasi kata sandi tidak cocok.');

                return Command::FAILURE;
            }
        }

        $passwordValidator = Validator::make(['password' => $password], [
            'password' => ['required', 'string', 'min:8'],
        ]);

        if ($passwordValidator->fails()) {
            foreach ($passwordValidator->errors()->all() as $error) {
                $this->error($error);
            }

            return Command::FAILURE;
        }

        // Verify ADMIN role exists
        $adminRole = DB::table('roles')->where('slug', 'ADMIN')->first();
        if (! $adminRole) {
            $this->error("Role 'ADMIN' tidak ditemukan. Pastikan seeder telah dijalankan: php artisan db:seed");

            return Command::FAILURE;
        }

        $publicId = (string) Str::ulid();
        $now = now();

        DB::transaction(function () use ($publicId, $name, $email, $password, $adminRole, $now) {
            $userId = DB::table('users')->insertGetId([
                'public_id' => $publicId,
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
                'is_active' => true,
                'last_login_at' => null,
                'password_changed_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('user_roles')->insert([
                'user_id' => $userId,
                'role_id' => $adminRole->id,
                'assigned_at' => $now,
                'assigned_by_user_id' => null,
            ]);

            DB::table('audit_logs')->insert([
                'event_type' => 'user.bootstrap_admin',
                'actor_type' => 'SYSTEM',
                'actor_user_id' => null,
                'target_type' => 'user',
                'target_id' => $userId,
                'target_public_id' => $publicId,
                'request_id' => null,
                'ip_hash' => null,
                'metadata' => json_encode(['email' => $email]),
                'before_state' => null,
                'after_state' => json_encode([
                    'id' => $userId,
                    'public_id' => $publicId,
                    'name' => $name,
                    'email' => $email,
                    'role' => 'ADMIN',
                    'is_active' => true,
                ]),
                'occurred_at' => $now,
            ]);
        });

        $this->info('Administrator SI-TAMU berhasil dibuat.');
        $this->table(
            ['Field', 'Value'],
            [
                ['Public ID', $publicId],
                ['Nama', $name],
                ['Email', $email],
                ['Role', 'ADMIN'],
                ['Status', 'Active'],
            ]
        );

        return Command::SUCCESS;
    }
}
