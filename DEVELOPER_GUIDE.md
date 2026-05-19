Tài liệu này hướng dẫn chi tiết cách viết **API, Request, Function (Backend Laravel)** và cách **viết & tổ chức logic xử lý (Frontend Vue 3)** theo đúng chuẩn của dự án hiện tại.

---

## PHẦN 1: HƯỚNG DẪN LẬP TRÌNH BACKEND (LARAVEL)

Backend của dự án được xây dựng bằng Laravel, sử dụng **Sanctum** để xác thực Token và hệ thống **Phân quyền dựa trên chức năng (Functional Authorization)** lưu trong cơ sở dữ liệu.

### 1. Cách viết API Route (`backend/routes/api.php`)

Các API route trong dự án được tổ chức và phân chia theo vai trò (Admin/Nhân viên và Khách hàng) và được bảo vệ bởi Middleware tương ứng.

#### Quy tắc thiết kế Route:
- Sử dụng danh từ và định dạng **kebab-case** cho URL (ví dụ: `/admin/danh-muc/create`, `/khach-hang/dia-chi/get-data`).
- Các API quản lý của Admin phải nằm trong nhóm Middleware `NhanVienMiddleware` (hoặc alias tương ứng).
- Các API của khách hàng yêu cầu đăng nhập phải gắn Middleware `khachHangMiddle`.
- Các API công khai (Public) cho trang chủ không sử dụng middleware xác thực.

#### Ví dụ khai báo trong `routes/api.php`:
```php
use App\Http\Controllers\DanhMucController;
use App\Http\Middleware\NhanVienMiddleware;

// 1. Route không yêu cầu đăng nhập (Public)
Route::get('/home-page/danh-muc/data-open', [DanhMucController::class, 'getPublicData']);

// 2. Nhóm Route dành cho Admin/Nhân viên (Có Middleware bảo vệ)
Route::middleware(NhanVienMiddleware::class)->group(function () {
    Route::get('/admin/danh-muc/data', [DanhMucController::class, 'getData']);
    Route::post('/admin/danh-muc/create', [DanhMucController::class, 'store']);
    Route::post('/admin/danh-muc/update', [DanhMucController::class, 'update']);
    Route::post('/admin/danh-muc/delete', [DanhMucController::class, 'destroy']);
    Route::post('/admin/danh-muc/search', [DanhMucController::class, 'search']);
    Route::post('/admin/danh-muc/change-status', [DanhMucController::class, 'changeStatus']);
});

// 3. Nhóm Route dành cho Khách hàng (Có Middleware bảo vệ)
Route::middleware('khachHangMiddle')->group(function () {
    Route::post('/khach-hang/them-don-hang', [DonHangController::class, 'themDonHang']);
    Route::post('/khach-hang/get-data-don-hang', [DonHangController::class, 'layDonHang']);
});
```

---

### 2. Cách viết Form Request để Validate dữ liệu (`app/Http/Requests`)

Để tránh việc Controller bị phình to (Fat Controller), toàn bộ logic validate dữ liệu gửi lên phải được tách biệt hoàn toàn vào lớp **Form Request**.

#### Quy trình tạo và viết Request:
1. Chạy lệnh Artisan để tạo Request:
   ```bash
   php artisan make:request DanhMucRequestCreate
   ```
2. Mở file request mới tạo trong `app/Http/Requests/`:
   - Trả về `true` trong hàm `authorize()`.
   - Viết các quy tắc kiểm tra dữ liệu trong hàm `rules()`.
   - Cung cấp các thông báo lỗi bằng tiếng Việt dễ hiểu trong hàm `messages()`.

#### Ví dụ thực tế (`DanhMucRequestCreate.php`):
```php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DanhMucRequestCreate extends FormRequest
{
    // Cho phép thực hiện request
    public function authorize(): bool
    {
        return true;
    }

    // Các quy tắc validation
    public function rules(): array
    {
        return [
            'ten_danh_muc' => 'required|string|max:255',
            'tinh_trang'   => 'nullable|integer|in:0,1',
            'hinh_anh'     => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Tối đa 2MB
        ];
    }

    // Thông báo lỗi tùy chỉnh bằng Tiếng Việt
    public function messages(): array
    {
        return [
            'ten_danh_muc.required' => 'Tên danh mục không được để trống.',
            'ten_danh_muc.string'   => 'Tên danh mục phải là chuỗi ký tự.',
            'ten_danh_muc.max'      => 'Tên danh mục không được vượt quá 255 ký tự.',
            'tinh_trang.in'         => 'Tình trạng không hợp lệ (0 = Hết hàng, 1 = Còn hàng).',
            'hinh_anh.image'        => 'File tải lên phải là hình ảnh.',
            'hinh_anh.mimes'        => 'Hình ảnh phải có định dạng jpeg, png, jpg hoặc gif.',
            'hinh_anh.max'          => 'Kích thước ảnh không được vượt quá 2MB.',
        ];
    }
}
```

