<?php
$page_title = "Yarac - Fashion Store Terpercaya";
require_once 'config/database.php';
require_once 'classes/Product.php';

// Database connection
$database = new Database();
$db = $database->getConnection();

// Initialize product object
$product = new Product($db);

// Get featured products
$featured_products = $product->getFeatured(4);

// Get advertisements
$ads_query = "SELECT * FROM advertisements WHERE active = 1 ORDER BY sort_order ASC";
$ads_stmt = $db->prepare($ads_query);
$ads_stmt->execute();
$advertisements = $ads_stmt->fetchAll(PDO::FETCH_ASSOC);

include 'includes/header.php';
?>

<!-- Home Section -->
<section class="hero" id="home">
    <!-- Enhanced Advertisement Slider -->
    <div class="ad-slider">
        <?php if (!empty($advertisements)): ?>
            <?php foreach ($advertisements as $index => $ad): ?>
                <div class="ad-slide <?php echo $index === 0 ? 'active' : ''; ?>">
                    <img src="assets/images/ads/<?php echo $ad['image']; ?>" alt="<?php echo htmlspecialchars($ad['title']); ?>">
                    <div class="ad-overlay">
                        <div class="ad-content">
                            <h2><?php echo htmlspecialchars($ad['title']); ?></h2>
                            <?php if ($ad['link']): ?>
                                <a href="<?php echo $ad['link']; ?>" class="ad-cta">Shop Now</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <!-- Default slides if no ads in database -->
            <div class="ad-slide active">
                <img src="https://hebbkx1anhila5yf.public.blob.vercel-storage.com/image-S3MvD60JBcm876z45W1nuNKEqL39r3.png" alt="Summer Collection 2024">
                <div class="ad-overlay">
                    <div class="ad-content">
                        <h2>Summer Collection 2024</h2>
                        <a href="products.php?category=casual" class="ad-cta">Shop Now</a>
                    </div>
                </div>
            </div>
            <div class="ad-slide">
                <img src="https://hebbkx1anhila5yf.public.blob.vercel-storage.com/image-S3MvD60JBcm876z45W1nuNKEqL39r3.png" alt="Formal Wear Sale">
                <div class="ad-overlay">
                    <div class="ad-content">
                        <h2>Formal Wear Sale</h2>
                        <a href="products.php?category=formal" class="ad-cta">Shop Now</a>
                    </div>
                </div>
            </div>
            <div class="ad-slide">
                <img src="https://hebbkx1anhila5yf.public.blob.vercel-storage.com/image-S3MvD60JBcm876z45W1nuNKEqL39r3.png" alt="New Arrivals">
                <div class="ad-overlay">
                    <div class="ad-content">
                        <h2>New Arrivals</h2>
                        <a href="products.php" class="ad-cta">Shop Now</a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        
        <div class="ad-controls">
            <button class="ad-prev" onclick="changeSlide(-1)">❮</button>
            <button class="ad-next" onclick="changeSlide(1)">❯</button>
        </div>
        <div class="ad-indicators">
            <?php 
            $slide_count = !empty($advertisements) ? count($advertisements) : 3;
            for ($i = 0; $i < $slide_count; $i++): 
            ?>
                <span class="indicator <?php echo $i === 0 ? 'active' : ''; ?>" onclick="currentSlideSet(<?php echo $i + 1; ?>)"></span>
            <?php endfor; ?>
        </div>
    </div>

    <div class="hero-filters">
        <button class="filter-btn active" data-filter="all">ALL <span>></span></button>
        <button class="filter-btn" data-filter="men">MEN <span>></span></button>
        <button class="filter-btn" data-filter="women">WOMEN <span>></span></button>
        <button class="filter-btn" data-filter="unisex">UNISEX <span>></span></button>
    </div>
</section>

