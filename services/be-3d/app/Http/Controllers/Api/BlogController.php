<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BaiViet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BlogController extends Controller
{
    /**
     * Vietnamese slug generator helper
     */
    private function generateSlug($title, $excludeId = 0)
    {
        $unicode = [
            'a' => 'á|à|ả|ã|ạ|ă|ắ|ằ|ẳ|ẵ|ặ|â|ấ|ầ|ẩ|ẫ|ậ',
            'd' => 'đ',
            'e' => 'é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ',
            'i' => 'í|ì|ỉ|ĩ|ị',
            'o' => 'ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ',
            'u' => 'ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự',
            'y' => 'ý|ỳ|ỷ|ỹ|ỵ',
            'A' => 'Á|À|Ả|Ã|Ạ|Ă|Ắ|Ằ|Ẳ|Ẵ|Ặ|Â|Ấ|Ầ|Ẩ|Ẫ|Ậ',
            'D' => 'Đ',
            'E' => 'É|È|Ẻ|Ẽ|Ẹ|Ê|Ế|Ề|Ể|Ễ|Ệ',
            'I' => 'Í|Ì|Ỉ|Ĩ|Ị',
            'O' => 'Ó|Ò|Ỏ|Õ|Ọ|Ô|Ố|Ồ|Ổ|Ỗ|Ộ|Ơ|Ớ|Ờ|Ở|Ỡ|Ợ',
            'U' => 'Ú|Ù|Ủ|Ũ|Ụ|Ư|Ứ|Ừ|Ử|Ữ|Ự',
            'Y' => 'Ý|Ỳ|Ỷ|Ỹ|Ỵ',
        ];
        foreach ($unicode as $nonUnicode => $uni) {
            $title = preg_replace("/($uni)/i", $nonUnicode, $title);
        }
        $title = strtolower($title);
        $title = preg_replace('/[^a-z0-9\-]/', '-', $title);
        $title = preg_replace('/-+/', '-', $title);
        $title = trim($title, '-');

        $slug = $title;
        $originalSlug = $slug;
        $count = 1;
        
        while (BaiViet::where('slug', $slug)->where('id', '!=', $excludeId)->exists()) {
            $slug = $originalSlug . '-' . $count;
            $count++;
        }

        return $slug;
    }

    /**
     * Client: List published blog posts
     */
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $loai = trim((string) $request->query('loai', ''));
        $limit = (int) $request->query('limit', 9);

        $query = BaiViet::with('nguoiDang:id,ho_ten,anh_dai_dien')
            ->where('trang_thai', true);

        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('tieu_de', 'like', "%{$q}%")
                    ->orWhere('tom_tat', 'like', "%{$q}%")
                    ->orWhere('noi_dung', 'like', "%{$q}%");
            });
        }

        if ($loai !== '') {
            $query->where('loai', $loai);
        }

        $posts = $query->orderBy('tao_luc', 'desc')->paginate($limit);

        return response()->json([
            'status' => true,
            'data' => $posts
        ]);
    }

    /**
     * Client: Show details of a blog post
     */
    public function show($id_or_slug)
    {
        $post = BaiViet::with('nguoiDang:id,ho_ten,anh_dai_dien')
            ->where('trang_thai', true)
            ->where(function ($query) use ($id_or_slug) {
                $query->where('id', $id_or_slug)
                      ->orWhere('slug', $id_or_slug);
            })
            ->first();

        if (!$post) {
            return response()->json([
                'status' => false,
                'message' => 'Bài viết không tồn tại hoặc đã bị ẩn.'
            ], 404);
        }

        // Increment views
        $post->increment('luot_xem');

        // Related posts
        $related = BaiViet::where('trang_thai', true)
            ->where('loai', $post->loai)
            ->where('id', '!=', $post->id)
            ->orderBy('tao_luc', 'desc')
            ->limit(5)
            ->get(['id', 'tieu_de', 'slug', 'anh_dai_dien', 'tao_luc', 'luot_xem']);

        return response()->json([
            'status' => true,
            'post' => $post,
            'related' => $related
        ]);
    }

    /**
     * Admin: List all blog posts (including drafts)
     */
    public function adminIndex(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $loai = trim((string) $request->query('loai', ''));
        $trangThai = $request->query('trang_thai', '');
        $limit = (int) $request->query('limit', 15);

        $query = BaiViet::with('nguoiDang:id,ho_ten');

        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('tieu_de', 'like', "%{$q}%")
                    ->orWhere('tom_tat', 'like', "%{$q}%");
            });
        }

        if ($loai !== '') {
            $query->where('loai', $loai);
        }

        if ($trangThai !== '') {
            $query->where('trang_thai', filter_var($trangThai, FILTER_VALIDATE_BOOLEAN));
        }

        $posts = $query->orderBy('tao_luc', 'desc')->paginate($limit);

        return response()->json([
            'status' => true,
            'data' => $posts
        ]);
    }

    /**
     * Admin: Create a new blog post
     */
    public function store(Request $request)
    {
        $request->validate([
            'tieu_de' => 'required|string|max:255',
            'noi_dung' => 'required|string',
            'loai' => 'required|string|in:tin_tuc,huong_dan,danh_gia',
            'trang_thai' => 'required|boolean',
            'tom_tat' => 'nullable|string|max:500',
            'anh_dai_dien_file' => 'nullable|image|max:2048'
        ], [
            'tieu_de.required' => 'Tiêu đề không được để trống.',
            'noi_dung.required' => 'Nội dung không được để trống.',
            'loai.required' => 'Vui lòng chọn thể loại bài viết.',
            'loai.in' => 'Thể loại bài viết không hợp lệ.'
        ]);

        $imagePath = null;
        if ($request->hasFile('anh_dai_dien_file')) {
            $file = $request->file('anh_dai_dien_file');
            $path = $file->store('uploads/blog', 'public');
            $imagePath = '/storage/' . $path;
        }

        $slug = $this->generateSlug($request->tieu_de);

        $post = BaiViet::create([
            'tieu_de' => $request->tieu_de,
            'slug' => $slug,
            'anh_dai_dien' => $imagePath,
            'tom_tat' => $request->tom_tat,
            'noi_dung' => $request->noi_dung,
            'loai' => $request->loai,
            'trang_thai' => $request->trang_thai,
            'id_nguoi_dang' => Auth::user()->id,
        ]);

        // activity log
        \App\Models\NhatKyHoatDong::ghiLog(
            Auth::user()->id,
            Auth::user()->ho_ten,
            "Đã thêm bài viết mới: {$post->tieu_de}",
            '#10b981'
        );

        return response()->json([
            'status' => true,
            'message' => 'Tạo bài viết mới thành công.',
            'data' => $post
        ], 201);
    }

    /**
     * Admin: Update a blog post
     */
    public function update(Request $request, $id)
    {
        $post = BaiViet::findOrFail($id);

        $request->validate([
            'tieu_de' => 'required|string|max:255',
            'noi_dung' => 'required|string',
            'loai' => 'required|string|in:tin_tuc,huong_dan,danh_gia',
            'trang_thai' => 'required|boolean',
            'tom_tat' => 'nullable|string|max:500',
            'anh_dai_dien_file' => 'nullable|image|max:2048',
            'xoa_anh_dai_dien' => 'nullable|boolean'
        ], [
            'tieu_de.required' => 'Tiêu đề không được để trống.',
            'noi_dung.required' => 'Nội dung không được để trống.',
            'loai.required' => 'Vui lòng chọn thể loại bài viết.',
            'loai.in' => 'Thể loại bài viết không hợp lệ.'
        ]);

        $imagePath = $post->anh_dai_dien;

        // Delete image if requested or if new one is uploaded
        if ($request->xoa_anh_dai_dien || $request->hasFile('anh_dai_dien_file')) {
            if ($post->anh_dai_dien) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $post->anh_dai_dien));
                $imagePath = null;
            }
        }

        // Upload new image
        if ($request->hasFile('anh_dai_dien_file')) {
            $file = $request->file('anh_dai_dien_file');
            $path = $file->store('uploads/blog', 'public');
            $imagePath = '/storage/' . $path;
        }

        // Update slug if title changes
        $slug = $post->slug;
        if ($request->tieu_de !== $post->tieu_de) {
            $slug = $this->generateSlug($request->tieu_de, $post->id);
        }

        $post->update([
            'tieu_de' => $request->tieu_de,
            'slug' => $slug,
            'anh_dai_dien' => $imagePath,
            'tom_tat' => $request->tom_tat,
            'noi_dung' => $request->noi_dung,
            'loai' => $request->loai,
            'trang_thai' => $request->trang_thai,
        ]);

        // activity log
        \App\Models\NhatKyHoatDong::ghiLog(
            Auth::user()->id,
            Auth::user()->ho_ten,
            "Đã cập nhật bài viết: {$post->tieu_de}",
            '#3b82f6'
        );

        return response()->json([
            'status' => true,
            'message' => 'Cập nhật bài viết thành công.',
            'data' => $post
        ]);
    }

    /**
     * Admin: Delete a blog post
     */
    public function destroy($id)
    {
        $post = BaiViet::findOrFail($id);

        // Delete image from disk if exists
        if ($post->anh_dai_dien) {
            Storage::disk('public')->delete(str_replace('/storage/', '', $post->anh_dai_dien));
        }

        $postTitle = $post->tieu_de;
        $post->delete();

        // activity log
        \App\Models\NhatKyHoatDong::ghiLog(
            Auth::user()->id,
            Auth::user()->ho_ten,
            "Đã xóa bài viết: {$postTitle}",
            '#ef4444'
        );

        return response()->json([
            'status' => true,
            'message' => 'Đã xóa bài viết thành công.'
        ]);
    }
}
