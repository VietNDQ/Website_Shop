<?php
require_once __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    DB::beginTransaction();

    // 1. Chuyển đổi nhóm Màu sắc
    $affected1 = DB::update("
        UPDATE bien_the_san_pham
        SET thuoc_tinh = JSON_REMOVE(JSON_SET(thuoc_tinh, '$.mau_sac', JSON_EXTRACT(thuoc_tinh, '$.Color')), '$.Color')
        WHERE JSON_EXTRACT(thuoc_tinh, '$.Color') IS NOT NULL
    ");
    
    $affected2 = DB::update("
        UPDATE bien_the_san_pham
        SET thuoc_tinh = JSON_REMOVE(JSON_SET(thuoc_tinh, '$.mau_sac', JSON_EXTRACT(thuoc_tinh, '$.color')), '$.color')
        WHERE JSON_EXTRACT(thuoc_tinh, '$.color') IS NOT NULL
    ");
    
    $affected3 = DB::update("
        UPDATE bien_the_san_pham
        SET thuoc_tinh = JSON_REMOVE(JSON_SET(thuoc_tinh, '$.mau_sac', JSON_EXTRACT(thuoc_tinh, '$.\"Màu sắc\"')), '$.\"Màu sắc\"')
        WHERE JSON_EXTRACT(thuoc_tinh, '$.\"Màu sắc\"') IS NOT NULL
    ");

    // 2. Chuyển đổi nhóm Kích thước
    $affected4 = DB::update("
        UPDATE bien_the_san_pham
        SET thuoc_tinh = JSON_REMOVE(JSON_SET(thuoc_tinh, '$.kich_thuoc', JSON_EXTRACT(thuoc_tinh, '$.Size')), '$.Size')
        WHERE JSON_EXTRACT(thuoc_tinh, '$.Size') IS NOT NULL
    ");

    $affected5 = DB::update("
        UPDATE bien_the_san_pham
        SET thuoc_tinh = JSON_REMOVE(JSON_SET(thuoc_tinh, '$.kich_thuoc', JSON_EXTRACT(thuoc_tinh, '$.size')), '$.size')
        WHERE JSON_EXTRACT(thuoc_tinh, '$.size') IS NOT NULL
    ");

    $affected6 = DB::update("
        UPDATE bien_the_san_pham
        SET thuoc_tinh = JSON_REMOVE(JSON_SET(thuoc_tinh, '$.kich_thuoc', JSON_EXTRACT(thuoc_tinh, '$.\"Kích thước\"')), '$.\"Kích thước\"')
        WHERE JSON_EXTRACT(thuoc_tinh, '$.\"Kích thước\"') IS NOT NULL
    ");

    DB::commit();
    echo "SQL update successful!\n";
    echo "Color updates: $affected1, $affected2, $affected3 rows.\n";
    echo "Size updates: $affected4, $affected5, $affected6 rows.\n";
} catch (\Exception $e) {
    DB::rollBack();
    echo "Error: " . $e->getMessage() . "\n";
}