---

### 3. Cách viết Function trong Controller (`app/Http/Controllers`)

Mỗi Controller kế thừa từ `Controller` và xử lý các hành động CRUD cụ thể.

#### Các tiêu chuẩn bắt buộc:
1. **Kiểm tra quyền hạn**: Sử dụng cơ chế check trong database:
   ```php
   $id_chuc_nang = [ID_CHUC_NANG_CU_THE];
   $chuc_vu = Auth::guard('sanctum')->user()->id_chuc_vu;
   $check = PhanQuyen::where('id_chuc_vu', $chuc_vu)->where('id_chuc_nang', $id_chuc_nang)->first();
   if (!$check) {
       return response()->json([
           'status' => 0,
           'message' => 'Bạn không có quyền thực hiện chức năng này!'
       ]);
   }
   ```
2. **Xử lý upload file/ảnh (nếu có)**: Lưu file vào thư mục `public` thông qua Disk `public` của Laravel và lưu đường dẫn tương đối `/storage/...` vào DB.
3. **Cấu trúc dữ liệu trả về**: Phải luôn trả về định dạng JSON thống nhất:
   - Thành công: `status => true` hoặc `status => 1`, kèm theo `message` và `data` (nếu cần).
   - Thất bại: `status => false` hoặc `status => 0`, kèm theo lý do lỗi trong `message`.

#### Ví dụ Controller mẫu (`DanhMucController.php`):
```php
namespace App\Http\Controllers;

use App\Http\Requests\DanhMucRequestCreate;
use App\Models\DanhMuc;
use App\Models\PhanQuyen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DanhMucController extends Controller
{
    // 1. Lấy danh sách danh mục (GET)
    public function getData()
    {
        $data = DanhMuc::all();
        return response()->json([
            'status' => true,
            'data'   => $data,
        ]);
    }

    // 2. Thêm mới danh mục (POST)
    public function store(DanhMucRequestCreate $request)
    {
        $hinhAnhPath = null;
        if ($request->hasFile('hinh_anh')) {
            $file = $request->file('hinh_anh');
            $path = $file->store('uploads/danh_muc', 'public');
            $hinhAnhPath = '/storage/' . $path;
        }

        DanhMuc::create([
            'ten_danh_muc' => $request->ten_danh_muc,
            'hinh_anh'     => $hinhAnhPath,
            'mo_ta'        => $request->mo_ta ?? null,
            'tinh_trang'   => $request->tinh_trang ?? 1,
        ]);

        return response()->json([
            'status'  => 1,
            'message' => 'Thêm mới danh mục ' . $request->ten_danh_muc . ' thành công.'
        ]);
    }

    // 3. Cập nhật danh mục (POST)
    public function update(Request $request)
    {
        $danhMuc = DanhMuc::find($request->id);
        if (!$danhMuc) {
            return response()->json([
                'status'  => 0,
                'message' => 'Danh mục không tồn tại!'
            ]);
        }

        $updateData = [
            'ten_danh_muc' => $request->ten_danh_muc,
            'mo_ta'        => $request->mo_ta ?? null,
            'tinh_trang'   => $request->tinh_trang ?? 1,
        ];

        // Xử lý upload ảnh mới & xóa ảnh cũ
        if ($request->hasFile('hinh_anh')) {
            if ($danhMuc->hinh_anh && file_exists(public_path($danhMuc->hinh_anh))) {
                @unlink(public_path($danhMuc->hinh_anh));
            }
            $file = $request->file('hinh_anh');
            $path = $file->store('uploads/danh_muc', 'public');
            $updateData['hinh_anh'] = '/storage/' . $path;
        }

        $danhMuc->update($updateData);

        return response()->json([
            'status'  => 1,
            'message' => 'Cập nhật danh mục thành công!'
        ]);
    }
}
```

---
---

## PHẦN 2: HƯỚNG DẪN VIẾT VÀ TỔ CHỨC LOGIC TRÊN FRONTEND (VUE 3)

Frontend của dự án sử dụng **Vue 3 (Single File Components - SFC)** kết hợp **Vite**, **Options API**, **Axios** để giao tiếp API và thư viện thông báo **vue-toaster** hoặc **vue-toast-notification**.

### 1. Cách Tổ Chức Một File Component (`.vue`)

Một component trong thư mục `src/components/` (ví dụ: `src/components/Admin/DanhMuc/index.vue`) phải có cấu trúc gồm 3 phần rõ rệt:

