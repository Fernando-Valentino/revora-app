<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

// 1. Model metrics columns
echo "=== MODEL METRICS COLUMNS ===\n";
$cols = DB::select("SHOW COLUMNS FROM model_metrics");
foreach ($cols as $c) {
    echo "{$c->Field} ({$c->Type})\n";
}

// 2. Model metrics data
echo "\n=== MODEL METRICS DATA ===\n";
$metrics = DB::table('model_metrics')->orderBy('id', 'desc')->get();
foreach ($metrics as $m) {
    $vals = get_object_vars($m);
    $parts = [];
    foreach ($vals as $k => $v) {
        $parts[] = "{$k}={$v}";
    }
    echo implode(" | ", $parts) . "\n";
}

// 3. Prediction results per rayon for latest runs
echo "\n=== PREDICTION RESULTS PER RAYON ===\n";

// Get latest run for each model type
$types = ['svr_default', 'svr_grid_search', 'svr_gwo'];
foreach ($types as $type) {
    $run = DB::table('model_runs')->where('model_type', $type)->where('status', 'success')->orderBy('id', 'desc')->first();
    if (!$run) {
        echo "\n--- {$type}: No run found ---\n";
        continue;
    }
    echo "\n--- {$type} (run_id={$run->id}) ---\n";
    $stats = DB::table('prediction_results')
        ->where('model_run_id', $run->id)
        ->selectRaw("rayon_name, count(*) as cnt, avg(actual_value) as avg_actual, avg(predicted_value) as avg_predicted, avg(abs(actual_value - predicted_value)) as avg_error, avg(CASE WHEN actual_value != 0 THEN abs((actual_value - predicted_value)/actual_value)*100 ELSE 0 END) as avg_mape")
        ->groupBy('rayon_name')
        ->orderBy('avg_mape')
        ->get();
    if (count($stats) == 0) {
        echo "  (no prediction results)\n";
    }
    foreach ($stats as $s) {
        echo "  {$s->rayon_name} | data={$s->cnt} | avg_aktual=Rp" . number_format($s->avg_actual, 0, ',', '.') . " | avg_prediksi=Rp" . number_format($s->avg_predicted, 0, ',', '.') . " | avg_error=Rp" . number_format($s->avg_error, 0, ',', '.') . " | MAPE=" . round($s->avg_mape, 2) . "%\n";
    }
}

// 4. Pendapatan data stats per rayon
echo "\n=== DATA PENDAPATAN PER RAYON ===\n";
$pendapatanStats = DB::table('pendapatans')
    ->join('rayons', 'pendapatans.rayon_id', '=', 'rayons.id')
    ->selectRaw("rayons.nama_rayon, count(*) as cnt, min(pendapatans.tanggal) as min_date, max(pendapatans.tanggal) as max_date, avg(pendapatans.jumlah_pendapatan) as avg_income, stddev(pendapatans.jumlah_pendapatan) as std_income, min(pendapatans.jumlah_pendapatan) as min_income, max(pendapatans.jumlah_pendapatan) as max_income")
    ->groupBy('rayons.nama_rayon')
    ->orderBy('rayons.nama_rayon')
    ->get();
foreach ($pendapatanStats as $p) {
    echo "{$p->nama_rayon} | data={$p->cnt} | periode={$p->min_date} s/d {$p->max_date} | avg=Rp" . number_format($p->avg_income, 0, ',', '.') . " | std=Rp" . number_format($p->std_income, 0, ',', '.') . " | min=Rp" . number_format($p->min_income, 0, ',', '.') . " | max=Rp" . number_format($p->max_income, 0, ',', '.') . "\n";
}
