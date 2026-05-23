# Postman Test Cases - E-commerce API (Tiếng Việt)

Tài liệu này hướng dẫn các trường hợp kiểm thử (test cases) cho hệ thống API với các endpoint đã được Việt hóa.

## 1. Authentication (Xác thực)

### Register (Đăng ký)

- **Method:** POST
- **URL:** `{{base_url}}/api/dang-ky`
- **Body:**

```json
{
    "ho_ten": "Nguyen Van A",
    "email": "customer_test@gmail.com",
    "mat_khau": "password123"
}
```

### Login (Đăng nhập)

- **Method:** POST
- **URL:** `{{base_url}}/api/dang-nhap`
- **Body:**

```json
{
    "email": "customer_test@gmail.com",
    "mat_khau": "password123"
}
```

---

## 2. Khách Hàng (Customer)

### Xem sản phẩm

- **Method:** GET
- **URL:** `{{base_url}}/api/san-pham`

### Đặt hàng (Checkout)

- **Method:** POST
- **URL:** `{{base_url}}/api/khach-hang/dat-hang`
- **Header:** `Authorization: Bearer {{token}}`
- **Body:**

```json
{
    "items": [{ "id_bien_the": 1, "so_luong": 2 }],
    "id_dia_chi": 1,
    "phuong_thuc_thanh_toan": "tien_mat",
    "ma_giam_gia": "CHA mung"
}
```

---

## 3. Quản Lý (Manager)

### Cập nhật trạng thái đơn hàng

- **Method:** PATCH
- **URL:** `{{base_url}}/api/quan-ly/don-hang/1/trang-thai`
- **Body:**

```json
{
    "trang_thai": "dang_giao",
    "ghi_chu": "Đã bàn giao cho đơn vị vận chuyển"
}
```

---

## 4. Quản Trị (Admin)

### Xem thống kê

- **Method:** GET
- **URL:** `{{base_url}}/api/quan-tri/thong-ke`

---

## 5. Webhook (Hệ thống)

### Cập nhật thanh toán

- **Method:** POST
- **URL:** `{{base_url}}/api/webhooks/thanh-toan`
- **Body:**

```json
{
    "order_id": "ORD-ABCXYZ",
    "transaction_id": "VNP12345678",
    "status": "success"
}
```
