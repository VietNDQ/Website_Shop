<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$variants = App\Models\BienTheSanPham::with('sanPham')->take(12)->get();
foreach ($variants as $v) {
    echo $v->sanPham->ten_san_pham . ' (' . implode(', ', $v->thuoc_tinh) . ') => ' . $v->hinh_anh . PHP_EOL;
}