<!-- Featured Products -->
<section class="featured-products">
    <div class="container">
        <h2 class="section-title">Featured Products</h2>
        <div class="products-grid" id="featured-products">
            <?php while ($row = $featured_products->fetch(PDO::FETCH_ASSOC)): ?>
                <div class="product-card fade-in" data-category="<?php echo $row['category']; ?>" data-gender="<?php echo $row['gender']; ?>" data-id="<?php echo $row['id']; ?>">
                    <img src="assets/images/products/<?php echo $row['image']; ?>" 
                         alt="<?php echo htmlspecialchars($row['name']); ?>" 
                         class="product-image"
                         onclick="quickView(<?php echo $row['id']; ?>)">
                    <div class="product-info">
                        <div class="product-category"><?php echo strtoupper($row['category']); ?></div>
                        <h3 class="product-name"><?php echo htmlspecialchars($row['name']); ?></h3>
                        <div class="product-rating">
                            <div class="stars">
                                <?php 
                                $rating = $row['rating'] ?? 0;
                                for ($i = 1; $i <= 5; $i++): 
                                ?>
                                    <span class="star <?php echo $i <= $rating ? '' : 'empty'; ?>">★</span>
                                <?php endfor; ?>
                            </div>
                            <span class="rating-text">(<?php echo $row['total_reviews'] ?? 0; ?> reviews)</span>
                        </div>
                        <div class="product-price">Rp <?php echo number_format($row['price'], 0, ',', '.'); ?></div>
                        <div class="product-actions">
                            <button class="btn-add-cart" onclick="addToCart(<?php echo $row['id']; ?>)">
                                <i class="fas fa-cart-plus"></i> Add to Cart
                            </button>
                            <button class="btn-quick-view" onclick="quickView(<?php echo $row['id']; ?>)">
                                <i class="fas fa-eye"></i> Quick View
                            </button>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
        <div class="view-all">
            <a href="products.php" class="btn-primary">
                <i class="fas fa-arrow-right"></i> View All Products
            </a>
        </div>
    </div>
</section>

<!-- Enhanced About Section -->
<section class="about-section" id="about">
    <div class="container">
        <h2 class="section-title">About Yarac</h2>
        <div class="about-content">
            <div class="about-text">
                <h3>Our Story</h3>
                <p>Yarac didirikan dengan visi untuk menghadirkan fashion berkualitas tinggi yang terjangkau untuk semua kalangan. Sejak tahun 2020, kami telah melayani ribuan pelanggan di seluruh Indonesia dengan komitmen pada kualitas, style, dan kepuasan pelanggan.</p>
                
                <p>Kami percaya bahwa fashion adalah bentuk ekspresi diri yang powerful. Setiap piece dalam koleksi kami dipilih dengan cermat untuk memastikan Anda tampil percaya diri dalam setiap kesempatan.</p>
                
                <h3>Why Choose Yarac?</h3>
                <p>Dengan pengalaman bertahun-tahun di industri fashion, kami memahami kebutuhan dan keinginan pelanggan modern. Tim kami bekerja keras untuk menghadirkan trend terbaru dengan kualitas terbaik.</p>
            </div>
        </div>
    </div>
</section>

<!-- Categories -->
<section class="categories">
    <div class="container">
        <h2 class="section-title">Shop by Category</h2>
        <div class="categories-grid">
            <div class="category-card fade-in" data-category="shirts">
                <i class="fas fa-tshirt" style="font-size: 3rem; color: var(--olive-drab); margin-bottom: 20px;"></i>
                <h3>Shirts</h3>
                <p>Koleksi kemeja terbaru untuk berbagai acara formal dan kasual</p>
            </div>
            <div class="category-card fade-in" data-category="casual">
                <i class="fas fa-user-tie" style="font-size: 3rem; color: var(--olive-drab); margin-bottom: 20px;"></i>
                <h3>Casual Wear</h3>
                <p>Pakaian santai kekinian untuk gaya sehari-hari yang stylish</p>
            </div>
            <div class="category-card fade-in" data-category="formal">
                <i class="fas fa-briefcase" style="font-size: 3rem; color: var(--olive-drab); margin-bottom: 20px;"></i>
                <h3>Formal Wear</h3>
                <p>Pakaian formal elegan untuk penampilan profesional</p>
            </div>
        </div>
    </div>
</section>

<!-- Newsletter -->
<section class="newsletter">
    <div class="container">
        <h2>Stay Updated</h2>
        <p>Dapatkan info terbaru tentang koleksi dan promo menarik dari Yarac</p>
        <form class="newsletter-form" id="newsletter-form" action="api/newsletter.php" method="POST">
            <input type="email" name="email" placeholder="Enter your email address" required>
            <button type="submit">
                <i class="fas fa-paper-plane"></i> Subscribe
            </button>
        </form>
    </div>
