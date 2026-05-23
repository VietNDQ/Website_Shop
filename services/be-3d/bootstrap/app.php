<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Tự động gửi cảnh báo Telegram khi có lỗi hệ thống nghiêm trọng (lỗi 500)
        $exceptions->report(function (\Throwable $e) {
            // Loại bỏ các lỗi client không cần cảnh báo
            if ($e instanceof \Illuminate\Validation\ValidationException || 
                $e instanceof \Illuminate\Auth\Access\AuthorizationException ||
                $e instanceof \Illuminate\Auth\AuthenticationException ||
                $e instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException ||
                $e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                return;
            }

            $token = env('TELEGRAM_BOT_TOKEN');
            $chatId = env('TELEGRAM_CHAT_ID');

            if ($token && $chatId) {
                try {
                    $message = "🚨 *LỖI HỆ THỐNG PHÁT HIỆN* 🚨\n\n"
                             . "*Nội dung:* " . $e->getMessage() . "\n"
                             . "*File:* " . $e->getFile() . " (Dòng " . $e->getLine() . ")\n"
                             . "*API URL:* " . request()->fullUrl() . "\n"
                             . "*IP Client:* " . request()->ip() . "\n"
                             . "*User Agent:* " . request()->userAgent();

                    \Illuminate\Support\Facades\Http::post("https://api.telegram.org/bot{$token}/sendMessage", [
                        'chat_id' => $chatId,
                        'text' => $message,
                        'parse_mode' => 'Markdown',
                    ]);
                } catch (\Exception $ex) {
                    \Illuminate\Support\Facades\Log::error('Không thể gửi báo lỗi Telegram: ' . $ex->getMessage());
                }
            }
        });
    })->create();
