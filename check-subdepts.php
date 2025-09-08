<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Check if there are sub-departments defined
echo "=== Checking Sub-Departments ===\n";

// Check account table structure
$accountStructure = DB::select('DESCRIBE tb_account');
echo "tb_account table structure:\n";
foreach ($accountStructure as $column) {
    echo "- {$column->Field}: {$column->Type}\n";
}

echo "\n=== Checking for Sub-Department Values ===\n";

// Check if there are any non-null sub_department_id values
$subDeptValues = DB::select("SELECT DISTINCT sub_department_id FROM tb_account WHERE sub_department_id IS NOT NULL");
echo "Existing sub_department_id values:\n";
foreach ($subDeptValues as $value) {
    echo "- {$value->sub_department_id}\n";
}

// Look for any tables that might contain sub-department information
echo "\n=== All Tables ===\n";
$tables = DB::select('SHOW TABLES');
foreach ($tables as $table) {
    $tableName = array_values((array)$table)[0];
    if (stripos($tableName, 'sub') !== false || stripos($tableName, 'dept') !== false) {
        echo "- {$tableName}\n";
    }
}

echo "\nDone!\n";
