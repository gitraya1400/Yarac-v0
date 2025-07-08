-- Updated database schema with enhanced features
DROP DATABASE IF EXISTS yarac_store_final_updated;
CREATE DATABASE yarac_store_final_updated;
USE yarac_store_final_updated;

-- Users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Products table with rating and enhanced features
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    category ENUM('shirts', 'casual', 'formal') NOT NULL,
    gender ENUM('men', 'women', 'unisex') NOT NULL,
    image VARCHAR(255) NOT NULL DEFAULT 'placeholder.jpg',
    stock INT NOT NULL DEFAULT 0,
    rating DECIMAL(2,1) DEFAULT 0.0,
    total_reviews INT DEFAULT 0,
    sizes JSON DEFAULT ('["S", "M", "L", "XL"]'),
    featured BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Product reviews table
CREATE TABLE product_reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    user_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    review TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Orders table
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    total_amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    shipping_address TEXT NOT NULL,
    phone VARCHAR(20) NOT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Order items table
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    price DECIMAL(10,2) NOT NULL,
    size VARCHAR(10) DEFAULT 'M',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Newsletter subscribers table
CREATE TABLE newsletter_subscribers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) UNIQUE NOT NULL,
    subscribed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Advertisements table
CREATE TABLE advertisements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    image VARCHAR(255) NOT NULL,
    link VARCHAR(255),
    active BOOLEAN DEFAULT TRUE,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert sample admin user (password: admin123)
INSERT INTO users (first_name, last_name, email, password, role, phone, address) VALUES 
('Admin', 'Yarac', 'admin@yarac.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', '+6281234567890', 'Jakarta, Indonesia');

-- Insert sample users (password: user123)
INSERT INTO users (first_name, last_name, email, password, role, phone, address) VALUES 
('John', 'Doe', 'john@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', '+6281234567891', 'Bandung, Indonesia'),
('Jane', 'Smith', 'jane@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', '+6281234567892', 'Surabaya, Indonesia'),
('Mike', 'Johnson', 'mike@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', '+6281234567893', 'Medan, Indonesia');

-- Insert sample products with enhanced data
INSERT INTO products (name, description, price, category, gender, image, stock, rating, total_reviews, featured) VALUES 
('Classic White Shirt', 'Kemeja putih klasik yang cocok untuk berbagai acara formal maupun kasual. Dibuat dari bahan katun premium yang nyaman dan breathable.', 299000, 'shirts', 'men', 'classic-white-shirt.jpg', 50, 4.5, 23, TRUE),
('Casual Denim Jacket', 'Jaket denim kasual yang trendy dan nyaman untuk gaya sehari-hari. Perfect untuk layering dan memberikan kesan stylish.', 459000, 'casual', 'women', 'casual-denim-jacket.jpg', 30, 4.2, 18, TRUE),
('Formal Black Blazer', 'Blazer hitam formal untuk penampilan profesional yang elegan. Cut yang sempurna dengan detail finishing berkualitas tinggi.', 699000, 'formal', 'men', 'formal-black-blazer.jpg', 25, 4.7, 31, TRUE),
('Trendy Crop Top', 'Crop top trendy untuk gaya kasual yang stylish dan modern. Cocok untuk berbagai aktivitas santai dan hangout.', 199000, 'casual', 'women', 'trendy-crop-top.jpg', 40, 4.1, 15, TRUE),
('Business Shirt', 'Kemeja bisnis berkualitas tinggi dengan bahan premium. Ideal untuk meeting dan acara formal lainnya.', 349000, 'shirts', 'men', 'business-shirt.jpg', 35, 4.4, 27, FALSE),
('Elegant Blouse', 'Blouse elegan untuk wanita dengan desain yang sophisticated. Perfect untuk office look yang chic.', 279000, 'formal', 'women', 'elegant-blouse.jpg', 28, 4.3, 22, FALSE),
('Casual T-Shirt', 'Kaos kasual yang nyaman untuk aktivitas sehari-hari. Bahan cotton combed yang lembut dan tahan lama.', 149000, 'casual', 'unisex', 'casual-tshirt.jpg', 60, 4.0, 45, FALSE),
('Formal Pants', 'Celana formal dengan potongan yang sempurna. Bahan berkualitas tinggi dengan fit yang comfortable.', 399000, 'formal', 'men', 'formal-pants.jpg', 20, 4.6, 19, FALSE),
('Summer Dress', 'Dress musim panas yang ringan dan airy. Perfect untuk cuaca tropis dengan style yang feminine.', 329000, 'casual', 'women', 'summer-dress.jpg', 32, 4.2, 28, FALSE),
('Polo Shirt', 'Polo shirt klasik yang versatile untuk berbagai occasion. Bahan pique cotton yang breathable.', 229000, 'shirts', 'unisex', 'polo-shirt.jpg', 45, 4.1, 33, FALSE),
('Cardigan Sweater', 'Cardigan sweater yang cozy untuk cuaca dingin. Layer piece yang stylish dan functional.', 389000, 'casual', 'women', 'cardigan-sweater.jpg', 22, 4.4, 16, FALSE),
('Chino Pants', 'Celana chino yang comfortable untuk daily wear. Style yang timeless dengan berbagai pilihan warna.', 319000, 'casual', 'men', 'chino-pants.jpg', 38, 4.3, 24, FALSE);

-- Insert sample advertisements
INSERT INTO advertisements (title, image, link, active, sort_order) VALUES 
('Summer Collection 2024', 'adv1.jpg', 'products.php?category=casual', TRUE, 1),
('Formal Wear Sale', 'adv2.jpg', 'products.php?category=formal', TRUE, 2),
('New Arrivals', 'adv3.jpg', 'products.php', TRUE, 3);

-- Insert sample reviews
INSERT INTO product_reviews (product_id, user_id, rating, review) VALUES 
(1, 2, 5, 'Kemeja yang sangat berkualitas, bahan nyaman dan cutting sempurna!'),
(1, 3, 4, 'Good quality shirt, recommended untuk formal events.'),
(2, 2, 4, 'Jaket denim yang stylish, cocok untuk gaya kasual sehari-hari.'),
(3, 3, 5, 'Blazer yang sangat elegan, perfect untuk meeting penting.'),
(4, 2, 4, 'Crop top yang trendy, bahan nyaman dan design menarik.');
