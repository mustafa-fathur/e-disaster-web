<?php

namespace Database\Seeders;

use App\Models\Disaster;
use App\Models\User;
use Illuminate\Database\Seeder;

class DisasterSeeder extends Seeder
{
    public function run(): void
    {
        $disasters = [
            [
                'title' => 'Gempa Bumi M 6.2 di Jawa Barat',
                'description' => 'Gempa bumi berkekuatan 6.2 SR mengguncang wilayah Jawa Barat. Pusat gempa berada di kedalaman 10 km.',
                'source' => 'BMKG',
                'types' => 'gempa bumi',
                'status' => 'ongoing',
                'date' => now()->subDays(2)->format('Y-m-d'),
                'time' => '14:30:00',
                'location' => 'Cianjur, Jawa Barat',
                'lat' => -6.8,
                'long' => 107.1,
                'magnitude' => 6.2,
                'depth' => 10.0,
            ],
            [
                'title' => 'Banjir Bandang di Jakarta',
                'description' => 'Banjir bandang melanda beberapa wilayah Jakarta akibat curah hujan tinggi.',
                'source' => 'manual',
                'types' => 'banjir',
                'status' => 'ongoing',
                'date' => now()->subDays(1)->format('Y-m-d'),
                'time' => '08:15:00',
                'location' => 'Jakarta Selatan',
                'lat' => -6.2615,
                'long' => 106.8106,
                'magnitude' => null,
                'depth' => null,
            ],
            [
                'title' => 'Gunung Merapi Erupsi',
                'description' => 'Gunung Merapi mengalami erupsi dengan kolom abu mencapai ketinggian 3000 meter.',
                'source' => 'BMKG',
                'types' => 'gunung meletus',
                'status' => 'ongoing',
                'date' => now()->subHours(6)->format('Y-m-d'),
                'time' => '03:45:00',
                'location' => 'Sleman, Yogyakarta',
                'lat' => -7.5407,
                'long' => 110.4456,
                'magnitude' => null,
                'depth' => null,
            ],
            [
                'title' => 'Tsunami Warning di Aceh',
                'description' => 'Peringatan tsunami dikeluarkan setelah gempa bumi berkekuatan 7.1 SR di Samudra Hindia.',
                'source' => 'BMKG',
                'types' => 'tsunami',
                'status' => 'completed',
                'date' => now()->subDays(5)->format('Y-m-d'),
                'time' => '22:30:00',
                'location' => 'Banda Aceh, Aceh',
                'lat' => 5.5483,
                'long' => 95.3238,
                'magnitude' => 7.1,
                'depth' => 15.0,
            ],
            [
                'title' => 'Kekeringan di Nusa Tenggara',
                'description' => 'Wilayah Nusa Tenggara Timur mengalami kekeringan parah dengan curah hujan sangat rendah.',
                'source' => 'manual',
                'types' => 'kekeringan',
                'status' => 'ongoing',
                'date' => now()->subWeeks(2)->format('Y-m-d'),
                'time' => null,
                'location' => 'Kupang, NTT',
                'lat' => -10.1833,
                'long' => 123.5833,
                'magnitude' => null,
                'depth' => null,
            ],
            [
                'title' => 'Angin Topan di Sulawesi',
                'description' => 'Angin topan dengan kecepatan 120 km/jam melanda wilayah Sulawesi Selatan.',
                'source' => 'BMKG',
                'types' => 'angin topan',
                'status' => 'completed',
                'date' => now()->subDays(3)->format('Y-m-d'),
                'time' => '16:20:00',
                'location' => 'Makassar, Sulawesi Selatan',
                'lat' => -5.1477,
                'long' => 119.4327,
                'magnitude' => null,
                'depth' => null,
            ],
            [
                'title' => 'Tanah Longsor di Sumatera',
                'description' => 'Tanah longsor terjadi di lereng bukit akibat hujan deras yang berlangsung 3 hari.',
                'source' => 'manual',
                'types' => 'tahan longsor',
                'status' => 'ongoing',
                'date' => now()->subDays(4)->format('Y-m-d'),
                'time' => '11:00:00',
                'location' => 'Bukittinggi, Sumatera Barat',
                'lat' => -0.3048,
                'long' => 100.3691,
                'magnitude' => null,
                'depth' => null,
            ],
            [
                'title' => 'Bencana Non Alam - Pandemi',
                'description' => 'Peningkatan kasus penyakit menular di beberapa wilayah Indonesia.',
                'source' => 'manual',
                'types' => 'bencanan non alam',
                'status' => 'ongoing',
                'date' => now()->subWeeks(1)->format('Y-m-d'),
                'time' => null,
                'location' => 'Jakarta Pusat',
                'lat' => -6.1944,
                'long' => 106.8229,
                'magnitude' => null,
                'depth' => null,
            ],
        ];

        // Get a random user to assign as reporter
        $reporter = User::inRandomOrder()->first();

        foreach ($disasters as $disasterData) {
            Disaster::create([
                ...$disasterData,
                'reported_by' => $reporter?->id,
            ]);
        }
    }
}