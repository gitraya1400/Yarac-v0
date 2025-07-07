<?php
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$page_title = "Admin Dashboard - Yarac Fashion Store";
$additional_css = ['admin.css'];

require_once '../config/database.php';
require_once '../classes/Product.php';
require_once '../classes/User.php';

// Database connection
$database = new Database();
$db = $database->getConnection();

// Get statistics
$stats_query = "
    SELECT 
        (SELECT COUNT(*) FROM products) as total_products,
        (SELECT COUNT(*) FROM users WHERE role = 'user') as total_users,
        (SELECT COUNT(*) FROM advertisements) as total_ads,
        (SELECT COUNT(*) FROM newsletter_subscribers) as total_subscribers
";
$stats_stmt = $db->prepare($stats_query);
$stats_stmt->execute();
$stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);

// Get recent products
$recent_products_query = "SELECT * FROM products ORDER BY created_at DESC LIMIT 5";
$recent_products_stmt = $db->prepare($recent_products_query);
$recent_products_stmt->execute();
$recent_products = $recent_products_stmt->fetchAll(PDO::FETCH_ASSOC);

// Get sales data for chart (mock data for demo)
$sales_data = [
    'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
    'data' => [120, 190, 300, 500, 200, 300]
];

include '../includes/header.php';
?>

