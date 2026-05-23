<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DanhMuc;
use App\Models\SanPham;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChatbotController extends Controller
{
    public function chat(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
            'history' => 'nullable|array',
            'history.*.role' => 'required|string|in:user,model,assistant',
            'history.*.text' => 'required|string',
        ]);

        $apiKey = env('GEMINI_API_KEY');
        if (!$apiKey) {
            return response()->json([
                'status' => false,
                'message' => 'GEMINI_API_KEY chưa được cấu hình ở Backend.'
            ], 500);
        }

        try {
            // 1. Lấy thông tin ngữ cảnh danh mục & sản phẩm nổi bật từ CSDL
            $categories = DanhMuc::where('trang_thai', 1)->pluck('ten_danh_muc')->toArray();
            $products = SanPham::with('danhMuc')
                ->whereIn('tinh_trang', [0, 1])
                ->limit(20)
                ->get();

            $productsContext = [];
            foreach ($products as $p) {
                $statusStr = $p->tinh_trang === 1 ? "Còn hàng" : "Hết hàng";
                $catName = $p->danhMuc ? $p->danhMuc->ten_danh_muc : "Mô hình";
                $priceStr = number_format($p->gia_co_ban, 0, ',', '.') . " đ";
                $productsContext[] = "{$p->ten_san_pham} (Danh mục: {$catName}, Giá: {$priceStr}, Tình trạng: {$statusStr})";
            }

            // Mảng lịch sử chat từ Vue 3 gửi lên
            $history = $request->input('history', []);

            // 2. Chuyển tiếp request sang Python Chatbot Service
            $chatbotServiceUrl = env('CHATBOT_SERVICE_URL', 'http://127.0.0.1:8001/chat');

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post($chatbotServiceUrl, [
                'message' => $request->input('message'),
                'history' => $history,
                'categories' => $categories,
                'products' => $productsContext,
            ]);

            if ($response->failed()) {
                Log::error('Python Chatbot Service Error: ' . $response->body());
                return response()->json([
                    'status' => false,
                    'message' => 'Lỗi kết nối với dịch vụ Chatbot AI ở Python.'
                ], 502);
            }

            return response()->json($response->json());

        } catch (\Exception $e) {
            Log::error('ChatbotController Error: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Đã xảy ra lỗi hệ thống tại Laravel: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getOrderStatusByCode($code)
    {
        try {
            $order = \App\Models\DonHang::with(['chiTiets', 'thanhToan'])
                ->where('ma_don_hang', trim($code))
                ->first();

            if (!$order) {
                return response()->json([
                    'status' => false,
                    'message' => "Không tìm thấy đơn hàng nào có mã {$code}."
                ], 404);
            }

            $statusMap = [
                'cho_xu_ly' => 'Chờ xử lý (Shop đang chuẩn bị hàng)',
                'dang_xu_ly' => 'Đang xử lý',
                'dang_giao' => 'Đang giao hàng (Đã giao cho đơn vị vận chuyển)',
                'da_giao' => 'Đã giao hàng thành công',
                'da_huy' => 'Đã hủy',
                'tra_hang' => 'Yêu cầu trả hàng/hoàn tiền',
            ];

            $statusText = $statusMap[$order->trang_thai] ?? $order->trang_thai;
            
            $paymentStatus = $order->thanhToan ? ($order->thanhToan->trang_thai === 'da_thanh_toan' ? 'Đã thanh toán' : 'Chưa thanh toán') : 'Chưa xác định';
            
            $ngayDat = $order->created_at ? $order->created_at->format('d/m/Y H:i') : ($order->tao_luc ? date('d/m/Y H:i', strtotime($order->tao_luc)) : 'Chưa rõ');

            return response()->json([
                'status' => true,
                'ma_don_hang' => $order->ma_don_hang,
                'trang_thai' => $statusText,
                'ngay_dat' => $ngayDat,
                'tong_thanh_toan' => number_format($order->tong_thanh_toan, 0, ',', '.') . ' đ',
                'trang_thai_thanh_toan' => $paymentStatus,
                'dia_chi_giao' => $order->dia_chi_giao_hang['dia_chi'] ?? 'Chưa rõ',
            ]);
        } catch (\Exception $e) {
            Log::error('ChatbotController getOrderStatusByCode Error: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Lỗi máy chủ khi tra cứu trạng thái đơn hàng.'
            ], 500);
        }
    }
}
