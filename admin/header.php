<?php
require_once 'auth.php';
requireAdmin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>CampMart Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#0b9729ff',
                        accent: '#FFA500'
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50">
    <!-- Sidebar -->
    <aside id="sidebar" class="fixed top-0 left-0 w-64 h-full bg-white shadow-lg z-40 transform transition-transform duration-300">
        <div class="h-full flex flex-col">
            <!-- Logo -->
            <div class="p-6 border-b">
                <h1 class="text-2xl font-bold text-primary">CampMart Admin</h1>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 overflow-y-auto py-4">
                <ul class="space-y-1 px-3">
                    <li>
                        <a href="index.php" class="flex items-center gap-3 px-4 py-3 text-gray-700 rounded-lg hover:bg-gray-100 <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'bg-primary text-white hover:bg-primary' : ''; ?>">
                            <i class="fas fa-dashboard w-5"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="users.php" class="flex items-center gap-3 px-4 py-3 text-gray-700 rounded-lg hover:bg-gray-100 <?php echo basename($_SERVER['PHP_SELF']) == 'users.php' ? 'bg-primary text-white hover:bg-primary' : ''; ?>">
                            <i class="fas fa-users w-5"></i>
                            <span>Users</span>
                        </a>
                    </li>
                    <li>
                        <a href="listings.php" class="flex items-center gap-3 px-4 py-3 text-gray-700 rounded-lg hover:bg-gray-100 <?php echo basename($_SERVER['PHP_SELF']) == 'listings.php' ? 'bg-primary text-white hover:bg-primary' : ''; ?>">
                            <i class="fas fa-box w-5"></i>
                            <span>Listings</span>
                        </a>
                    </li>
                    <li>
                        <a href="lost-found.php" class="flex items-center gap-3 px-4 py-3 text-gray-700 rounded-lg hover:bg-gray-100 <?php echo basename($_SERVER['PHP_SELF']) == 'lost-found.php' ? 'bg-primary text-white hover:bg-primary' : ''; ?>">
                            <i class="fas fa-search w-5"></i>
                            <span>Found It</span>
                        </a>
                    </li>
                    
                    <!-- System Management -->
                    <li class="pt-4 pb-2 px-4">
                        <span class="text-xs font-semibold text-gray-400 uppercase">System</span>
                    </li>
                    <li>
                        <a href="campuses.php" class="flex items-center gap-3 px-4 py-3 text-gray-700 rounded-lg hover:bg-gray-100 <?php echo basename($_SERVER['PHP_SELF']) == 'campuses.php' ? 'bg-primary text-white hover:bg-primary' : ''; ?>">
                            <i class="fas fa-university w-5"></i>
                            <span>Campuses</span>
                        </a>
                    </li>
                    <li>
                        <a href="categories.php" class="flex items-center gap-3 px-4 py-3 text-gray-700 rounded-lg hover:bg-gray-100 <?php echo basename($_SERVER['PHP_SELF']) == 'categories.php' ? 'bg-primary text-white hover:bg-primary' : ''; ?>">
                            <i class="fas fa-tags w-5"></i>
                            <span>Categories</span>
                        </a>
                    </li>
                    <li>
                        <a href="departments.php" class="flex items-center gap-3 px-4 py-3 text-gray-700 rounded-lg hover:bg-gray-100 <?php echo basename($_SERVER['PHP_SELF']) == 'departments.php' ? 'bg-primary text-white hover:bg-primary' : ''; ?>">
                            <i class="fas fa-building w-5"></i>
                            <span>Departments</span>
                        </a>
                    </li>
                    <li>
                        <a href="levels.php" class="flex items-center gap-3 px-4 py-3 text-gray-700 rounded-lg hover:bg-gray-100 <?php echo basename($_SERVER['PHP_SELF']) == 'levels.php' ? 'bg-primary text-white hover:bg-primary' : ''; ?>">
                            <i class="fas fa-layer-group w-5"></i>
                            <span>Levels</span>
                        </a>
                    </li>
                    
                    <!-- Reports & Analytics -->
                    <li class="pt-4 pb-2 px-4">
                        <span class="text-xs font-semibold text-gray-400 uppercase">Reports</span>
                    </li>
                    <li>
                        <a href="reports.php" class="flex items-center gap-3 px-4 py-3 text-gray-700 rounded-lg hover:bg-gray-100 <?php echo basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'bg-primary text-white hover:bg-primary' : ''; ?>">
                            <i class="fas fa-flag w-5"></i>
                            <span>Flagged Content</span>
                        </a>
                    </li>
                    <li>
                        <a href="analytics.php" class="flex items-center gap-3 px-4 py-3 text-gray-700 rounded-lg hover:bg-gray-100 <?php echo basename($_SERVER['PHP_SELF']) == 'analytics.php' ? 'bg-primary text-white hover:bg-primary' : ''; ?>">
                            <i class="fas fa-chart-line w-5"></i>
                            <span>Analytics</span>
                        </a>
                    </li>
                </ul>
            </nav>

            <!-- Admin Profile -->
            <div class="p-4 border-t">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-primary text-white flex items-center justify-center">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate"><?php echo htmlspecialchars(getAdminName()); ?></p>
                        <p class="text-xs text-gray-500 truncate"><?php echo htmlspecialchars(getAdminEmail()); ?></p>
                    </div>
                </div>
                <a href="logout.php" class="mt-3 w-full flex items-center justify-center gap-2 px-4 py-2 text-sm text-red-600 hover:bg-red-50 rounded-lg transition">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </div>
        </div>
    </aside>

    <!-- Mobile Menu Toggle -->
    <button id="mobile-menu-toggle" class="fixed top-4 left-4 z-50 lg:hidden bg-primary text-white p-3 rounded-lg shadow-lg">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Main Content Area -->
    <div class="lg:ml-64">
        <!-- Top Bar -->
        <header class="bg-white shadow-sm sticky top-0 z-30">
            <div class="flex items-center justify-between px-6 py-4">
                <h2 class="text-2xl font-semibold text-gray-800"><?php echo $page_title ?? 'Dashboard'; ?></h2>
                
                <div class="flex items-center gap-4">
                    <!-- Notifications -->
                    <button class="relative p-2 text-gray-600 hover:text-primary">
                        <i class="fas fa-bell text-xl"></i>
                        <span class="absolute top-0 right-0 w-2 h-2 bg-red-500 rounded-full"></span>
                    </button>
                    
                    <!-- View Site -->
                    <a href="../index.php" target="_blank" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:text-primary">
                        <i class="fas fa-external-link-alt"></i>
                        <span class="hidden sm:inline">View Site</span>
                    </a>
                </div>
            </div>
        </header>

        <!-- Page Content -->
        <main class="p-6">
            <!-- Flash Messages -->
            <?php if (isset($_SESSION['success'])): ?>
                <div class="mb-6 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg flex items-center justify-between">
                    <span><?php echo htmlspecialchars($_SESSION['success']); ?></span>
                    <button onclick="this.parentElement.remove()" class="text-green-800 hover:text-green-900">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="mb-6 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg flex items-center justify-between">
                    <span><?php echo htmlspecialchars($_SESSION['error']); ?></span>
                    <button onclick="this.parentElement.remove()" class="text-red-800 hover:text-red-900">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <!-- Page content goes here -->