<div class="admin-dashboard">
    <div class="admin-container">
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <div class="sidebar-header">
                <h2><i class="fas fa-tachometer-alt"></i> Admin Panel</h2>
            </div>
            <nav class="sidebar-nav">
                <a href="#dashboard" class="nav-item active" data-section="dashboard">
                    <i class="fas fa-chart-line"></i> Dashboard
                </a>
                <a href="#products" class="nav-item" data-section="products">
                    <i class="fas fa-box"></i> Products
                </a>
                <a href="#advertisements" class="nav-item" data-section="advertisements">
                    <i class="fas fa-bullhorn"></i> Advertisements
                </a>
                <a href="#users" class="nav-item" data-section="users">
                    <i class="fas fa-users"></i> Users
                </a>
                <a href="#analytics" class="nav-item" data-section="analytics">
                    <i class="fas fa-chart-bar"></i> Analytics
                </a>
                <a href="#settings" class="nav-item" data-section="settings">
                    <i class="fas fa-cog"></i> Settings
                </a>
            </nav>
            <div class="sidebar-footer">
                <a href="../index.php" class="btn-back-to-site">
                    <i class="fas fa-arrow-left"></i> Back to Site
                </a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="admin-main">
            <!-- Dashboard Section -->
            <section id="dashboard" class="admin-section active">
                <div class="section-header">
                    <h1>Dashboard Overview</h1>
                    <p>Welcome back, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</p>
                </div>

                <!-- Stats Cards -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon products">
                            <i class="fas fa-box"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $stats['total_products']; ?></h3>
                            <p>Total Products</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon users">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $stats['total_users']; ?></h3>
                            <p>Total Users</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon ads">
                            <i class="fas fa-bullhorn"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $stats['total_ads']; ?></h3>
                            <p>Advertisements</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon subscribers">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $stats['total_subscribers']; ?></h3>
                            <p>Newsletter Subscribers</p>
                        </div>
                    </div>
                </div>

                <!-- Charts and Recent Activity -->
                <div class="dashboard-grid">
                    <div class="chart-container">
                        <h3>Sales Overview</h3>
                        <canvas id="salesChart"></canvas>
                    </div>
                    <div class="recent-activity">
                        <h3>Recent Products</h3>
                        <div class="activity-list">
                            <?php foreach ($recent_products as $product): ?>
                            <div class="activity-item">
                                <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="Product">
                                <div class="activity-info">
                                    <h4><?php echo htmlspecialchars($product['name']); ?></h4>
                                    <p>$<?php echo number_format($product['price'], 2); ?></p>
                                    <small><?php echo date('M j, Y', strtotime($product['created_at'])); ?></small>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Products Section -->
            <section id="products" class="admin-section">
                <div class="section-header">
                    <h1>Product Management</h1>
                    <button class="btn-primary" onclick="openProductModal()">
                        <i class="fas fa-plus"></i> Add New Product
                    </button>
                </div>

                <div class="table-container">
                    <table class="admin-table" id="productsTable">
                        <thead>
                            <tr>
                                <th>Image</th>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Stock</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Products will be loaded via AJAX -->
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- Advertisements Section -->
            <section id="advertisements" class="admin-section">
                <div class="section-header">
                    <h1>Advertisement Management</h1>
                    <button class="btn-primary" onclick="openAdModal()">
                        <i class="fas fa-plus"></i> Add New Ad
                    </button>
                </div>

                <div class="ads-grid" id="adsGrid">
                    <!-- Ads will be loaded via AJAX -->
                </div>
            </section>

            <!-- Users Section -->
            <section id="users" class="admin-section">
                <div class="section-header">
                    <h1>User Management</h1>
                </div>

                <div class="table-container">
                    <table class="admin-table" id="usersTable">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Joined</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Users will be loaded via AJAX -->
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- Analytics Section -->
            <section id="analytics" class="admin-section">
                <div class="section-header">
                    <h1>Analytics & Reports</h1>
                </div>

                <div class="analytics-grid">
                    <div class="analytics-card">
                        <h3>Popular Products</h3>
                        <canvas id="popularProductsChart"></canvas>
                    </div>
                    <div class="analytics-card">
                        <h3>User Growth</h3>
                        <canvas id="userGrowthChart"></canvas>
                    </div>
                    <div class="analytics-card">
                        <h3>Category Distribution</h3>
                        <canvas id="categoryChart"></canvas>
                    </div>
                    <div class="analytics-card">
                        <h3>Monthly Revenue</h3>
                        <canvas id="revenueChart"></canvas>
                    </div>
                </div>
            </section>

            <!-- Settings Section -->
            <section id="settings" class="admin-section">
                <div class="section-header">
                    <h1>Settings</h1>
                </div>

                <div class="settings-grid">
                    <div class="settings-card">
                        <h3>Site Settings</h3>
                        <form id="siteSettingsForm">
                            <div class="form-group">
                                <label>Site Name</label>
                                <input type="text" value="Yarac Fashion Store" name="site_name">
                            </div>
                            <div class="form-group">
                                <label>Contact Email</label>
                                <input type="email" value="info@yaracfashion.com" name="contact_email">
                            </div>
                            <div class="form-group">
                                <label>WhatsApp Number</label>
                                <input type="text" value="+1234567890" name="whatsapp_number">
                            </div>
                            <button type="submit" class="btn-primary">Save Settings</button>
                        </form>
                    </div>

                    <div class="settings-card">
                        <h3>Newsletter Settings</h3>
                        <form id="newsletterSettingsForm">
                            <div class="form-group">
                                <label>Newsletter Title</label>
                                <input type="text" value="Stay Updated with Yarac Fashion" name="newsletter_title">
                            </div>
                            <div class="form-group">
                                <label>Newsletter Description</label>
                                <textarea name="newsletter_description">Get the latest fashion trends and exclusive offers delivered to your inbox.</textarea>
                            </div>
                            <button type="submit" class="btn-primary">Save Settings</button>
                        </form>
                    </div>
                </div>
            </section>
        </main>
    </div>
</div>

<!-- Product Modal -->
<div id="productModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeProductModal()">&times;</span>
        <h2 id="productModalTitle">Add New Product</h2>
        <form id="productForm" enctype="multipart/form-data">
            <input type="hidden" id="productId" name="product_id">
            <div class="form-row">
                <div class="form-group">
                    <label>Product Name</label>
                    <input type="text" id="productName" name="name" required>
                </div>
                <div class="form-group">
                    <label>Category</label>
                    <select id="productCategory" name="category" required>
                        <option value="">Select Category</option>
                        <option value="men">Men</option>
                        <option value="women">Women</option>
                        <option value="accessories">Accessories</option>
                        <option value="shoes">Shoes</option>
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Price ($)</label>
                    <input type="number" id="productPrice" name="price" step="0.01" required>
                </div>
                <div class="form-group">
                    <label>Stock Quantity</label>
                    <input type="number" id="productStock" name="stock" required>
                </div>
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea id="productDescription" name="description" rows="4"></textarea>
            </div>
            <div class="form-group">
                <label>Product Image</label>
                <input type="file" id="productImage" name="image" accept="image/*">
                <div id="imagePreview"></div>
            </div>
            <div class="form-actions">
                <button type="button" onclick="closeProductModal()" class="btn-secondary">Cancel</button>
                <button type="submit" class="btn-primary">Save Product</button>
            </div>
        </form>
    </div>
