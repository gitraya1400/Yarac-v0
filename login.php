<?php
$page_title = "Sign In - Yarac Fashion Store";
$additional_css = ['auth.css'];

require_once 'config/database.php';
require_once 'classes/User.php';

$error_message = '';
$success_message = '';

// Handle Google OAuth callback
if (isset($_GET['code'])) {
    require_once 'includes/google-oauth.php';
    
    try {
        $google_client = getGoogleClient();
        $token = $google_client->fetchAccessTokenWithAuthCode($_GET['code']);
        
        if (!isset($token['error'])) {
            $google_client->setAccessToken($token['access_token']);
            $google_service = new Google_Service_Oauth2($google_client);
            $user_info = $google_service->userinfo->get();
            
            // Check if user exists or create new user
            $database = new Database();
            $db = $database->getConnection();
            $user = new User($db);
            
            $user->email = $user_info->email;
            
            if (!$user->emailExists()) {
                // Create new user from Google data
                $user->first_name = $user_info->givenName;
                $user->last_name = $user_info->familyName;
                $user->password = password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT);
                $user->role = 'user';
                
                if ($user->register()) {
                    $success_message = "Account created successfully with Google!";
                } else {
                    $error_message = "Failed to create account";
                }
            }
            
            // Login user
            if ($user->getByEmail($user_info->email)) {
                session_start();
                $_SESSION['user_id'] = $user->id;
                $_SESSION['user_name'] = $user->first_name . ' ' . $user->last_name;
                $_SESSION['user_email'] = $user->email;
                $_SESSION['user_role'] = $user->role;
                
                header("Location: index.php");
                exit();
            }
        } else {
            $error_message = "Google authentication failed";
        }
    } catch (Exception $e) {
        $error_message = "Authentication error occurred";
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $database = new Database();
    $db = $database->getConnection();
    $user = new User($db);
    
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $remember = isset($_POST['remember']);
    
    if (empty($email) || empty($password)) {
        $error_message = "Please fill in all fields";
    } else {
        if ($user->login($email, $password)) {
            session_start();
            $_SESSION['user_id'] = $user->id;
            $_SESSION['user_name'] = $user->first_name . ' ' . $user->last_name;
            $_SESSION['user_email'] = $user->email;
            $_SESSION['user_role'] = $user->role;
            
            if ($remember) {
                setcookie('remember_user', $user->id, time() + (86400 * 30), "/");
            }
            
            // Redirect based on role
            if ($user->role === 'admin') {
                header("Location: admin/dashboard.php");
            } else {
                header("Location: index.php");
            }
            exit();
        } else {
            $error_message = "Invalid email or password";
        }
    }
}

include 'includes/header.php';
?>

<section class="auth-section">
    <div class="container">
        <div class="auth-container">
            <div class="auth-card">
                <h2>Sign In</h2>
                <p>Welcome back to Yarac Fashion Store</p>
                
                <?php if (!empty($error_message)): ?>
                    <div class="alert alert-error">
                        <?php echo htmlspecialchars($error_message); ?>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($success_message)): ?>
                    <div class="alert alert-success">
                        <?php echo htmlspecialchars($success_message); ?>
                    </div>
                <?php endif; ?>
                
                <!-- Google Sign In Button -->
                <div class="google-signin">
                    <a href="<?php echo isset($_GET['google']) ? '#' : 'login.php?google=1'; ?>" class="btn-google-signin">
                        <svg width="20" height="20" viewBox="0 0 24 24">
                            <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                            <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                            <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                            <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                        </svg>
                        Continue with Google
                    </a>
                </div>
                
                <div class="divider">
                    <span>or</span>
                </div>
                
                <form class="auth-form" method="POST" action="">
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" placeholder="Enter your email" required 
                               value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" placeholder="Enter your password" required>
                    </div>
                    
                    <div class="form-options">
                        <label class="checkbox-label">
                            <input type="checkbox" name="remember">
                            <span class="checkmark"></span>
                            Remember me
                        </label>
                        <a href="#" class="forgot-password">Forgot Password?</a>
                    </div>
                    
                    <button type="submit" class="btn-auth">
                        <i class="fas fa-sign-in-alt"></i> Sign In
                    </button>
                </form>
                
                <div class="auth-switch">
                    Don't have an account? <a href="register.php">Sign up here</a>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
// Enhanced form validation and UX
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('.auth-form');
    const submitBtn = document.querySelector('.btn-auth');
    const inputs = document.querySelectorAll('input[required]');
    
    // Real-time validation
    inputs.forEach(input => {
        input.addEventListener('blur', function() {
            validateField(this);
        });
        
        input.addEventListener('input', function() {
            if (this.classList.contains('error')) {
                validateField(this);
            }
        });
    });
    
    // Form submission
    form.addEventListener('submit', function(e) {
        let isValid = true;
        
        inputs.forEach(input => {
            if (!validateField(input)) {
                isValid = false;
            }
        });
        
        if (!isValid) {
            e.preventDefault();
            return;
        }
        
        // Show loading state
        submitBtn.classList.add('loading');
        submitBtn.disabled = true;
    });
    
    function validateField(field) {
        const value = field.value.trim();
        let isValid = true;
        
        // Remove existing error styling
        field.classList.remove('error');
        
        // Basic validation
        if (field.hasAttribute('required') && !value) {
            isValid = false;
        }
        
        // Email validation
        if (field.type === 'email' && value) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(value)) {
                isValid = false;
            }
        }
        
        // Password validation
        if (field.type === 'password' && value && value.length < 6) {
            isValid = false;
        }
        
        if (!isValid) {
            field.classList.add('error');
        }
        
        return isValid;
    }
});
</script>

<?php include 'includes/footer.php'; ?>
