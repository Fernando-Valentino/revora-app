<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\FastApiService;
use App\Models\Pendapatan;
use Carbon\Carbon;

$lastKnownDate = Pendapatan::max('tanggal') ?? '2025-07-20';
$futureStart = Carbon::parse($lastKnownDate)->addDay()->format('Y-m-d');
$futureEnd = Carbon::parse($lastKnownDate)->addDays(7)->format('Y-m-d');

echo "Start: {$futureStart} | End: {$futureEnd}\n";

$fastApiService = app(FastApiService::class);
$response = $fastApiService->post('api/v1/predict', [
    'tanggal_mulai' => $futureStart,
    'tanggal_akhir' => $futureEnd,
    'rayon_id' => 0,
    'daftar_libur_nasional' => []
]);

if ($response) {
    echo "Success! Response:\n";
    print_r($response);
} else {
    echo "Failed to get response from FastAPI API!\n";
}