</div>

<!-- Advertisement Modal -->
<div id="adModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeAdModal()">&times;</span>
        <h2 id="adModalTitle">Add New Advertisement</h2>
        <form id="adForm" enctype="multipart/form-data">
            <input type="hidden" id="adId" name="ad_id">
            <div class="form-group">
                <label>Advertisement Title</label>
                <input type="text" id="adTitle" name="title" required>
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea id="adDescription" name="description" rows="3"></textarea>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Link URL</label>
                    <input type="url" id="adLink" name="link_url">
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select id="adStatus" name="status">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label>Advertisement Image</label>
                <input type="file" id="adImage" name="image" accept="image/*" required>
                <div id="adImagePreview"></div>
            </div>
            <div class="form-actions">
                <button type="button" onclick="closeAdModal()" class="btn-secondary">Cancel</button>
                <button type="submit" class="btn-primary">Save Advertisement</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Admin Dashboard JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Initialize dashboard
    initializeDashboard();
    loadProducts();
    loadAdvertisements();
    loadUsers();
    initializeCharts();
});

function initializeDashboard() {
    // Navigation handling
    const navItems = document.querySelectorAll('.nav-item');
    const sections = document.querySelectorAll('.admin-section');
    
    navItems.forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Remove active class from all nav items and sections
            navItems.forEach(nav => nav.classList.remove('active'));
            sections.forEach(section => section.classList.remove('active'));
            
            // Add active class to clicked nav item
            this.classList.add('active');
            
            // Show corresponding section
            const sectionId = this.getAttribute('data-section');
            document.getElementById(sectionId).classList.add('active');
        });
    });
}

