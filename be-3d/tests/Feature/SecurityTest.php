<?php

use App\Models\NguoiDung;
use App\Models\NhatKyHoatDong;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

uses(RefreshDatabase::class);

test('user cannot have more than 2 active tokens (session limit)', function () {
    $user = NguoiDung::create([
        'ho_ten' => 'Nguyen Van A',
        'email' => 'vana@gmail.com',
        'mat_khau' => bcrypt('password123'),
        'vai_tro' => 3,
        'dang_hoat_dong' => true,
    ]);

    // Create 3 tokens
    $token1 = $user->createToken('device_1');
    // Simulate slight time difference if needed, but the logic sorts by created_at or id,
    // and since Sanctum tokens have primary key auto-increment, sorting by created_at or id works.
    // In our model: $this->tokens()->orderBy('created_at', 'asc')->limit($tokensCount - 1)->get()->each->delete();
    // Wait, let's sleep a tiny bit if created_at is equal?
    // In SQLite or MySQL, timestamps might be identical if created in the same second, so order by created_at, asc might not be stable,
    // but typically Sanctum tokens' auto-increment ID is also order-coherent with created_at.
    // Let's check: can we add orderBy('id', 'asc') to be extra safe, or does created_at work? Let's check how NguoiDung.php implements it.
    // In NguoiDung.php, line 22: $this->tokens()->orderBy('created_at', 'asc')->limit($tokensCount - 1)->get()->each->delete();
    // Since created_at might be the same, let's make sure the test runs successfully. If they have the same created_at, SQLite will return them in insertion order.
    // Let's create tokens with a small delay or check.
    $token2 = $user->createToken('device_2');

    expect($user->tokens()->count())->toBe(2);

    $token3 = $user->createToken('device_3');

    // Only 2 tokens should remain, and device_1 (oldest) should be deleted
    expect($user->tokens()->count())->toBe(2);
    
    // Assert that the first token was deleted
    $token1Model = $user->tokens()->where('name', 'device_1')->first();
    expect($token1Model)->toBeNull();
    
    // Assert that device_2 and device_3 remain
    expect($user->tokens()->where('name', 'device_2')->first())->not->toBeNull();
    expect($user->tokens()->where('name', 'device_3')->first())->not->toBeNull();
});

test('activity logger saves logs to database and files', function () {
    $user = NguoiDung::create([
        'ho_ten' => 'Nguyen Van B',
        'email' => 'vanb@gmail.com',
        'mat_khau' => bcrypt('password123'),
        'vai_tro' => 3,
        'dang_hoat_dong' => true,
    ]);

    Log::shouldReceive('info')
        ->once()
        ->withArgs(function ($message) {
            return str_contains($message, 'ActivityLog') && str_contains($message, 'Test Action Logged');
        });

    $log = NhatKyHoatDong::ghiLog($user->id, $user->ho_ten, 'Test Action Logged', '#ff0000');

    expect($log)->not->toBeNull();
    
    $this->assertDatabaseHas('nhat_ky_hoat_dong', [
        'id_nguoi_dung' => $user->id,
        'ten_nguoi_dung' => 'Nguyen Van B',
        'mau_sac' => '#ff0000',
    ]);
});

test('system logs 500 exceptions and reports to telegram when configured', function () {
    Http::fake();

    // Set temporary env values
    putenv('TELEGRAM_BOT_TOKEN=test-token');
    putenv('TELEGRAM_CHAT_ID=test-chat-id');

    // Define a route that throws an exception for testing
    Route::get('/test-500-error', function () {
        throw new \RuntimeException("Test System Error 500");
    });

    $response = $this->get('/test-500-error');

    // The response status should be 500
    $response->assertStatus(500);

    // Verify Telegram API was called
    Http::assertSent(function (\Illuminate\Http\Client\Request $request) {
        return str_contains($request->url(), 'https://api.telegram.org/bottest-token/sendMessage')
            && $request['chat_id'] === 'test-chat-id'
            && str_contains($request['text'], '🚨 *LỖI HỆ THỐNG PHÁT HIỆN* 🚨')
            && str_contains($request['text'], 'Test System Error 500');
    });

    // Clean up env
    putenv('TELEGRAM_BOT_TOKEN');
    putenv('TELEGRAM_CHAT_ID');
});

