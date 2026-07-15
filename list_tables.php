<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

// List tables
$tables = DB::select('SHOW TABLES');
echo "=== TABLES ===\n";
foreach ($tables as $t) {
    $vals = get_object_vars($t);
    echo array_values($vals)[0] . "\n";
}