function initializeCharts() {
    // Sales Chart
    const salesCtx = document.getElementById('salesChart').getContext('2d');
    new Chart(salesCtx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($sales_data['labels']); ?>,
            datasets: [{
                label: 'Sales',
                data: <?php echo json_encode($sales_data['data']); ?>,
                borderColor: '#2b3e34',
                backgroundColor: 'rgba(43, 62, 52, 0.1)',
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
}

// Product Management Functions
function loadProducts() {
    fetch('../api/admin_products.php')
        .then(response => response.json())
        .then(data => {
            const tbody = document.querySelector('#productsTable tbody');
            tbody.innerHTML = '';
            
            data.forEach(product => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td><img src="${product.image_url}" alt="${product.name}" class="table-image"></td>
                    <td>${product.name}</td>
                    <td>${product.category}</td>
                    <td>$${parseFloat(product.price).toFixed(2)}</td>
                    <td>${product.stock}</td>
                    <td><span class="status ${product.stock > 0 ? 'active' : 'inactive'}">${product.stock > 0 ? 'In Stock' : 'Out of Stock'}</span></td>
                    <td>
                        <button onclick="editProduct(${product.id})" class="btn-edit">Edit</button>
                        <button onclick="deleteProduct(${product.id})" class="btn-delete">Delete</button>
                    </td>
                `;
                tbody.appendChild(row);
            });
        })
        .catch(error => console.error('Error loading products:', error));
}

function openProductModal(productId = null) {
    const modal = document.getElementById('productModal');
    const title = document.getElementById('productModalTitle');
    
    if (productId) {
        title.textContent = 'Edit Product';
        // Load product data for editing
        loadProductData(productId);
    } else {
        title.textContent = 'Add New Product';
        document.getElementById('productForm').reset();
        document.getElementById('productId').value = '';
    }
    
    modal.style.display = 'block';
}

function closeProductModal() {
    document.getElementById('productModal').style.display = 'none';
}

function editProduct(productId) {
    openProductModal(productId);
}

function deleteProduct(productId) {
    if (confirm('Are you sure you want to delete this product?')) {
        fetch('../api/admin_products.php', {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ id: productId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Product deleted successfully', 'success');
                loadProducts();
            } else {
                showNotification('Error deleting product', 'error');
            }
        });
    }
}

// Advertisement Management Functions
function loadAdvertisements() {
    fetch('../api/admin_ads.php')
        .then(response => response.json())
        .then(data => {
            const grid = document.getElementById('adsGrid');
            grid.innerHTML = '';
            
            data.forEach(ad => {
                const adCard = document.createElement('div');
                adCard.className = 'ad-card';
                adCard.innerHTML = `
                    <img src="${ad.image_url}" alt="${ad.title}">
                    <div class="ad-info">
                        <h3>${ad.title}</h3>
                        <p>${ad.description}</p>
                        <span class="status ${ad.status}">${ad.status}</span>
                        <div class="ad-actions">
                            <button onclick="editAd(${ad.id})" class="btn-edit">Edit</button>
                            <button onclick="deleteAd(${ad.id})" class="btn-delete">Delete</button>
                        </div>
                    </div>
                `;
                grid.appendChild(adCard);
            });
        })
        .catch(error => console.error('Error loading advertisements:', error));
}

function openAdModal(adId = null) {
    const modal = document.getElementById('adModal');
    const title = document.getElementById('adModalTitle');
    
    if (adId) {
        title.textContent = 'Edit Advertisement';
        // Load ad data for editing
        loadAdData(adId);
    } else {
        title.textContent = 'Add New Advertisement';
        document.getElementById('adForm').reset();
        document.getElementById('adId').value = '';
    }
    
    modal.style.display = 'block';
}

function closeAdModal() {
    document.getElementById('adModal').style.display = 'none';
}

// User Management Functions
function loadUsers() {
    fetch('../api/admin_users.php')
        .then(response => response.json())
        .then(data => {
            const tbody = document.querySelector('#usersTable tbody');
            tbody.innerHTML = '';
            
            data.forEach(user => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${user.first_name} ${user.last_name}</td>
                    <td>${user.email}</td>
                    <td><span class="role ${user.role}">${user.role}</span></td>
                    <td>${new Date(user.created_at).toLocaleDateString()}</td>
                    <td><span class="status active">Active</span></td>
                    <td>
                        <button onclick="viewUser(${user.id})" class="btn-view">View</button>
                        ${user.role !== 'admin' ? `<button onclick="deleteUser(${user.id})" class="btn-delete">Delete</button>` : ''}
                    </td>
                `;
                tbody.appendChild(row);
            });
        })
        .catch(error => console.error('Error loading users:', error));
}

// Utility Functions
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.textContent = message;
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 3000);
}

// Form Submissions
document.getElementById('productForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const productId = document.getElementById('productId').value;
    
    const url = productId ? '../api/admin_products.php?action=update' : '../api/admin_products.php?action=create';
    
    fetch(url, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(productId ? 'Product updated successfully' : 'Product created successfully', 'success');
            closeProductModal();
            loadProducts();
        } else {
            showNotification('Error saving product', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error saving product', 'error');
    });
});

document.getElementById('adForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const adId = document.getElementById('adId').value;
    
    const url = adId ? '../api/admin_ads.php?action=update' : '../api/admin_ads.php?action=create';
    
    fetch(url, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(adId ? 'Advertisement updated successfully' : 'Advertisement created successfully', 'success');
            closeAdModal();
            loadAdvertisements();
        } else {
            showNotification('Error saving advertisement', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error saving advertisement', 'error');
    });
});

// Image Preview Functions
document.getElementById('productImage').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('imagePreview').innerHTML = `<img src="${e.target.result}" alt="Preview" style="max-width: 200px; max-height: 200px; object-fit: cover; border-radius: 8px;">`;
        };
        reader.readAsDataURL(file);
    }
});

document.getElementById('adImage').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('adImagePreview').innerHTML = `<img src="${e.target.result}" alt="Preview" style="max-width: 200px; max-height: 200px; object-fit: cover; border-radius: 8px;">`;
        };
        reader.readAsDataURL(file);
    }
});
</script>

<?php include '../includes/footer.php'; ?>
