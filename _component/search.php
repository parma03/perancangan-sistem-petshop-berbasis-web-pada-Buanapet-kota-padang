<div class="search-popup">
    <div class="search-popup-container">
        <form role="search" method="get" class="form-group" action="products.php">
            <input type="search" id="search-form" class="form-control border-0 border-bottom"
                placeholder="Cari produk..." value="" name="search" />
            <button type="submit" class="search-submit border-0 position-absolute bg-white"
                style="top: 15px;right: 15px;">
                <svg class="search" width="24" height="24">
                    <use xlink:href="#search"></use>
                </svg>
            </button>
        </form>

        <h5 class="cat-list-title">Kategori Produk</h5>
        <ul class="cat-list">
            <?php foreach ($productTypes as $type): ?>
                <li class="cat-list-item">
                    <a href="products.php?tipe=<?php echo urlencode($type['tipe_barang']); ?>"
                        title="<?php echo htmlspecialchars($type['tipe_barang']); ?>">
                        <?php echo htmlspecialchars($type['tipe_barang']); ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>