```vue
<template>
  <!-- 1. GIAO DIỆN HTML (UI) -->
</template>

<script>
  // 2. XỬ LÝ LOGIC (Data, Methods, Lifecycle Hooks)
</script>

<style>
  /* 3. GIAO DIỆN CSS (Styling) */
</style>
```

---

### 2. Tổ Chức và Quản Lý State (Trong phần `<script>`)

Để quản lý trạng thái của màn hình (VD: form thêm mới, form chỉnh sửa, bộ lọc tìm kiếm, danh sách hiển thị, các biến preview ảnh), chúng ta tổ chức trong hàm `data()` của Vue 3 Options API:

```javascript
import axios from 'axios';

export default {
    data() {
        return {
            // Đối tượng lưu trữ form thêm mới
            create: {
                ten_danh_muc: "",
                tinh_trang: 1,
                mo_ta: "",
                hinh_anh: null
            },
            // Đối tượng lưu trữ form chỉnh sửa
            edit: {
                id: null,
                ten_danh_muc: "",
                tinh_trang: 1,
                mo_ta: "",
                hinh_anh: null
            },
            // Đối tượng lưu trữ ID cần xóa
            del: {
                id: null,
                ten_danh_muc: ""
            },
            // Đối tượng chứa từ khóa tìm kiếm
            search: {
                noi_dung: ""
            },
            // Mảng chứa danh sách bản ghi hiển thị trên Table
            list: [],
            // Các biến xử lý preview ảnh
            createImagePreview: null,
            createImageFile: null,
            editImagePreview: null,
            editImageFile: null,
            // Base URL kết nối đến API Laravel
            apiBaseUrl: 'http://127.0.0.1:8000/api'
        }
    },
    mounted() {
        // Lifecycle Hook: Chạy ngay sau khi component render xong
        this.loadData();
    },
    methods: {
        // Các hàm xử lý sự kiện và gọi API nằm ở đây
    }
}
```

---

### 3. Cách Viết Các Hàm Gọi API Sử Dụng Axios

Tất cả các hành động tương tác với CSDL (CRUD) đều phải gọi đến API Backend qua **Axios**.

#### Quy chuẩn gửi Token xác thực (Authorization Header):
Vì dự án dùng xác thực Sanctum, mỗi request gọi API yêu cầu bảo mật bắt buộc phải gửi Header `Authorization`:
- **Đối với Admin/Nhân viên**: Lấy token `token_nhan_vien` trong `localStorage`.
- **Đối với Khách hàng**: Lấy token `token_khach_hang` trong `localStorage`.

#### 3.1. Hàm lấy dữ liệu (GET request)
```javascript
loadData() {
    axios
        .get(`${this.apiBaseUrl}/admin/danh-muc/data`, {
            headers: {
                Authorization: 'Bearer ' + localStorage.getItem('token_nhan_vien')
            }
        })
        .then((res) => {
            // Gán dữ liệu trả về từ backend vào state list
            this.list = res.data.data || [];
        })
        .catch((error) => {
            this.$toast.error("Không thể kết nối đến máy chủ!");
        });
}
```

#### 3.2. Hàm Thêm Mới / Cập Nhật có File (Multipart Form-Data)
Khi form có upload ảnh hoặc file, ta không truyền JSON object trực tiếp mà phải sử dụng **`FormData`** và thêm header `'Content-Type': 'multipart/form-data'`:

```javascript
themMoi() {
    const formData = new FormData();
    formData.append('ten_danh_muc', this.create.ten_danh_muc.trim());
    formData.append('mo_ta', this.create.mo_ta || '');
    formData.append('tinh_trang', this.create.tinh_trang);
    
    // Nếu người dùng có chọn ảnh
    if (this.createImageFile) {
        formData.append('hinh_anh', this.createImageFile);
    }

    axios
        .post(`${this.apiBaseUrl}/admin/danh-muc/create`, formData, {
            headers: {
                Authorization: 'Bearer ' + localStorage.getItem('token_nhan_vien'),
                'Content-Type': 'multipart/form-data'
            }
        })
        .then((res) => {
            if (res.data.status) {
                // Hiển thị thông báo thành công
                this.$toast.success(res.data.message);
                // Tải lại bảng dữ liệu
                this.loadData();
                // Reset form
                this.create = { ten_danh_muc: "", mo_ta: "", tinh_trang: 1, hinh_anh: null };
                this.createImagePreview = null;
                this.createImageFile = null;
            } else {
                this.$toast.error(res.data.message);
            }
        })
        .catch((err) => {
            // Đọc và hiển thị các lỗi validate từ Laravel Request gửi về
            if (err.response && err.response.data && err.response.data.errors) {
                const errors = Object.values(err.response.data.errors);
                errors.forEach((messageArray) => {
                    this.$toast.error(messageArray[0]);
                });
            } else {
                this.$toast.error("Có lỗi xảy ra, vui lòng thử lại!");
            }
        });
}
```

