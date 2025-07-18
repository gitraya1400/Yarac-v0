<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : 'Yarac - Fashion Store'; ?></title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <?php if(isset($additional_css)): ?>
        <?php foreach($additional_css as $css): ?>
            <link rel="stylesheet" href="assets/css/<?php echo $css; ?>">
        <?php endforeach; ?>
    <?php endif; ?>
    <link href="https://fonts.googleapis.com/css2?family=League+Spartan:wght@300;400;500;600;700;800;900&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <meta name="description" content="Yarac Fashion Store - Koleksi fashion terbaru dengan kualitas premium">
    <meta name="keywords" content="fashion, clothing, style, yarac, baju, kemeja, jaket">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar" id="navbar">
        <div class="nav-container">
            <div class="nav-links left">
                <a href="index.php">HOME</a>
                <a href="products.php">PRODUCTS</a>
                <a href="index.php#about">ABOUT</a>
            </div>
            <div class="nav-logo">
                <img src="assets/images/Yarac LOgo.png" alt="Yarac Logo" id="logo">
            </div>
            <div class="nav-auth">
                <?php if(isset($_SESSION['user_id'])): ?>
                    <a href="#" class="sign-in-btn" id="auth-btn" onclick="showProfile()">
                        <i class="fas fa-user-circle"></i>
                        <span><?php echo explode(' ', $_SESSION['user_name'])[0]; ?></span>
                    </a>
                <?php else: ?>
                    <a href="login.php" class="sign-in-btn" id="auth-btn">SIGN IN</a>
                <?php endif; ?>
                <div class="cart-icon" onclick="toggleCart()">
                    <i class="fas fa-shopping-cart"></i>
                    <span class="cart-count" id="cart-count">0</span>
                </div>
            </div>
        </div>
    </nav>

    <!-- Profile Popup -->
    <?php if(isset($_SESSION['user_id'])): ?>
    <div class="profile-popup" id="profile-popup">
        <div class="profile-content">
            <span class="close-profile" onclick="closeProfile()">&times;</span>
            <div class="profile-header">
                <i class="fas fa-user-circle"></i>
                <div>
                    <h3 id="profile-name"><?php echo $_SESSION['user_name']; ?></h3>
                    <p style="margin: 0; color: var(--moss-green); font-size: 14px;"><?php echo $_SESSION['user_email']; ?></p>
                </div>
            </div>
            <div class="profile-body" id="profile-body">
                <?php if($_SESSION['user_role'] == 'admin'): ?>
                    <div class="profile-section">
                        <h4>Admin Panel</h4>
                        <div class="admin-controls">
                            <button class="admin-btn" onclick="location.href='admin/products.php'">
                                <i class="fas fa-box"></i> Manage Products
                            </button>
                            <button class="admin-btn" onclick="location.href='admin/advertisements.php'">
                                <i class="fas fa-ad"></i> Manage Ads
                            </button>
                            <button class="admin-btn" onclick="location.href='admin/orders.php'">
                                <i class="fas fa-shopping-bag"></i> View Orders
                            </button>
                            <button class="admin-btn" onclick="location.href='admin/users.php'">
                                <i class="fas fa-users"></i> Manage Users
                            </button>
                            <button class="admin-btn" onclick="location.href='profile.php'">
                                <i class="fas fa-user-edit"></i> Edit Profile
                            </button>
                            <button class="admin-btn" onclick="logout()">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </button>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="profile-section">
                        <h4>My Account</h4>
                        <div class="admin-controls">
                            <button class="admin-btn" onclick="location.href='profile.php'">
                                <i class="fas fa-user-edit"></i> Edit Profile
                            </button>
                            <button class="admin-btn" onclick="location.href='orders.php'">
                                <i class="fas fa-shopping-bag"></i> My Orders
                            </button>
                            <button class="admin-btn" onclick="location.href='change-password.php'">
                                <i class="fas fa-key"></i> Change Password
                            </button>
                            <button class="admin-btn" onclick="logout()">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </button>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Cart Dropdown -->
    <div class="cart-dropdown" id="cart-dropdown">
        <div class="cart-header">
            <h3>Shopping Cart</h3>
            <button class="close-cart" onclick="toggleCart()">&times;</button>
        </div>
        <div class="cart-items" id="cart-items">
            <p class="empty-cart">Your cart is empty</p>
        </div>
        <div class="cart-footer">
            <div class="cart-total">
                <strong>Total: <span id="cart-total">Rp 0</span></strong>
            </div>
            <button class="btn-checkout" onclick="checkout()">
                <i class="fab fa-whatsapp"></i> Checkout via WhatsApp
            </button>
        </div>
    </div>
