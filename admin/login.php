<?php
/**
 * Admin Login Page
 */

session_start();

// If already logged in as admin, redirect to dashboard
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: index.php");
    exit;
}

$error = $_SESSION['error'] ?? '';
$success = $_SESSION['success'] ?? '';
unset($_SESSION['error'], $_SESSION['success']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - CampMart</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .primary-color { color: #0a9253ff; }
        .bg-primary { background-color: #198754 }
        .border-primary { border-color: #11a35fff; }
        
        body {
            background: linear-gradient(135deg, #198754 0%, #15683f 100%);
            background-image: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="%23ffffff" fill-opacity="0.1" d="M0,96L48,112C96,128,192,160,288,160C384,160,480,128,576,122.7C672,117,768,139,864,144C960,149,1056,139,1152,133.3C1248,128,1344,128,1392,128L1440,128L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path></svg>');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
        }
    </style>
</head>
<body>
    <div class="min-h-screen flex items-center justify-center px-4">
        <div class="max-w-md w-full">
            <!-- Logo -->
            <div class="text-center mb-8">
                <h1 class="text-4xl font-bold text-white mb-2">CampMart</h1>
                <p class="text-gray-100">Admin Control Panel</p>
            </div>
            
            <!-- Login Card with Full Opacity -->
            <div class="bg-white rounded-lg shadow-2xl p-8 backdrop-filter backdrop-blur-md">
                <h2 class="text-2xl font-bold text-gray-900 mb-6 text-center">Admin Login</h2>
                
                <?php if ($error): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                        <?php echo htmlspecialchars($success); ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="login-process.php">
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-semibold mb-2">Email</label>
                        <input type="email" name="email" required 
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-primary">
                    </div>
                    
                    <div class="mb-6">
                        <label class="block text-gray-700 text-sm font-semibold mb-2">Password</label>
                        <input type="password" name="password" required 
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-primary">
                    </div>
                    
                    <button type="submit" class="w-full bg-primary text-white py-3 rounded-lg hover:opacity-90 transition font-semibold">
                        <i class="fas fa-sign-in-alt mr-2"></i> Login
                    </button>
                </form>
                
                <div class="mt-6 text-center">
                    <a href="../index.php" class="text-gray-600 hover:text-primary text-sm">
                        <i class="fas fa-arrow-left mr-1"></i> Back to Website
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
