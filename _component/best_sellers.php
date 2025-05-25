<!-- Best Sellers Section -->
<section id="best-sellers" class="best-sellers product-carousel py-5 position-relative overflow-hidden">
    <div class="container">
        <div class="d-flex flex-wrap justify-content-between align-items-center mt-5 mb-3">
            <h4 class="text-uppercase">Produk Terlaris</h4>
            <a href="products.php?sort=terlaris" class="btn-link">Lihat Semua Produk Terlaris</a>
        </div>

        <?php if (!empty($bestSellers)): ?>
            <div class="swiper product-swiper open-up" data-aos="zoom-out">
                <div class="swiper-wrapper d-flex">
                    <?php foreach ($bestSellers as $product): ?>
                        <div class="swiper-slide">
                            <div class="product-item">
                                <div class="product-card h-100">
                                    <div class="product-image-container position-relative">
                                        <a href="product-detail.php?id=<?php echo $product['id_barang']; ?>">
                                            <img src="assets/uploads/barang/<?php echo htmlspecialchars($product['foto_barang']); ?>"
                                                alt="<?php echo htmlspecialchars($product['nama_barang']); ?>"
                                                class="product-image">
                                        </a>
                                        <?php if ($product['total_terjual'] > 0): ?>
                                            <span class="product-badge badge-bestseller">
                                                <i class="icon-fire"></i> Terlaris
                                            </span>
                                        <?php endif; ?>
                                        <div class="product-overlay">
                                            <button class="btn btn-primary btn-sm add-to-cart-btn"
                                                data-id="<?php echo $product['id_barang']; ?>">
                                                <i class="icon-shopping-cart"></i>
                                                Tambah ke Keranjang
                                            </button>
                                        </div>
                                    </div>
                                    <div class="product-info">
                                        <div class="product-category">
                                            <?php echo htmlspecialchars($product['tipe_barang']); ?>
                                        </div>
                                        <h5 class="product-title">
                                            <a href="product-detail.php?id=<?php echo $product['id_barang']; ?>">
                                                <?php echo htmlspecialchars($product['nama_barang']); ?>
                                            </a>
                                        </h5>
                                        <div class="product-price">
                                            <?php echo formatPrice($product['harga_barang']); ?>
                                        </div>
                                        <div class="product-stats">
                                            <small class="text-success">
                                                <i class="icon-check"></i>
                                                <?php echo $product['total_terjual']; ?> Terjual
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="swiper-pagination"></div>
            </div>
        <?php else: ?>
            <div class="text-center py-4">
                <div class="empty-state">
                    <i class="icon-trending-up" style="font-size: 3rem; color: #ddd;"></i>
                    <p class="text-muted mt-3">Belum ada data penjualan</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>
<style>
    /* Product Card Styles */
    .product-card {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
        border: 1px solid #f0f0f0;
        overflow: hidden;
    }

    .product-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    }

    /* Product Image Container */
    .product-image-container {
        position: relative;
        width: 100%;
        height: 280px;
        overflow: hidden;
        background: #f8f9fa;
    }

    .product-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center;
        transition: transform 0.3s ease;
    }

    .product-card:hover .product-image {
        transform: scale(1.05);
    }

    /* Product Badge */
    .product-badge {
        position: absolute;
        top: 12px;
        right: 12px;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        z-index: 2;
    }

    .badge-new {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }

    .badge-bestseller {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        color: white;
    }

    /* Product Overlay */
    .product-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .product-card:hover .product-overlay {
        opacity: 1;
    }

    .add-to-cart-btn {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        border-radius: 25px;
        padding: 10px 20px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        transform: translateY(20px);
        transition: all 0.3s ease;
    }

    .product-card:hover .add-to-cart-btn {
        transform: translateY(0);
    }

    .add-to-cart-btn:hover {
        background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
        transform: translateY(-2px);
    }

    /* Product Info */
    .product-info {
        padding: 20px;
    }

    .product-category {
        color: #6c757d;
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 8px;
        font-weight: 500;
    }

    .product-title {
        margin: 0 0 12px 0;
        font-size: 1.1rem;
        font-weight: 600;
        line-height: 1.3;
    }

    .product-title a {
        color: #2c3e50;
        text-decoration: none;
        transition: color 0.3s ease;
    }

    .product-title a:hover {
        color: #667eea;
    }

    .product-price {
        font-size: 1.2rem;
        font-weight: 700;
        color: #2c3e50;
        margin-bottom: 8px;
    }

    .product-stats {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .product-stats small {
        display: flex;
        align-items: center;
        gap: 4px;
    }

    /* Empty State */
    .empty-state {
        padding: 40px 20px;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .product-image-container {
            height: 220px;
        }

        .product-info {
            padding: 15px;
        }

        .product-title {
            font-size: 1rem;
        }

        .product-price {
            font-size: 1.1rem;
        }
    }

    /* Swiper Customization */
    .product-swiper .swiper-pagination-bullet {
        background: #667eea;
        opacity: 0.3;
    }

    .product-swiper .swiper-pagination-bullet-active {
        opacity: 1;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }

    /* Loading State for Add to Cart */
    .add-to-cart-btn.loading {
        pointer-events: none;
        opacity: 0.7;
    }

    .add-to-cart-btn.success {
        background: linear-gradient(135deg, #56ab2f 0%, #a8e6cf 100%);
    }

    .add-to-cart-btn.success:hover {
        background: linear-gradient(135deg, #a8e6cf 0%, #56ab2f 100%);
    }
</style>