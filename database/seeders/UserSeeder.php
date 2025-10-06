<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create specific test users for different roles
        
        // 1. Admin Users (Web-only access)
        User::factory()->admin()->create([
            'name' => 'admin',
            'email' => 'admin@edisaster.test',
            'timezone' => 'Asia/Jakarta',
            'location' => 'Jakarta',
            'lat' => -6.2088,
            'long' => 106.8456,
            'nik' => '1234567890123456',
            'phone' => '+6281234567890',
            'address' => 'Jl. Sudirman No. 1, Jakarta Pusat',
        ]);

        // 2. Officer Users (Web + Mobile access)
        User::factory()->officer()->create([
            'name' => 'Fathur',
            'email' => 'fathur@edisaster.test',
            'timezone' => 'Asia/Jakarta',
            'location' => 'Padang',
            'lat' => -0.9471,
            'long' => 100.4172,
            'nik' => '2345678901234567',
            'phone' => '+6282345678901',
            'address' => 'Jl. Imam Bonjol No. 15, Padang',
        ]);

        // 3. Active Volunteer Users (Web + Mobile access)
        User::factory()->activeVolunteer()->create([
            'name' => 'Ilham',
            'email' => 'ilham@edisaster.test',
            'timezone' => 'Asia/Jakarta',
            'location' => 'Jakarta',
            'lat' => -6.2088,
            'long' => 106.8456,
            'nik' => '3456789012345678',
            'phone' => '+6283456789012',
            'address' => 'Jl. Thamrin No. 25, Jakarta Selatan',
        ]);

        User::factory()->activeVolunteer()->create([
            'name' => 'Nouval',
            'email' => 'nouval@edisaster.test',
            'timezone' => 'Asia/Jakarta',
            'location' => 'Padang',
            'lat' => -0.9471,
            'long' => 100.4172,
            'nik' => '4567890123456789',
            'phone' => '+6284567890123',
            'address' => 'Jl. Ahmad Yani No. 8, Padang',
        ]);

        User::factory()->activeVolunteer()->create([
            'name' => 'Fariz',
            'email' => 'fariz@edisaster.test',
            'timezone' => 'Asia/Jakarta',
            'location' => 'Batusangkar',
            'lat' => -0.4562,
            'long' => 100.5842,
            'nik' => '5678901234567890',
            'phone' => '+6285678901234',
            'address' => 'Jl. Merdeka No. 12, Batusangkar',
        ]);

        $this->command->info('✅ UserSeeder completed successfully!');
        $this->command->info('📊 Created users:');
        $this->command->info('   - 1 Admin user: admin@edisaster.test (Jakarta) - web-only');
        $this->command->info('   - 1 Officer user: fathur@edisaster.test (Padang) - web + mobile');
        $this->command->info('   - 3 Active volunteers:');
        $this->command->info('     * ilham@edisaster.test (Jakarta)');
        $this->command->info('     * nouval@edisaster.test (Padang)');
        $this->command->info('     * fariz@edisaster.test (Batusangkar)');
        $this->command->info('   - All users timezone: Asia/Jakarta (WIB)');
        $this->command->info('   - Total: 5 test users');
    }
}