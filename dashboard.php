<?php
session_start();

// Keamanan: Pastikan hanya admin yang bisa mengakses
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$page_title = "Admin Dashboard - Yarac Fashion Store";
// [FIX] Path CSS disesuaikan untuk direktori utama
$additional_css = ['assets/css/admin.css'];

// [FIX] Path require_once disesuaikan karena file ini ada di root
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

// Ambil statistik awal untuk ditampilkan
$stats_query = "SELECT 
    (SELECT COUNT(*) FROM products) as total_products,
    (SELECT COUNT(*) FROM users WHERE role = 'user') as total_users,
    (SELECT COUNT(*) FROM advertisements) as total_ads,
    (SELECT COUNT(*) FROM newsletter_subscribers) as total_subscribers";
$stats_stmt = $db->prepare($stats_query);
$stats_stmt->execute();
$stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);

// [FIX] Path include disesuaikan
include 'includes/header.php';
?>

<div class="admin-layout" style="padding-top: 80px;"> <aside class="admin-sidebar">
        <div class="sidebar-header">
            <h3><i class="fas fa-tachometer-alt"></i> Admin Panel</h3>
        </div>
        <nav class="sidebar-nav">
            <ul>
                <li><a href="#" class="nav-item active" onclick="showSection('dashboard')"><i class="fas fa-chart-line"></i> Dashboard</a></li>
                <li><a href="#" class="nav-item" onclick="showSection('products')"><i class="fas fa-box"></i> Products</a></li>
                <li><a href="#" class="nav-item" onclick="showSection('advertisements')"><i class="fas fa-bullhorn"></i> Advertisements</a></li>
                <li><a href="#" class="nav-item" onclick="showSection('users')"><i class="fas fa-users"></i> Users</a></li>
                <li><a href="index.php" class="nav-item"><i class="fas fa-arrow-left"></i> Back to Site</a></li>
            </ul>
        </nav>
    </aside>

    <main class="admin-main">
        <section id="dashboard" class="admin-section active">
            <div class="section-header">
                <h1>Dashboard Overview</h1>
                <p>Welcome back, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</p>
            </div>
            <div class="stats-grid">
                <div class="stat-card"><h3><?php echo $stats['total_products']; ?></h3><p>Total Products</p></div>
                <div class="stat-card"><h3><?php echo $stats['total_users']; ?></h3><p>Total Users</p></div>
                <div class="stat-card"><h3><?php echo $stats['total_ads']; ?></h3><p>Advertisements</p></div>
                <div class="stat-card"><h3><?php echo $stats['total_subscribers']; ?></h3><p>Subscribers</p></div>
            </div>
        </section>

        <section id="products" class="admin-section">
            <div class="section-header"><h1>Product Management</h1></div>
            <div class="table-container" id="products-content"><p>Loading products...</p></div>
        </section>

        <section id="advertisements" class="admin-section">
             <div class="section-header"><h1>Advertisement Management</h1></div>
            <div class="ads-grid-container" id="ads-content"><p>Loading advertisements...</p></div>
        </section>

        <section id="users" class="admin-section">
            <div class="section-header"><h1>User Management</h1></div>
            <div class="table-container" id="users-content"><p>Loading users...</p></div>
        </section>
    </main>
</div>

<script>
// Fungsi untuk menampilkan/menyembunyikan bagian
function showSection(sectionId) {
    document.querySelectorAll('.admin-section').forEach(section => {
        section.classList.remove('active');
    });
    document.getElementById(sectionId).classList.add('active');

    document.querySelectorAll('.nav-item').forEach(item => {
        item.classList.remove('active');
    });
    document.querySelector(`.nav-item[onclick="showSection('${sectionId}')"]`).classList.add('active');

    if (sectionId === 'products' && document.getElementById('products-content').innerHTML.includes('Loading')) {
        loadContent('products', 'admin_products.php', displayProducts);
    }
    // Tambahkan logika serupa untuk ads dan users jika diperlukan
}

// Fungsi generik untuk memuat konten
function loadContent(type, apiEndpoint, displayFunction) {
    const container = document.getElementById(`${type}-content`);
    container.innerHTML = `<p>Loading ${type}...</p>`;
    
    fetch(`api/${apiEndpoint}`) // Path sudah benar
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            displayFunction(data, container);
        })
        .catch(error => {
            console.error(`Error loading ${type}:`, error);
            container.innerHTML = `<p>Gagal memuat ${type}. Lihat console untuk detail.</p>`;
        });
}

// Fungsi spesifik untuk menampilkan produk
function displayProducts(data, container) {
    let tableHTML = `<table class="admin-table">
        <thead><tr><th>Image</th><th>Name</th><th>Price</th><th>Stock</th></tr></thead><tbody>`;
    data.forEach(p => {
        tableHTML += `<tr>
            <td><img src="assets/images/products/${p.image}" alt="${p.name}" class="table-image"></td>
            <td>${p.name}</td>
            <td>Rp ${Number(p.price).toLocaleString('id-ID')}</td>
            <td>${p.stock}</td>
        </tr>`;
    });
    container.innerHTML = tableHTML + '</tbody></table>';
}

// Inisialisasi: Tampilkan halaman dashboard saat pertama kali dimuat
document.addEventListener('DOMContentLoaded', () => {
    showSection('dashboard');
});
</script>

<style>
/* CSS untuk Single Page App */
.admin-section { display: none; animation: fadeIn 0.5s; }
.admin-section.active { display: block; }
.table-image { width: 60px; height: 60px; object-fit: cover; border-radius: 8px; }
@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
</style>

</body>
</html>