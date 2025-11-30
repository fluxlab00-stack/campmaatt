<?php
/**
 * Forgot Password Page
 */

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';

$pageTitle = "Forgot Password - CampMart";

include __DIR__ . '/../includes/header.php';
?>

<!-- Hero Section -->
<section class="hero-gradient text-white py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h1 class="text-4xl md:text-5xl font-bold mb-4 drop-shadow-lg">
                <i class="fas fa-key"></i> Forgot Password
            </h1>
            <p class="text-xl text-gray-100">
                Reset your password securely
            </p>
        </div>
    </div>
</section>

<!-- Forgot Password Form -->
<section class="py-16 bg-gray-50">
    <div class="max-w-md mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-lg shadow-md p-8">
            <div class="text-center mb-6">
                <div class="w-16 h-16 bg-primary bg-opacity-10 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-lock text-3xl text-primary"></i>
                </div>
                <h2 class="text-2xl font-bold text-gray-900 mb-2">Reset Your Password</h2>
                <p class="text-gray-600">
                    Enter your email address and we'll send you a link to reset your password
                </p>
            </div>
            
            <form method="POST" action="<?php echo baseUrl('includes/auth/forgot-password-process.php'); ?>">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                
                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-semibold mb-2">Email Address</label>
                    <input type="email" name="email" placeholder="your.email@example.edu.ng" required 
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-primary transition">
                </div>
                
                <button type="submit" class="w-full bg-primary text-white py-3 rounded-lg hover:bg-pink-700 transition font-semibold mb-4">
                    <i class="fas fa-paper-plane mr-2"></i> Send Reset Link
                </button>
                
                <div class="text-center">
                    <a href="<?php echo baseUrl('index.php'); ?>" class="text-primary hover:underline text-sm">
                        <i class="fas fa-arrow-left mr-1"></i> Back to Login
                    </a>
                </div>
            </form>
            
            <div class="mt-8 pt-6 border-t">
                <h3 class="font-semibold text-gray-900 mb-3">Having Trouble?</h3>
                <ul class="space-y-2 text-sm text-gray-600">
                    <li class="flex items-start">
                        <i class="fas fa-info-circle text-primary mr-2 mt-1"></i>
                        <span>Make sure to use the email address you registered with</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-info-circle text-primary mr-2 mt-1"></i>
                        <span>Check your spam folder if you don't receive the email within 5 minutes</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-info-circle text-primary mr-2 mt-1"></i>
                        <span>Contact support if you still can't access your account</span>
                    </li>
                </ul>
                
                <div class="mt-4">
                    <a href="contact.php" class="text-primary hover:underline text-sm">
                        <i class="fas fa-envelope mr-1"></i> Contact Support
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Security Notice -->
        <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4 text-center">
            <i class="fas fa-shield-alt text-blue-500 text-2xl mb-2"></i>
            <p class="text-sm text-blue-800">
                <strong>Security Notice:</strong> Password reset links expire after 1 hour for your security. 
                Never share your reset link with anyone.
            </p>
        </div>
    </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
<?php include __DIR__ . '/../includes/modals.php'; ?>
