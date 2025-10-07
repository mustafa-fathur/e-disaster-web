<?php

namespace Database\Seeders;

use App\Models\Disaster;
use App\Models\DisasterVolunteer;
use App\Models\User;
use Illuminate\Database\Seeder;

class DisasterVolunteerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $disasters = Disaster::all();
        if ($disasters->isEmpty()) return;

        $volunteerUsers = User::whereIn('type', ['officer', 'volunteer'])->get();
        if ($volunteerUsers->isEmpty()) return;

        foreach ($disasters as $disaster) {
            // attach up to 3 volunteers per disaster
            foreach ($volunteerUsers->random(min(3, $volunteerUsers->count())) as $user) {
                DisasterVolunteer::firstOrCreate([
                    'disaster_id' => $disaster->id,
                    'user_id' => $user->id,
                ]);
            }
        }
    }
}