</section>

<!-- Enhanced Quick View Modal -->
<div class="modal" id="quick-view-modal">
    <div class="modal-content">
        <span class="close" onclick="closeQuickView()">&times;</span>
        <div class="modal-body">
            <div class="modal-image">
                <img id="modal-product-image" src="/placeholder.svg" alt="">
            </div>
            <div class="modal-info">
                <div class="product-category" id="modal-product-category"></div>
                <h3 id="modal-product-name"></h3>
                <div class="product-rating" id="modal-product-rating">
                    <div class="stars" id="modal-stars"></div>
                    <span class="rating-text" id="modal-rating-text"></span>
                </div>
                <div class="product-price" id="modal-product-price"></div>
                <div class="product-description" id="modal-product-description"></div>
                
                <div class="size-selector">
                    <label for="modal-size">Size:</label>
                    <select id="modal-size">
                        <option value="S">S</option>
                        <option value="M" selected>M</option>
                        <option value="L">L</option>
                        <option value="XL">XL</option>
                    </select>
                </div>
                
                <div class="quantity-selector">
                    <label for="modal-quantity">Quantity:</label>
                    <input type="number" id="modal-quantity" value="1" min="1" max="10">
                </div>
                
                <button class="btn-add-cart-modal" onclick="addToCartFromModal()">
                    <i class="fas fa-cart-plus"></i> Add to Cart
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Enhanced JavaScript for homepage functionality
let currentSlideIndex = 0;
let slideInterval;

// Auto slide functionality
function startSlideShow() {
    slideInterval = setInterval(() => {
        changeSlide(1);
    }, 5000);
}

function stopSlideShow() {
    clearInterval(slideInterval);
}

function changeSlide(direction) {
    const slides = document.querySelectorAll('.ad-slide');
    const indicators = document.querySelectorAll('.indicator');
    
    slides[currentSlideIndex].classList.remove('active');
    indicators[currentSlideIndex].classList.remove('active');
    
    currentSlideIndex += direction;
    
    if (currentSlideIndex >= slides.length) {
        currentSlideIndex = 0;
    } else if (currentSlideIndex < 0) {
        currentSlideIndex = slides.length - 1;
    }
    
    slides[currentSlideIndex].classList.add('active');
    indicators[currentSlideIndex].classList.add('active');
}

function currentSlideSet(slideIndex) {
    const slides = document.querySelectorAll('.ad-slide');
    const indicators = document.querySelectorAll('.indicator');
    
    slides[currentSlideIndex].classList.remove('active');
    indicators[currentSlideIndex].classList.remove('active');
    
    currentSlideIndex = slideIndex - 1;
    
    slides[currentSlideIndex].classList.add('active');
    indicators[currentSlideIndex].classList.add('active');
}

// Enhanced filter functionality
document.addEventListener('DOMContentLoaded', function() {
    const filterBtns = document.querySelectorAll('.filter-btn');
    const productCards = document.querySelectorAll('.product-card');
    
    filterBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const filter = this.getAttribute('data-filter');
            
            // Update active button
            filterBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            
            // Filter products with animation
            productCards.forEach(card => {
                const category = card.getAttribute('data-category');
                const gender = card.getAttribute('data-gender');
                
                if (filter === 'all' || gender === filter) {
                    card.style.display = 'block';
                    card.classList.add('fade-in');
                } else {
                    card.style.display = 'none';
                    card.classList.remove('fade-in');
                }
            });
        });
    });
    
    // Category card click handlers
    document.querySelectorAll('.category-card').forEach(card => {
        card.addEventListener('click', function() {
            const category = this.getAttribute('data-category');
            window.location.href = `products.php?category=${category}`;
        });
    });
    
    // Start slideshow
    startSlideShow();
    
    // Pause slideshow on hover
    const slider = document.querySelector('.ad-slider');
    slider.addEventListener('mouseenter', stopSlideShow);
    slider.addEventListener('mouseleave', startSlideShow);
});

// Newsletter form submission
document.getElementById('newsletter-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('api/newsletter.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Thank you for subscribing!', 'success');
            this.reset();
        } else {
            showNotification(data.message || 'Subscription failed', 'error');
        }
    })
    .catch(error => {
        showNotification('An error occurred', 'error');
    });
});
</script>

<?php include 'includes/footer.php'; ?>