#### 3.3. Hàm Chỉnh Sửa / Xóa dữ liệu (POST với JSON Object)
Nếu request gửi lên chỉ là text/JSON thông thường (như Xóa hoặc Đổi trạng thái), ta truyền tham số trực tiếp:

```javascript
xoa() {
    axios
        .post(`${this.apiBaseUrl}/admin/danh-muc/delete`, this.del, {
            headers: {
                Authorization: 'Bearer ' + localStorage.getItem('token_nhan_vien')
            }
        })
        .then((res) => {
            if (res.data.status) {
                this.$toast.success(res.data.message);
                this.loadData();
            } else {
                this.$toast.error(res.data.message);
            }
        })
        .catch((err) => {
            this.handleApiError(err);
        });
}
```

---

### 4. Quản Lý File, Preview Ảnh và Xử Lý Lỗi Ảnh

Để tăng trải nghiệm người dùng, ta thêm các hàm bổ trợ xử lý hình ảnh trên UI.

#### 4.1. Hàm chọn ảnh và hiển thị preview (không cần upload lên server ngay lập tức):
Hàm này sử dụng đối tượng **`FileReader`** của trình duyệt để tạo ra base64 preview hiển thị tức thì trên thẻ `<img>`.

```javascript
handleCreateImageUpload(event) {
    const file = event.target.files[0];
    if (file) {
        // Validate kích thước file tối đa 2MB
        if (file.size > 2 * 1024 * 1024) {
            this.$toast.error('Hình ảnh không được vượt quá 2MB!');
            return;
        }
        // Validate loại file phải là ảnh
        if (!file.type.startsWith('image/')) {
            this.$toast.error('File được chọn phải là hình ảnh!');
            return;
        }
        this.createImageFile = file;
        
        // Tạo preview ảnh
        const reader = new FileReader();
        reader.onload = (e) => {
            this.createImagePreview = e.target.result;
        };
        reader.readAsDataURL(file);
    }
}
```

#### 4.2. Xử lý đường dẫn ảnh từ Backend gửi về
Hình ảnh lưu ở backend Laravel là đường dẫn tương đối (VD: `/storage/uploads/danh_muc/abc.jpg`). Tại FE, ta cần sinh đường dẫn đầy đủ bằng cách ghép với domain của Server:

```javascript
getImageUrl(url) {
    if (!url || url === '') {
        // Ảnh mặc định dạng Base64 khi không có ảnh
        return 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PHJlY3Qgd2lkdGg9IjYwIiBoZWlnaHQ9IjYwIiBmaWxsPSIjZTllY2VmIi8+PHRleHQgeD0iNTAlIiB5PSI1MCUiIGZvbnQtc2l6ZT0iMTIiIGZpbGw9IiM5OTkiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGR5PSIuM2VtIj5ObyBJbWFnZTwvdGV4dD48L3N2Zz4=';
    }
    if (url.startsWith('http://') || url.startsWith('https://')) {
        return url;
    }
    // Chuyển 'http://localhost:8000/api' thành 'http://localhost:8000' + '/storage/...'
    const domain = this.apiBaseUrl.replace('/api', '');
    return `${domain}${url}`;
}
```

---

### 5. Cách Validate & Hiển Thị Thông Báo Lỗi từ Laravel Validator lên UI

Khi API validate thất bại (ví dụ: Tên danh mục bị trùng hoặc quá dài), Laravel trả về mã lỗi HTTP **422 Unprocessable Entity** kèm danh sách lỗi chi tiết trong `errors`. 

Ta viết một hàm dùng chung để duyệt qua danh sách lỗi và bắn Toast thông báo cụ thể cho người dùng:

```javascript
handleApiError(error) {
    if (error.response && error.response.status === 422) {
        // Đọc các lỗi validation của Laravel
        const errorData = error.response.data.errors;
        Object.keys(errorData).forEach((field) => {
            const messages = errorData[field];
            messages.forEach((msg) => {
                this.$toast.error(msg); // Bắn từng tin nhắn lỗi lên góc màn hình
            });
        });
    } else if (error.response && error.response.data && error.response.data.message) {
        this.$toast.error(error.response.data.message);
    } else {
        this.$toast.error("Có lỗi hệ thống xảy ra. Vui lòng liên hệ Admin!");
    }
}
```

Trong phần `.catch()` của các request Axios khác, ta chỉ cần gọi đơn giản:
```javascript
.catch((err) => {
    this.handleApiError(err);
})

---
*Tài liệu này được biên soạn cụ thể cho cấu trúc codebase dự án Web_ShopClothes.
