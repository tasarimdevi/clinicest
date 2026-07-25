<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            SubscriptionPlanSeeder::class,
            DemoDataSeeder::class,
        ]);

        $admin = User::factory()->create([
            'name' => 'Clinicest Admin',
            'email' => 'admin@clinicest.test',
        ]);
        $admin->assignRole('super_admin');
    }
}
