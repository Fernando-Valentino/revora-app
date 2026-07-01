<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Rayon;
use App\Models\JuruParkir;
use App\Models\Pendapatan;
use App\Models\HariLibur;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class CsvSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Seed Rayons first
        $this->call([
            RayonSeeder::class,
        ]);

        // 2. Fetch and Seed Holidays for 2023, 2024, 2025, 2026
        $years = [2023, 2024, 2025, 2026];
        $holidayDates = [];

        foreach ($years as $year) {
            $this->command->info("Fetching national holidays for year $year...");
            try {
                // Fetch from the API
                $response = Http::timeout(15)->get("https://api-hari-libur.vercel.app/api?year={$year}");
                if ($response->successful()) {
                    $resData = $response->json();
                    $holidays = $resData['data'] ?? [];

                    foreach ($holidays as $item) {
                        $dateStr = $item['date'] ?? null;
                        $desc = $item['description'] ?? 'Hari Libur';

                        if ($dateStr) {
                            HariLibur::updateOrCreate(
                                ['tanggal' => $dateStr],
                                [
                                    'keterangan' => $desc,
                                    'tipe' => 'Libur Nasional',
                                ]
                            );
                            $holidayDates[] = $dateStr;
                        }
                    }
                }
            } catch (\Exception $e) {
                $this->command->error("Failed to fetch holidays from API for $year: " . $e->getMessage());
            }

            // Generate Saturdays & Sundays (Weekend)
            $this->command->info("Generating weekends for year $year...");
            $startDate = Carbon::createFromDate($year, 1, 1);
            $endDate = Carbon::createFromDate($year, 12, 31);
            $dayMapping = [
                'Sunday' => 'Minggu',
                'Monday' => 'Senin',
                'Tuesday' => 'Selasa',
                'Wednesday' => 'Rabu',
                'Thursday' => 'Kamis',
                'Friday' => 'Jumat',
                'Saturday' => 'Sabtu'
            ];

            for ($date = $startDate; $date->lte($endDate); $date->addDay()) {
                if ($date->isWeekend()) {
                    $dateString = $date->toDateString();
                    $englishDay = $date->format('l');
                    $indonesianDay = $dayMapping[$englishDay] ?? $englishDay;

                    HariLibur::updateOrCreate(
                        ['tanggal' => $dateString],
                        [
                            'keterangan' => "Weekend ({$indonesianDay})",
                            'tipe' => 'Weekend',
                        ]
                    );
                }
            }
        }

        // Hardcode fallback list of national holidays to ensure we don't miss any from constants.py
        $liburNasionalConst = [
            '2023-01-01', '2023-01-10', '2023-01-23', '2023-02-18', '2023-03-22',
            '2023-03-23', '2023-04-07', '2023-04-19', '2023-04-20', '2023-04-21',
            '2023-04-22', '2023-04-23', '2023-04-24', '2023-04-25', '2023-05-01',
            '2023-05-18', '2023-06-01', '2023-06-02', '2023-06-04', '2023-06-28',
            '2023-06-29', '2023-06-30', '2023-07-19', '2023-08-17', '2023-09-28',
            '2023-12-25', '2023-12-26', '2024-01-01', '2024-02-08', '2024-02-09',
            '2024-02-10', '2024-03-11', '2024-03-12', '2024-03-29', '2024-03-31',
            '2024-04-08', '2024-04-09', '2024-04-10', '2024-04-11', '2024-04-12',
            '2024-04-15', '2024-05-01', '2024-05-09', '2024-05-10', '2024-05-23',
            '2024-05-24', '2024-06-01', '2024-06-14', '2024-06-17', '2024-07-07',
            '2024-08-17', '2024-09-16', '2024-12-25', '2024-12-26', '2025-01-01',
            '2025-01-27', '2025-01-28', '2025-01-29', '2025-03-28', '2025-03-29',
            '2025-03-31', '2025-04-01', '2025-04-02', '2025-04-03', '2025-04-04',
            '2025-04-07', '2025-04-18', '2025-04-20', '2025-05-01', '2025-05-12',
            '2025-05-13', '2025-05-29', '2025-05-30', '2025-06-01', '2025-06-06',
            '2025-06-09', '2025-06-27', '2025-08-17', '2025-08-18', '2025-09-05',
            '2025-12-25', '2025-12-26', '2026-01-01', '2026-01-16', '2026-02-17',
            '2026-03-18', '2026-03-19', '2026-03-20', '2026-03-21', '2026-03-22',
            '2026-03-23', '2026-03-24', '2026-04-03', '2026-04-05', '2026-05-01',
            '2026-05-14', '2026-05-15', '2026-05-27', '2026-05-28', '2026-05-31',
            '2026-06-01', '2026-06-16', '2026-08-17', '2026-08-25', '2026-12-24',
            '2026-12-25', '2027-01-01', '2027-01-05', '2027-02-06', '2027-03-09',
            '2027-03-10', '2027-03-26', '2027-05-01', '2027-05-06', '2027-05-17',
            '2027-05-20', '2027-06-01', '2027-06-06', '2027-08-15', '2027-08-17',
            '2027-12-25', '2027-12-26'
        ];

        foreach ($liburNasionalConst as $dateStr) {
            $exists = HariLibur::where('tanggal', $dateStr)->first();
            if (!$exists) {
                HariLibur::create([
                    'tanggal' => $dateStr,
                    'keterangan' => 'Hari Libur Nasional',
                    'tipe' => 'Libur Nasional'
                ]);
            } else if ($exists->tipe === 'Weekend') {
                $exists->update([
                    'keterangan' => $exists->keterangan . ' & Libur Nasional',
                    'tipe' => 'Libur Nasional'
                ]);
            }
        }

        // 3. Import CSV transactions
        $csvPath = 'd:/KULIAH/Semester 8/MODEL_SVR/ml-engine/research/DATA_PENDAPATAN_PARKIR_PER_HARI_2023-2025.csv';
        if (!file_exists($csvPath)) {
            $this->command->error("CSV file not found at: $csvPath");
            return;
        }

        $this->command->info("Seeding Pendapatan data from CSV...");
        $handle = fopen($csvPath, 'r');
        $header = fgetcsv($handle, 1000, ',');

        $rayonsMap = [
            '1' => Rayon::where('nama_rayon', 'Rayon I')->first(),
            '2' => Rayon::where('nama_rayon', 'Rayon II')->first(),
            '3' => Rayon::where('nama_rayon', 'Rayon III')->first(),
            '4' => Rayon::where('nama_rayon', 'Rayon IV')->first(),
            '5' => Rayon::where('nama_rayon', 'Rayon V')->first(),
        ];

        $jukirsMap = [];
        foreach ($rayonsMap as $key => $rayon) {
            if ($rayon) {
                $jukirsMap[$key] = JuruParkir::where('rayon_id', $rayon->id)->first();
            }
        }

        $insertBuffer = [];
        $count = 0;

        while (($row = fgetcsv($handle, 1000, ',')) !== false) {
            if (empty($row) || count($row) < 9) continue;

            $tanggal = $row[0];
            $rayonKey = $row[4];
            $jumlah = floatval($row[8]);

            $rayon = $rayonsMap[$rayonKey] ?? null;
            $juruParkir = $jukirsMap[$rayonKey] ?? null;

            if ($rayon && $juruParkir) {
                $insertBuffer[] = [
                    'tanggal' => $tanggal,
                    'rayon_id' => $rayon->id,
                    'juru_parkir_id' => $juruParkir->id,
                    'jumlah' => $jumlah,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                $count++;

                if (count($insertBuffer) >= 500) {
                    Pendapatan::insert($insertBuffer);
                    $insertBuffer = [];
                }
            }
        }

        if (count($insertBuffer) > 0) {
            Pendapatan::insert($insertBuffer);
        }

        fclose($handle);
        $this->command->info("Successfully seeded $count pendapatan records.");
    }
}
