<template>
  <!-- ─── HERO ─── -->
  <section class="hero">
    <div class="hero-bg"></div>
    <div class="hero-grid"></div>
    <div class="hero-orb hero-orb-1"></div>
    <div class="hero-orb hero-orb-2"></div>
    <div class="hero-inner">
      <div class="hero-content">
        <div class="hero-eyebrow">Premium Collectible Models</div>
        <h1 class="hero-heading">
          Discover Your Next<br />
          <em>Extraordinary</em><br />
          Model
        </h1>
        <p class="hero-subtext">
          Handpicked 1:18, 1:24 and 1:43 scale masterpieces — from iconic
          architecture to limited-edition automotive legends. Precision
          engineered for the discerning collector.
        </p>
        <div class="hero-stats">
          <div>
            <div class="hero-stat-value">2<span>K</span>+</div>
            <div class="hero-stat-label">Models in stock</div>
          </div>
          <div>
            <div class="hero-stat-value">48<span>h</span></div>
            <div class="hero-stat-label">Fast dispatch</div>
          </div>
          <div>
            <div class="hero-stat-value">4.9<span>★</span></div>
            <div class="hero-stat-label">Avg. rating</div>
          </div>
        </div>
      </div>
      <div class="hero-visual">
        <div class="model-scene">
          <div class="hero-badge">★ Editor's Pick</div>
          <div class="model-float mf-1">
            <div class="model-float-inner">
              <div class="model-float-img">🏛️</div>
              <div class="model-float-info">
                <div class="model-float-name">Burj Khalifa</div>
                <div class="model-float-price">$189</div>
              </div>
            </div>
          </div>
          <div class="model-float mf-2">
            <div class="model-float-inner">
              <div class="model-float-img">🏎️</div>
              <div class="model-float-info">
                <div class="model-float-name">Ferrari F40</div>
                <div class="model-float-price">$249</div>
              </div>
            </div>
          </div>
          <div class="model-float mf-3">
            <div class="model-float-inner">
              <div class="model-float-img">✈️</div>
              <div class="model-float-info">
                <div class="model-float-name">Boeing 747</div>
                <div class="model-float-price">$129</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ─── SEARCH & FILTER ─── -->
  <div class="search-section">
    <div class="search-inner">
      <div class="search-field-wrap">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="11" cy="11" r="8" />
          <path d="M21 21l-4.35-4.35" />
        </svg>
        <input
          class="search-field"
          type="text"
          placeholder="Search by name, brand, or scale…"
        />
      </div>
      <select class="select-filter">
        <option>All Categories</option>
        <option>Automotive</option>
        <option>Architecture</option>
        <option>Aviation</option>
        <option>Military</option>
        <option>Ships & Vessels</option>
      </select>
      <select class="select-filter">
        <option>All Scales</option>
        <option>1:18</option>
        <option>1:24</option>
        <option>1:43</option>
        <option>1:72</option>
      </select>
      <select class="select-filter">
        <option>Sort: Featured</option>
        <option>Price: Low–High</option>
        <option>Price: High–Low</option>
        <option>Newest First</option>
      </select>
      <button class="btn-explore">
        <svg
          width="15"
          height="15"
          viewBox="0 0 24 24"
          fill="none"
          stroke="currentColor"
          stroke-width="2.5"
        >
          <circle cx="11" cy="11" r="8" />
          <path d="M21 21l-4.35-4.35" />
        </svg>
        Explore
      </button>
    </div>
  </div>

  <!-- ─── FEATURES ─── -->
  <div class="features-bg">
    <div style="max-width: 1280px; margin: 0 auto">
      <div class="features-grid">
        <div class="feature-item">
          <div class="feature-icon">🚚</div>
          <div>
            <div class="feature-title">Free Shipping</div>
            <div class="feature-desc">
              On all orders over $80. Express 48h available.
            </div>
          </div>
        </div>
        <div class="feature-item">
          <div class="feature-icon">🔒</div>
          <div>
            <div class="feature-title">Secure Packaging</div>
            <div class="feature-desc">
              Triple-layer foam protection, guaranteed intact.
            </div>
          </div>
        </div>
        <div class="feature-item">
          <div class="feature-icon">✅</div>
          <div>
            <div class="feature-title">Authenticity Guarantee</div>
            <div class="feature-desc">
              Every model verified and certificate included.
            </div>
          </div>
        </div>
        <div class="feature-item">
          <div class="feature-icon">↩️</div>
          <div>
            <div class="feature-title">30-Day Returns</div>
            <div class="feature-desc">Hassle-free returns. No questions asked.</div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- ─── FEATURED MODELS ─── -->
  <div class="products-bg">
    <div class="section">
      <div class="section-header">
        <div class="section-title-wrap">
          <div class="section-eyebrow">Hand-Selected</div>
          <h2 class="section-title">Featured Models</h2>
        </div>
        <a href="#" class="btn-view-all">View All →</a>
      </div>

      <div class="cat-tabs">
        <button class="cat-tab active">All</button>
        <button class="cat-tab">Automotive</button>
        <button class="cat-tab">Architecture</button>
        <button class="cat-tab">Aviation</button>
        <button class="cat-tab">Military</button>
        <button class="cat-tab">Limited Edition</button>
      </div>

      <div class="product-grid">
        <!-- Card 1 -->
        <div class="product-card">
          <div class="product-badge badge-new">New</div>
          <div class="product-img-wrap">
            <div class="product-emoji">🏎️</div>
          </div>
          <div class="product-info">
            <div class="product-brand">Bburago</div>
            <div class="product-name">Ferrari SF90 Stradale</div>
            <div class="product-scale">Scale 1:18 · Die-cast</div>
            <div class="product-rating">
              <span class="stars">★★★★★</span>
              <span>(142)</span>
            </div>
            <div class="product-footer">
              <div>
                <span class="product-price-orig">$289</span>
                <span class="product-price">$249</span>
              </div>
              <button class="btn-view">View</button>
            </div>
          </div>
        </div>

        <!-- Card 2 -->
        <div class="product-card">
          <div class="product-badge badge-ltd">Limited</div>
          <div class="product-img-wrap">
            <div class="product-emoji">🏛️</div>
          </div>
          <div class="product-info">
            <div class="product-brand">Architecture Studio</div>
            <div class="product-name">Empire State Building</div>
            <div class="product-scale">Scale 1:500 · Resin</div>
            <div class="product-rating">
              <span class="stars">★★★★★</span>
              <span>(87)</span>
            </div>
            <div class="product-footer">
              <div>
                <span class="product-price-orig"></span>
                <span class="product-price">$189</span>
              </div>
              <button class="btn-view">View</button>
            </div>
          </div>
        </div>

        <!-- Card 3 -->
        <div class="product-card">
          <div class="product-badge badge-sale">−20%</div>
          <div class="product-img-wrap">
            <div class="product-emoji">✈️</div>
          </div>
          <div class="product-info">
            <div class="product-brand">Herpa Wings</div>
            <div class="product-name">Airbus A380 Lufthansa</div>
            <div class="product-scale">Scale 1:200 · Plastic</div>
            <div class="product-rating">
              <span class="stars">★★★★☆</span>
              <span>(203)</span>
            </div>
            <div class="product-footer">
              <div>
                <span class="product-price-orig">$159</span>
                <span class="product-price">$127</span>
              </div>
              <button class="btn-view">View</button>
            </div>
          </div>
        </div>

        <!-- Card 4 -->
        <div class="product-card">
          <div class="product-img-wrap">
            <div class="product-emoji">⛵</div>
          </div>
          <div class="product-info">
            <div class="product-brand">Mamoli</div>
            <div class="product-name">HMS Victory — Admiral Nelson</div>
            <div class="product-scale">Scale 1:100 · Wood Kit</div>
            <div class="product-rating">
              <span class="stars">★★★★★</span>
              <span>(59)</span>
            </div>
            <div class="product-footer">
              <div>
                <span class="product-price-orig"></span>
                <span class="product-price">$349</span>
              </div>
              <button class="btn-view">View</button>
            </div>
          </div>
        </div>

        <!-- Card 5 -->
        <div class="product-card">
          <div class="product-badge badge-new">New</div>
          <div class="product-img-wrap">
            <div class="product-emoji">🚀</div>
          </div>
          <div class="product-info">
            <div class="product-brand">Revell</div>
            <div class="product-name">Saturn V Apollo Rocket</div>
            <div class="product-scale">Scale 1:96 · Plastic Kit</div>
            <div class="product-rating">
              <span class="stars">★★★★★</span>
              <span>(311)</span>
            </div>
            <div class="product-footer">
              <div>
                <span class="product-price-orig">$99</span>
                <span class="product-price">$79</span>
              </div>
              <button class="btn-view">View</button>
            </div>
          </div>
        </div>

        <!-- Card 6 -->
        <div class="product-card">
          <div class="product-badge badge-ltd">Limited</div>
          <div class="product-img-wrap">
            <div class="product-emoji">🏰</div>
          </div>
          <div class="product-info">
            <div class="product-brand">Wrebbit 3D</div>
            <div class="product-name">Neuschwanstein Castle</div>
            <div class="product-scale">Scale 1:300 · Resin</div>
            <div class="product-rating">
              <span class="stars">★★★★☆</span>
              <span>(44)</span>
            </div>
            <div class="product-footer">
              <div>
                <span class="product-price-orig"></span>
                <span class="product-price">$219</span>
              </div>
              <button class="btn-view">View</button>
            </div>
          </div>
        </div>

        <!-- Card 7 -->
        <div class="product-card">
          <div class="product-badge badge-sale">−15%</div>
          <div class="product-img-wrap">
            <div class="product-emoji">🚂</div>
          </div>
          <div class="product-info">
            <div class="product-brand">Märklin</div>
            <div class="product-name">ICE 3 High-Speed Train</div>
            <div class="product-scale">Scale H0 · Die-cast</div>
            <div class="product-rating">
              <span class="stars">★★★★★</span>
              <span>(177)</span>
            </div>
            <div class="product-footer">
              <div>
                <span class="product-price-orig">$199</span>
                <span class="product-price">$169</span>
              </div>
              <button class="btn-view">View</button>
            </div>
          </div>
        </div>

        <!-- Card 8 -->
        <div class="product-card">
          <div class="product-img-wrap">
            <div class="product-emoji">🚁</div>
          </div>
          <div class="product-info">
            <div class="product-brand">Forces of Valor</div>
            <div class="product-name">UH-60 Black Hawk</div>
            <div class="product-scale">Scale 1:72 · Die-cast</div>
            <div class="product-rating">
              <span class="stars">★★★★☆</span>
              <span>(93)</span>
            </div>
            <div class="product-footer">
              <div>
                <span class="product-price-orig"></span>
                <span class="product-price">$139</span>
              </div>
              <button class="btn-view">View</button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- ─── PROMO BANNER ─── -->
  <div class="banner-strip">
    <div class="banner-strip-inner">
      <div class="banner-content-left">
        <span class="banner-tag">New Collection Drop</span>
        <h2 class="banner-title">Automotive<br /><em>Legends</em><br />Series 2025</h2>
        <p class="banner-desc">
          Twelve iconic machines from the golden era of motorsport. Each cast in
          precise 1:18 scale with photo-etched detailing and opening components.
        </p>
        <button class="btn-banner">Shop the Collection →</button>
      </div>
      <div class="banner-visual">
        <div class="banner-deco"></div>
        <div class="banner-deco banner-deco-2"></div>
        <div class="banner-model">
          <span class="banner-model-emoji">🏎️</span>
          <div class="banner-model-name">Bugatti Chiron</div>
          <div class="banner-model-price">$349</div>
        </div>
        <div class="banner-model">
          <span class="banner-model-emoji">🚗</span>
          <div class="banner-model-name">Porsche 959</div>
          <div class="banner-model-price">$279</div>
        </div>
        <div class="banner-model">
          <span class="banner-model-emoji">🏁</span>
          <div class="banner-model-name">McLaren F1</div>
          <div class="banner-model-price">$319</div>
        </div>
      </div>
    </div>
  </div>
</template>
<script>
export default {};
</script>
<style></style>
