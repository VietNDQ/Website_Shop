<?php
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\DonHang;
use App\Models\SanPham;

$orders = DonHang::count();
$products = SanPham::count();
echo "Orders: $orders, Products: $products\n";
