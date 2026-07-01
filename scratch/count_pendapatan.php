<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Pendapatan;
use Illuminate\Support\Facades\DB;

$total = Pendapatan::count();
echo "Total Pendapatan: " . $total . "\n";

$by_rayon = Pendapatan::select('rayon_id', DB::raw('count(*) as total'))
    ->groupBy('rayon_id')
    ->get();
foreach ($by_rayon as $row) {
    echo "Rayon ID " . $row->rayon_id . ": " . $row->total . " baris\n";
}
