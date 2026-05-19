<template>
  <div class="pd-page">

    <!-- Breadcrumb -->
    <div class="pd-breadcrumb">
      <div class="pd-container">
        <router-link to="/" class="bc-link">Trang chủ</router-link>
        <span class="bc-sep">›</span>
        <span class="bc-link">Mô hình xe hơi</span>
        <span class="bc-sep">›</span>
        <span class="bc-current">{{ product.name }}</span>
      </div>
    </div>

    <!-- Main content -->
    <div class="pd-container pd-main">

      <!-- LEFT: Gallery -->
      <div class="pd-gallery">
        <div class="pd-main-img-wrap">
          <span class="pd-badge-sale" v-if="product.discount">-{{ product.discount }}%</span>
          <span class="pd-badge-new" v-if="product.isNew">MỚI</span>
          <div class="pd-main-img">{{ product.emoji }}</div>
          <div class="pd-zoom-hint">🔍 Rê chuột để phóng to</div>
        </div>
        <div class="pd-thumbs">
          <div
            v-for="(img, i) in product.images"
            :key="i"
            class="pd-thumb"
            :class="{ active: activeImg === i }"
            @click="activeImg = i"
          >{{ img }}</div>
        </div>
      </div>

      <!-- RIGHT: Info -->
      <div class="pd-info">
        <div class="pd-brand">{{ product.brand }}</div>
        <h1 class="pd-title">{{ product.name }}</h1>

        <!-- Rating -->
        <div class="pd-rating-row">
          <div class="pd-stars">
            <span v-for="s in 5" :key="s" :class="s <= product.rating ? 'star-on' : 'star-off'">★</span>
          </div>
          <span class="pd-rating-count">({{ product.reviewCount }} đánh giá)</span>
          <span class="pd-sold">• Đã bán {{ product.sold }}</span>
        </div>

        <!-- Price -->
        <div class="pd-price-box">
          <span class="pd-price">{{ formatPrice(product.price) }}</span>
          <span class="pd-old-price" v-if="product.oldPrice">{{ formatPrice(product.oldPrice) }}</span>
          <span class="pd-save" v-if="product.discount">Tiết kiệm {{ formatPrice(product.oldPrice - product.price) }}</span>
        </div>

        <!-- Key specs -->
        <div class="pd-specs">
          <div class="pd-spec" v-for="spec in product.specs" :key="spec.label">
            <span class="pd-spec-icon">{{ spec.icon }}</span>
            <div>
              <div class="pd-spec-label">{{ spec.label }}</div>
              <div class="pd-spec-val">{{ spec.value }}</div>
            </div>
          </div>
        </div>

        <!-- Color selector -->
        <div class="pd-option-group">
          <div class="pd-option-label">Màu sắc: <strong>{{ selectedColor }}</strong></div>
          <div class="pd-colors">
            <button
              v-for="c in product.colors"
              :key="c.name"
              class="pd-color-btn"
              :class="{ active: selectedColor === c.name }"
              :style="{ background: c.hex }"
              :title="c.name"
              @click="selectedColor = c.name"
            ></button>
          </div>
        </div>

        <!-- Quantity -->
        <div class="pd-option-group">
          <div class="pd-option-label">Số lượng:</div>
          <div class="pd-qty-row">
            <div class="pd-qty">
              <button @click="qty > 1 && qty--" class="pd-qty-btn">−</button>
              <span class="pd-qty-val">{{ qty }}</span>
              <button @click="qty++" class="pd-qty-btn">+</button>
            </div>
            <span class="pd-stock">Còn {{ product.stock }} sản phẩm</span>
          </div>
        </div>

        <!-- Actions -->
        <div class="pd-actions">
          <button class="pd-btn-cart" @click="addToCart">
            🛒 Thêm vào giỏ hàng
          </button>
          <button class="pd-btn-buy" @click="buyNow">
            ⚡ Mua ngay
          </button>
          <button class="pd-btn-wish" :class="{ wished: isWished }" @click="isWished = !isWished" title="Yêu thích">
            {{ isWished ? '❤️' : '🤍' }}
          </button>
        </div>

        <!-- Shipping info -->
        <div class="pd-shipping">
          <div class="pd-ship-row"><span>🚚</span> Giao hàng miễn phí cho đơn từ <strong>300.000đ</strong></div>
          <div class="pd-ship-row"><span>↩️</span> Đổi trả miễn phí trong vòng <strong>30 ngày</strong></div>
          <div class="pd-ship-row"><span>🔒</span> Thanh toán bảo mật — SSL</div>
        </div>
      </div>
    </div>

    <!-- Tabs: Description / Specs / Reviews -->
    <div class="pd-container pd-tabs-section">
      <div class="pd-tabs">
        <button
          v-for="tab in tabs"
          :key="tab.key"
          class="pd-tab"
          :class="{ active: activeTab === tab.key }"
          @click="activeTab = tab.key"
        >{{ tab.label }}</button>
      </div>

      <!-- Description -->
      <div v-if="activeTab === 'desc'" class="pd-tab-content">
        <p>{{ product.description }}</p>
        <ul class="pd-feature-list">
          <li v-for="f in product.features" :key="f">✅ {{ f }}</li>
        </ul>
      </div>

      <!-- Specs table -->
      <div v-if="activeTab === 'specs'" class="pd-tab-content">
        <table class="pd-spec-table">
          <tbody>
            <tr v-for="row in product.specTable" :key="row.label">
              <td class="pd-st-label">{{ row.label }}</td>
              <td class="pd-st-val">{{ row.value }}</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Reviews -->
      <div v-if="activeTab === 'reviews'" class="pd-tab-content">
        <div class="pd-review-summary">
          <div class="pd-rs-score">{{ product.rating }}<span>/5</span></div>
          <div>
            <div class="pd-stars lg">
              <span v-for="s in 5" :key="s" :class="s <= product.rating ? 'star-on' : 'star-off'">★</span>
            </div>
            <div class="pd-rs-label">{{ product.reviewCount }} đánh giá</div>
          </div>
        </div>
        <div class="pd-review-list">
          <div v-for="rv in reviews" :key="rv.id" class="pd-review-card">
            <div class="pd-rv-header">
              <div class="pd-rv-avatar">{{ rv.name[0] }}</div>
              <div>
                <div class="pd-rv-name">{{ rv.name }}</div>
                <div class="pd-stars sm">
                  <span v-for="s in 5" :key="s" :class="s <= rv.rating ? 'star-on' : 'star-off'">★</span>
                </div>
              </div>
              <div class="pd-rv-date">{{ rv.date }}</div>
            </div>
            <p class="pd-rv-body">{{ rv.body }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Related Products -->
    <div class="pd-container pd-related">
      <h2 class="pd-section-title">Sản phẩm tương tự</h2>
      <div class="pd-related-grid">
        <div v-for="rp in related" :key="rp.id" class="pd-related-card" @click="$router.push(`/product/${rp.id}`)">
          <div class="pd-rc-img">{{ rp.emoji }}</div>
          <div class="pd-rc-name">{{ rp.name }}</div>
          <div class="pd-rc-price">{{ formatPrice(rp.price) }}</div>
          <div class="pd-rc-stars">
            <span v-for="s in 5" :key="s" :class="s <= rp.rating ? 'star-on' : 'star-off'">★</span>
          </div>
        </div>
      </div>
    </div>

  </div>
</template>

<script>
export default {
  name: 'ProductDetail',
  data() {
    return {
      activeImg: 0,
      selectedColor: 'Đỏ Ferrari',
      qty: 1,
      isWished: false,
      activeTab: 'desc',
      tabs: [
        { key: 'desc',    label: 'Mô tả sản phẩm' },
        { key: 'specs',   label: 'Thông số kỹ thuật' },
        { key: 'reviews', label: 'Đánh giá (48)' },
      ],
      product: {
        id: 1,
        name: 'Ferrari F40 1:18 Scale — Limited Edition',
        brand: 'BBURAGO SIGNATURE',
        emoji: '🏎️',
        images: ['🏎️', '🔴', '⚙️', '📦'],
        price: 1_890_000,
        oldPrice: 2_490_000,
        discount: 24,
        isNew: false,
        rating: 5,
        reviewCount: 48,
        sold: '120+',
        stock: 7,
        description:
          'Mô hình siêu xe Ferrari F40 tỉ lệ 1:18 được sản xuất theo phong cách Limited Edition bởi Bburago Signature. Mỗi chi tiết được tái hiện tỉ mỉ từ bánh xe, bộ phanh Brembo, cho tới nội thất bằng da thật. Hộp đựng cao cấp với tem số thứ tự riêng, thích hợp cho nhà sưu tập chuyên nghiệp.',
        features: [
          'Tỉ lệ chính xác 1:18 — cao nhất phân khúc',
          'Kim loại đúc khuôn Die-cast toàn thân',
          'Bánh xe cao su thật, cửa & capo mở được',
          'Kính trong suốt, nội thất chi tiết đầy đủ',
          'Hộp kính trưng bày đi kèm',
          'Số lượng giới hạn — chỉ 500 chiếc toàn cầu',
        ],
        specs: [
          { icon: '📐', label: 'Tỉ lệ', value: '1:18' },
          { icon: '📏', label: 'Kích thước', value: '24 × 10 × 6 cm' },
          { icon: '⚖️', label: 'Trọng lượng', value: '520g' },
          { icon: '🏭', label: 'Chất liệu', value: 'Die-cast Zinc Alloy' },
        ],
        specTable: [
          { label: 'Thương hiệu',       value: 'Bburago Signature' },
          { label: 'Mẫu xe',            value: 'Ferrari F40 (1987)' },
          { label: 'Tỉ lệ',             value: '1:18' },
          { label: 'Màu sắc chính',     value: 'Đỏ Rosso Corsa' },
          { label: 'Chất liệu thân',    value: 'Die-cast Zinc Alloy' },
          { label: 'Lốp xe',            value: 'Cao su thật' },
          { label: 'Cửa',               value: 'Mở được' },
          { label: 'Capo trước / sau',  value: 'Mở được' },
          { label: 'Số lượng sản xuất', value: '500 chiếc' },
          { label: 'Xuất xứ',           value: 'Ý' },
          { label: 'Bảo hành',          value: '12 tháng' },
        ],
        colors: [
          { name: 'Đỏ Ferrari',   hex: '#d00000' },
          { name: 'Đen huyền',    hex: '#1a1a1a' },
          { name: 'Vàng champagne', hex: '#c9a84c' },
        ],
      },
      reviews: [
        { id: 1, name: 'Nguyễn Minh Khoa', rating: 5, date: '12/05/2025', body: 'Sản phẩm cực kỳ chất lượng, chi tiết rất sắc nét, đóng gói cẩn thận. Giao hàng nhanh, sẽ ủng hộ tiếp!' },
        { id: 2, name: 'Trần Thu Hà',      rating: 4, date: '03/04/2025', body: 'Mô hình đẹp hơn mong đợi, chỉ tiếc màu đỏ ngoài thực tế hơi sậm hơn ảnh một chút. Nói chung rất ổn.' },
        { id: 3, name: 'Lê Văn Bình',      rating: 5, date: '20/03/2025', body: 'Mua làm quà sinh nhật cho bạn, bạn mình khen mãi. Hộp kính đi kèm rất sang trọng!' },
      ],
      related: [
        { id: 2, name: 'Lamborghini Huracán 1:18', emoji: '🟡', price: 1_650_000, rating: 5 },
        { id: 3, name: 'Porsche 911 GT3 1:18',     emoji: '⚪', price: 1_750_000, rating: 4 },
        { id: 4, name: 'McLaren P1 1:18',           emoji: '🟠', price: 2_100_000, rating: 5 },
        { id: 5, name: 'Bugatti Chiron 1:18',       emoji: '🔵', price: 2_350_000, rating: 5 },
      ],
    };
  },
  methods: {
    formatPrice(val) {
      if (!val) return '';
      return val.toLocaleString('vi-VN') + 'đ';
    },
    addToCart() {
      this.$toast?.success(`Đã thêm ${this.qty} sản phẩm vào giỏ hàng!`) || alert('Đã thêm vào giỏ hàng!');
    },
    buyNow() {
      this.$toast?.info('Chức năng thanh toán đang phát triển!') || alert('Đang phát triển!');
    },
  },
};
</script>

<style scoped>
/* ── Base ── */
.pd-page { background: #f8fafc; min-height: 100vh; padding-bottom: 80px; }
.pd-container { max-width: 1200px; margin: 0 auto; padding: 0 20px; }

/* ── Breadcrumb ── */
.pd-breadcrumb { background: #fff; border-bottom: 1px solid #e2e8f0; padding: 12px 0; font-size: 13px; }
.bc-link { color: #e11d48; text-decoration: none; }
.bc-link:hover { text-decoration: underline; }
.bc-sep { color: #94a3b8; margin: 0 6px; }
.bc-current { color: #475569; }

/* ── Main grid ── */
.pd-main { display: grid; grid-template-columns: 1fr 1fr; gap: 48px; padding-top: 36px; padding-bottom: 48px; }

/* ── Gallery ── */
.pd-main-img-wrap {
  position: relative; background: #fff; border-radius: 20px;
  border: 1px solid #e2e8f0; aspect-ratio: 1; display: flex;
  align-items: center; justify-content: center; overflow: hidden;
  box-shadow: 0 4px 24px rgba(0,0,0,.06);
}
.pd-main-img { font-size: 140px; line-height: 1; }
.pd-badge-sale, .pd-badge-new {
  position: absolute; top: 16px; left: 16px; border-radius: 8px;
  font-size: 12px; font-weight: 700; padding: 4px 10px; color: #fff;
}
.pd-badge-sale { background: #e11d48; }
.pd-badge-new  { background: #0ea5e9; top: 52px; }
.pd-zoom-hint { position: absolute; bottom: 12px; right: 14px; font-size: 11px; color: #94a3b8; }
.pd-thumbs { display: flex; gap: 10px; margin-top: 14px; }
.pd-thumb {
  width: 68px; height: 68px; border-radius: 12px; border: 2px solid #e2e8f0;
  background: #fff; display: flex; align-items: center; justify-content: center;
  font-size: 28px; cursor: pointer; transition: all .2s;
}
.pd-thumb.active, .pd-thumb:hover { border-color: #e11d48; box-shadow: 0 0 0 3px rgba(225,29,72,.15); }

/* ── Info panel ── */
.pd-info { display: flex; flex-direction: column; gap: 20px; }
.pd-brand { font-size: 12px; font-weight: 700; color: #e11d48; letter-spacing: .1em; text-transform: uppercase; }
.pd-title { font-size: 26px; font-weight: 800; color: #0f172a; line-height: 1.3; margin: 0; }

/* Rating */
.pd-rating-row { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
.pd-stars { display: flex; gap: 2px; }
.star-on  { color: #f59e0b; }
.star-off { color: #d1d5db; }
.pd-stars.lg  span { font-size: 22px; }
.pd-stars.sm  span { font-size: 13px; }
.pd-rating-count { font-size: 13px; color: #e11d48; cursor: pointer; }
.pd-sold { font-size: 13px; color: #64748b; }

/* Price */
.pd-price-box { display: flex; align-items: center; gap: 14px; flex-wrap: wrap; }
.pd-price { font-size: 32px; font-weight: 800; color: #e11d48; }
.pd-old-price { font-size: 16px; color: #94a3b8; text-decoration: line-through; }
.pd-save { font-size: 13px; background: #fff7ed; color: #ea580c; border-radius: 6px; padding: 3px 10px; font-weight: 600; }

/* Key specs strip */
.pd-specs { display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; }
.pd-spec {
  display: flex; align-items: center; gap: 12px; background: #fff;
  border: 1px solid #e2e8f0; border-radius: 12px; padding: 12px 14px;
}
.pd-spec-icon { font-size: 22px; }
.pd-spec-label { font-size: 11px; color: #94a3b8; text-transform: uppercase; letter-spacing: .05em; }
.pd-spec-val   { font-size: 14px; font-weight: 700; color: #1e293b; }

/* Option groups */
.pd-option-group { display: flex; flex-direction: column; gap: 10px; }
.pd-option-label { font-size: 14px; color: #475569; }
.pd-colors { display: flex; gap: 10px; }
.pd-color-btn {
  width: 30px; height: 30px; border-radius: 50%; border: 3px solid transparent;
  cursor: pointer; transition: all .2s; outline: none;
}
.pd-color-btn.active, .pd-color-btn:hover { border-color: #e11d48; box-shadow: 0 0 0 3px rgba(225,29,72,.2); }
.pd-qty-row { display: flex; align-items: center; gap: 16px; }
.pd-qty { display: flex; align-items: center; gap: 0; border: 1.5px solid #e2e8f0; border-radius: 12px; overflow: hidden; }
.pd-qty-btn { background: #f1f5f9; border: none; width: 38px; height: 38px; font-size: 18px; cursor: pointer; transition: background .15s; }
.pd-qty-btn:hover { background: #e2e8f0; }
.pd-qty-val { width: 44px; text-align: center; font-weight: 700; font-size: 15px; color: #0f172a; }
.pd-stock { font-size: 13px; color: #10b981; font-weight: 600; }

/* Actions */
.pd-actions { display: flex; gap: 12px; flex-wrap: wrap; }
.pd-btn-cart, .pd-btn-buy {
  flex: 1; min-width: 140px; border: none; border-radius: 14px; padding: 15px 0;
  font-size: 15px; font-weight: 700; cursor: pointer; transition: all .25s;
}
.pd-btn-cart {
  background: #fff; color: #e11d48; border: 2px solid #e11d48;
}
.pd-btn-cart:hover { background: #fff1f2; }
.pd-btn-buy {
  background: linear-gradient(135deg, #e11d48, #be123c);
  color: #fff; box-shadow: 0 8px 24px rgba(225,29,72,.3);
}
.pd-btn-buy:hover { box-shadow: 0 12px 32px rgba(225,29,72,.4); transform: translateY(-1px); }
.pd-btn-wish {
  width: 52px; height: 52px; border-radius: 14px; border: 2px solid #e2e8f0;
  background: #fff; font-size: 22px; cursor: pointer; transition: all .2s;
}
.pd-btn-wish.wished { border-color: #e11d48; background: #fff1f2; }
.pd-btn-wish:hover { border-color: #e11d48; }

/* Shipping */
.pd-shipping { background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 14px; padding: 16px 18px; display: flex; flex-direction: column; gap: 8px; }
.pd-ship-row { font-size: 13.5px; color: #166534; display: flex; gap: 8px; }

/* ── Tabs ── */
.pd-tabs-section { border-top: 1px solid #e2e8f0; padding-top: 40px; }
.pd-tabs { display: flex; gap: 4px; border-bottom: 2px solid #e2e8f0; margin-bottom: 28px; }
.pd-tab { background: none; border: none; padding: 12px 22px; font-size: 14.5px; font-weight: 600; color: #64748b; cursor: pointer; border-bottom: 3px solid transparent; margin-bottom: -2px; transition: all .2s; }
.pd-tab.active { color: #e11d48; border-bottom-color: #e11d48; }
.pd-tab:hover  { color: #e11d48; }
.pd-tab-content { max-width: 820px; color: #334155; line-height: 1.8; font-size: 15px; }

.pd-feature-list { margin-top: 16px; display: flex; flex-direction: column; gap: 8px; padding: 0; list-style: none; }
.pd-feature-list li { font-size: 14.5px; }

/* Spec table */
.pd-spec-table { width: 100%; border-collapse: collapse; }
.pd-spec-table tr { border-bottom: 1px solid #f1f5f9; }
.pd-spec-table tr:nth-child(odd) { background: #f8fafc; }
.pd-st-label { padding: 12px 16px; font-size: 13.5px; color: #64748b; font-weight: 600; width: 220px; }
.pd-st-val   { padding: 12px 16px; font-size: 14px; color: #0f172a; }

/* Review summary */
.pd-review-summary { display: flex; align-items: center; gap: 24px; background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 24px 28px; margin-bottom: 24px; }
.pd-rs-score { font-size: 56px; font-weight: 800; color: #0f172a; line-height: 1; }
.pd-rs-score span { font-size: 22px; color: #94a3b8; }
.pd-rs-label { font-size: 13px; color: #64748b; margin-top: 4px; }
.pd-review-list { display: flex; flex-direction: column; gap: 16px; }
.pd-review-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 20px 22px; }
.pd-rv-header { display: flex; align-items: center; gap: 14px; margin-bottom: 12px; }
.pd-rv-avatar { width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #e11d48, #8b5cf6); color: #fff; font-weight: 700; display: flex; align-items: center; justify-content: center; font-size: 16px; flex-shrink: 0; }
.pd-rv-name { font-size: 14px; font-weight: 700; color: #0f172a; }
.pd-rv-date { margin-left: auto; font-size: 12px; color: #94a3b8; }
.pd-rv-body { font-size: 14px; color: #475569; line-height: 1.6; margin: 0; }

/* ── Related ── */
.pd-related { padding-top: 48px; }
.pd-section-title { font-size: 22px; font-weight: 800; color: #0f172a; margin-bottom: 24px; }
.pd-related-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; }
.pd-related-card {
  background: #fff; border: 1px solid #e2e8f0; border-radius: 18px; padding: 22px;
  cursor: pointer; transition: all .25s; text-align: center;
}
.pd-related-card:hover { transform: translateY(-4px); box-shadow: 0 12px 32px rgba(0,0,0,.1); border-color: #e11d48; }
.pd-rc-img   { font-size: 56px; margin-bottom: 12px; }
.pd-rc-name  { font-size: 13px; font-weight: 600; color: #1e293b; margin-bottom: 6px; line-height: 1.4; }
.pd-rc-price { font-size: 16px; font-weight: 800; color: #e11d48; margin-bottom: 6px; }
.pd-rc-stars { font-size: 13px; }

/* ── Responsive ── */
@media (max-width: 900px) {
  .pd-main { grid-template-columns: 1fr; gap: 28px; }
  .pd-related-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 520px) {
  .pd-title { font-size: 20px; }
  .pd-price { font-size: 24px; }
  .pd-related-grid { grid-template-columns: 1fr; }
  .pd-specs { grid-template-columns: 1fr; }
}
</style>
