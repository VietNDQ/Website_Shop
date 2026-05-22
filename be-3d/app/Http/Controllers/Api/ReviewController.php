<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DanhGia;
use App\Models\AnhDanhGia;
use App\Models\ChiTietDonHang;
use App\Models\BienTheSanPham;
use App\Models\LichSuTrangThaiDonHang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ReviewController extends Controller
{
    /**
     * 1. Khách hàng gửi đánh giá sản phẩm
     */
    public function submitReview(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_chi_tiet_don_hang' => 'required|exists:chi_tiet_don_hang,id',
            'so_sao' => 'required|integer|min:1|max:5',
            'binh_luan' => 'nullable|string|max:1000',
            'anh_danh_gia' => 'nullable|array|max:5',
            'anh_danh_gia.*' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120', // Tối đa 5MB/ảnh
        ], [
            'id_chi_tiet_don_hang.required' => 'Chi tiết đơn hàng là bắt buộc.',
            'id_chi_tiet_don_hang.exists' => 'Chi tiết đơn hàng không tồn tại.',
            'so_sao.required' => 'Số sao đánh giá là bắt buộc.',
            'so_sao.min' => 'Số sao tối thiểu là 1.',
            'so_sao.max' => 'Số sao tối đa là 5.',
            'anh_danh_gia.max' => 'Bạn chỉ được gửi tối đa 5 hình ảnh.',
            'anh_danh_gia.*.image' => 'Tệp gửi lên phải là hình ảnh.',
            'anh_danh_gia.*.max' => 'Mỗi hình ảnh chỉ được tải lên tối đa 5MB.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 0,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $user = Auth::user();
        if (!$user) {
            return response()->json(['status' => 0, 'message' => 'Vui lòng đăng nhập.'], 401);
        }

        // Kiểm tra chi tiết đơn hàng và quyền sở hữu
        $detail = ChiTietDonHang::with('donHang')->find($request->id_chi_tiet_don_hang);
        if (!$detail) {
            return response()->json(['status' => 0, 'message' => 'Không tìm thấy chi tiết đơn hàng.'], 404);
        }

        $order = $detail->donHang;
        if ($order->id_nguoi_dung !== $user->id) {
            return response()->json(['status' => 0, 'message' => 'Bạn không sở hữu đơn hàng này.'], 403);
        }

        // Quyền đánh giá (Verified Purchase): Đơn hàng trạng thái "da_giao"
        if ($order->trang_thai !== 'da_giao') {
            return response()->json(['status' => 0, 'message' => 'Bạn chỉ có thể đánh giá khi đơn hàng đã giao thành công.'], 400);
        }

        // Giới hạn số lần: Mỗi sản phẩm trong đơn hàng chỉ được đánh giá 1 lần duy nhất
        $alreadyReviewed = DanhGia::where('id_chi_tiet_don_hang', $detail->id)->exists();
        if ($alreadyReviewed) {
            return response()->json(['status' => 0, 'message' => 'Sản phẩm trong đơn hàng này đã được đánh giá rồi.'], 400);
        }

        // Thời hạn đánh giá: trong vòng 30 ngày kể từ khi nhận hàng (da_giao)
        $deliveryHistory = LichSuTrangThaiDonHang::where('id_don_hang', $order->id)
            ->where('trang_thai', 'da_giao')
            ->orderByDesc('tao_luc')
            ->first();
        $deliveredAt = $deliveryHistory ? $deliveryHistory->tao_luc : $order->cap_nhat_luc;

        if ($deliveredAt) {
            $deliveredAt = \Illuminate\Support\Carbon::parse($deliveredAt);
            if ($deliveredAt->lt(now()->subDays(30))) {
                return response()->json(['status' => 0, 'message' => 'Thời hạn đánh giá cho sản phẩm này đã hết (tối đa 30 ngày từ khi nhận hàng).'], 400);
            }
        }

        // Lấy sản phẩm cha từ biến thể
        $variant = BienTheSanPham::find($detail->id_bien_the);
        if (!$variant) {
            return response()->json(['status' => 0, 'message' => 'Sản phẩm hoặc biến thể này không còn tồn tại trên hệ thống.'], 400);
        }

        // Bộ lọc từ ngữ thô tục đơn giản (chống spam/chửi thề)
        $binhLuan = $request->binh_luan;
        if ($binhLuan) {
            $badWords = ['vcl', 'clm', 'dm', 'đm', 'dcm', 'đcm', 'fuck', 'shit', 'chó', 'cho', 'dien', 'điên', 'khùng', 'khung'];
            $replacements = array_fill(0, count($badWords), '***');
            $binhLuan = str_ireplace($badWords, $replacements, $binhLuan);
        }

        DB::beginTransaction();
        try {
            // Lưu đánh giá. Mặc định là hiển thị công khai 'hien_thi'
            $review = DanhGia::create([
                'id_nguoi_dung' => $user->id,
                'id_san_pham' => $variant->id_san_pham,
                'id_bien_the' => $detail->id_bien_the,
                'id_chi_tiet_don_hang' => $detail->id,
                'so_sao' => $request->so_sao,
                'binh_luan' => $binhLuan,
                'trang_thai' => 'hien_thi' // Mặc định hiển thị ngay
            ]);

            // Xử lý ảnh đính kèm
            if ($request->hasFile('anh_danh_gia')) {
                foreach ($request->file('anh_danh_gia') as $image) {
                    $fileName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                    $image->move(public_path('uploads/reviews'), $fileName);
                    $imagePath = 'uploads/reviews/' . $fileName;

                    AnhDanhGia::create([
                        'id_danh_gia' => $review->id,
                        'duong_dan_anh' => $imagePath
                    ]);
                }
            }

            // Ghi nhật ký hoạt động
            \App\Models\NhatKyHoatDong::create([
                'hanh_dong' => 'Đánh giá sản phẩm',
                'mo_ta' => 'Khách hàng #' . $user->id . ' đánh giá ' . $request->so_sao . ' sao cho sản phẩm ID ' . $variant->id_san_pham,
                'loai_doi_tuong' => 'danh_gia',
                'id_doi_tuong' => $review->id,
            ]);

            DB::commit();

            return response()->json([
                'status' => 1,
                'message' => 'Đánh giá sản phẩm thành công!',
                'data' => $review->load('anhs')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 0,
                'message' => 'Có lỗi xảy ra khi gửi đánh giá: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 2. Lấy danh sách đánh giá công khai ở trang chi tiết sản phẩm
     */
    public function getProductReviews(Request $request, $productId)
    {
        // 2.1 Tính toán tổng quan điểm số
        $allReviews = DanhGia::where('id_san_pham', $productId)
            ->where('trang_thai', 'hien_thi')
            ->get();

        $totalCount = $allReviews->count();
        $averageRating = $totalCount > 0 ? round($allReviews->avg('so_sao'), 1) : 0;

        $starCounts = [
            5 => $allReviews->where('so_sao', 5)->count(),
            4 => $allReviews->where('so_sao', 4)->count(),
            3 => $allReviews->where('so_sao', 3)->count(),
            2 => $allReviews->where('so_sao', 2)->count(),
            1 => $allReviews->where('so_sao', 1)->count(),
        ];

        // 2.2 Xây dựng Query lấy danh sách đánh giá
        $query = DanhGia::with(['nguoiDung', 'anhs', 'chiTietDonHang'])
            ->where('id_san_pham', $productId)
            ->where('trang_thai', 'hien_thi');

        // Bộ lọc sao
        if ($request->filled('sao')) {
            $query->where('so_sao', $request->sao);
        }

        // Bộ lọc có hình ảnh
        if ($request->co_anh == 1) {
            $query->whereHas('anhs');
        }

        // Bộ lọc có bình luận
        if ($request->co_binh_luan == 1) {
            $query->whereNotNull('binh_luan')->where('binh_luan', '!=', '');
        }

        $reviews = $query->latest('tao_luc')->paginate(10);

        // Biến đổi dữ liệu đầu ra (Ẩn danh tên, định dạng ngày tháng)
        $reviews->getCollection()->transform(function ($rv) {
            $variantName = $rv->chiTietDonHang ? $rv->chiTietDonHang->ten_bien_the_luc_mua : 'Sản phẩm gốc';
            return [
                'id' => $rv->id,
                'name' => $this->maskName($rv->nguoiDung->ho_ten ?? 'Khách hàng'),
                'rating' => $rv->so_sao,
                'date' => $rv->tao_luc ? $rv->tao_luc->format('d/m/Y') : 'N/A',
                'body' => $rv->binh_luan,
                'variant' => $variantName,
                'images' => $rv->anhs->pluck('duong_dan_anh')->map(function ($path) {
                    return '/' . $path;
                }),
                'reply' => $rv->phan_hoi_admin,
            ];
        });

        // Đếm tổng số lượng cho bộ lọc nhanh
        $countWithImages = DanhGia::where('id_san_pham', $productId)
            ->where('trang_thai', 'hien_thi')
            ->whereHas('anhs')
            ->count();

        $countWithComments = DanhGia::where('id_san_pham', $productId)
            ->where('trang_thai', 'hien_thi')
            ->whereNotNull('binh_luan')
            ->where('binh_luan', '!=', '')
            ->count();

        return response()->json([
            'status' => 1,
            'data' => [
                'summary' => [
                    'average' => $averageRating,
                    'total' => $totalCount,
                    'stars' => $starCounts,
                    'count_with_images' => $countWithImages,
                    'count_with_comments' => $countWithComments,
                ],
                'reviews' => $reviews
            ]
        ]);
    }

    /**
     * 3. Admin lấy toàn bộ đánh giá để quản lý
     */
    public function getReviewsAdmin(Request $request)
    {
        $query = DanhGia::with(['nguoiDung', 'sanPham', 'anhs', 'chiTietDonHang']);

        // Tìm kiếm theo tên khách hoặc tên sản phẩm
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('nguoiDung', function ($qu) use ($search) {
                    $qu->where('ho_ten', 'like', "%{$search}%");
                })->orWhereHas('sanPham', function ($qp) use ($search) {
                    $qp->where('ten_san_pham', 'like', "%{$search}%");
                });
            });
        }

        // Lọc theo sao
        if ($request->filled('sao')) {
            $query->where('so_sao', $request->sao);
        }

        // Lọc theo trạng thái
        if ($request->filled('trang_thai')) {
            $query->where('trang_thai', $request->trang_thai);
        }

        $limit = $request->input('limit', 20);
        $reviews = $query->latest('tao_luc')->paginate($limit);

        // Format dữ liệu trả về cho admin
        $reviews->getCollection()->transform(function ($rv) {
            $statusLabels = [
                'cho_duyet' => 'Chờ duyệt',
                'hien_thi' => 'Đã duyệt', // hoặc Hiển thị
                'an' => 'Đã ẩn'
            ];

            return [
                'id' => $rv->id,
                'customer' => $rv->nguoiDung->ho_ten ?? 'Ẩn danh',
                'product' => $rv->sanPham->ten_san_pham ?? 'N/A',
                'variant' => $rv->chiTietDonHang ? $rv->chiTietDonHang->ten_bien_the_luc_mua : '',
                'stars' => $rv->so_sao,
                'content' => $rv->binh_luan,
                'date' => $rv->tao_luc ? $rv->tao_luc->format('d/m/Y H:i') : 'N/A',
                'status' => $rv->trang_thai,
                'statusLabel' => $statusLabels[$rv->trang_thai] ?? 'Không xác định',
                'images' => $rv->anhs->pluck('duong_dan_anh')->map(function ($path) {
                    return '/' . $path;
                }),
                'reply' => $rv->phan_hoi_admin,
            ];
        });

        // Tính toán các thông số tổng quan phục vụ thống kê (DB aggregate)
        $total = DanhGia::count();
        $average = round(DanhGia::avg('so_sao') ?? 0, 1);
        $pending = DanhGia::where('trang_thai', 'cho_duyet')->count();
        $positive = DanhGia::where('so_sao', '>=', 4)->count();
        $satisfactionRate = $total > 0 ? round(($positive / $total) * 100) : 100;

        $stats = [
            'total' => $total,
            'average' => number_format($average, 1),
            'pending' => $pending,
            'satisfactionRate' => $satisfactionRate,
        ];

        return response()->json([
            'status' => 1,
            'data' => $reviews,
            'stats' => $stats
        ]);
    }

    /**
     * 4. Admin ẩn/hiện hoặc duyệt đánh giá
     */
    public function toggleVisibility(Request $request, $id)
    {
        $request->validate([
            'trang_thai' => 'required|in:cho_duyet,hien_thi,an'
        ]);

        $review = DanhGia::find($id);
        if (!$review) {
            return response()->json(['status' => 0, 'message' => 'Không tìm thấy đánh giá.'], 404);
        }

        $oldStatus = $review->trang_thai;
        $review->trang_thai = $request->trang_thai;
        $review->save();

        // Ghi nhật ký hoạt động
        \App\Models\NhatKyHoatDong::create([
            'hanh_dong' => 'Duyệt đánh giá',
            'mo_ta' => 'Cập nhật trạng thái đánh giá #' . $review->id . ' từ ' . $oldStatus . ' sang ' . $request->trang_thai,
            'loai_doi_tuong' => 'danh_gia',
            'id_doi_tuong' => $review->id,
        ]);

        return response()->json([
            'status' => 1,
            'message' => 'Cập nhật trạng thái đánh giá thành công.',
            'trang_thai' => $review->trang_thai
        ]);
    }

    /**
     * 5. Admin phản hồi đánh giá
     */
    public function replyReview(Request $request, $id)
    {
        $request->validate([
            'phan_hoi_admin' => 'required|string|max:1000'
        ], [
            'phan_hoi_admin.required' => 'Nội dung phản hồi không được để trống.',
            'phan_hoi_admin.max' => 'Nội dung phản hồi tối đa 1000 ký tự.'
        ]);

        $review = DanhGia::find($id);
        if (!$review) {
            return response()->json(['status' => 0, 'message' => 'Không tìm thấy đánh giá.'], 404);
        }

        $review->phan_hoi_admin = $request->phan_hoi_admin;
        $review->save();

        // Ghi nhật ký hoạt động
        \App\Models\NhatKyHoatDong::create([
            'hanh_dong' => 'Phản hồi đánh giá',
            'mo_ta' => 'Admin phản hồi đánh giá #' . $review->id,
            'loai_doi_tuong' => 'danh_gia',
            'id_doi_tuong' => $review->id,
        ]);

        return response()->json([
            'status' => 1,
            'message' => 'Phản hồi đánh giá thành công.',
            'data' => $review->phan_hoi_admin
        ]);
    }

    /**
     * Hàm bổ trợ ẩn danh tên
     */
    private function maskName($name)
    {
        if (empty($name)) return 'K***h';
        $parts = explode(' ', trim($name));
        if (count($parts) === 1) {
            $single = $parts[0];
            $len = mb_strlen($single);
            if ($len <= 2) return $single;
            return mb_substr($single, 0, 1) . str_repeat('*', $len - 2) . mb_substr($single, -1);
        }
        $firstLetter = mb_substr($parts[0], 0, 1);
        $lastWord = end($parts);
        $lastLetter = mb_substr($lastWord, -1);
        return $firstLetter . '***' . $lastLetter;
    }
}
