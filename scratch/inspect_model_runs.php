<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\ModelRun;

$runs = ModelRun::orderBy('id', 'desc')->take(20)->get();
foreach ($runs as $run) {
    echo "ID: {$run->id} | Type: {$run->model_type} | Status: {$run->status} | Created: {$run->created_at} | Updated: {$run->updated_at}\n";
    if ($run->modelParameter) {
        echo "   Params: C=" . $run->modelParameter->c_value . ", eps=" . $run->modelParameter->epsilon_value . ", gamma=" . $run->modelParameter->gamma_value . "\n";
    }
}
