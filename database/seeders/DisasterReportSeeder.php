<?php

namespace Database\Seeders;

use App\Models\Disaster;
use App\Models\DisasterReport;
use App\Models\DisasterVolunteer;
use App\Models\User;
use Illuminate\Database\Seeder;

class DisasterReportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $disasters = Disaster::all();
        if ($disasters->isEmpty()) return;

        foreach ($disasters as $disaster) {
            $reporter = DisasterVolunteer::where('disaster_id', $disaster->id)->inRandomOrder()->first();

            $reports = [
                [
                    'title' => 'Laporan Awal',
                    'description' => 'Laporan awal mengenai situasi di lokasi bencana. Tim menuju TKP.',
                    'lat' => $disaster->lat,
                    'long' => $disaster->long,
                    'is_final_stage' => false,
                ],
                [
                    'title' => 'Laporan Perkembangan',
                    'description' => 'Kondisi terbaru: evakuasi sedang berlangsung, kebutuhan logistik meningkat.',
                    'lat' => $disaster->lat,
                    'long' => $disaster->long,
                    'is_final_stage' => false,
                ],
                [
                    'title' => 'Laporan Akhir',
                    'description' => 'Situasi terkendali. Pembersihan dan pemulihan dimulai.',
                    'lat' => $disaster->lat,
                    'long' => $disaster->long,
                    'is_final_stage' => true,
                ],
            ];

            foreach ($reports as $data) {
                DisasterReport::create([
                    ...$data,
                    'disaster_id' => $disaster->id,
                    'reported_by' => $reporter?->id,
                ]);
            }
        }
    }
}
