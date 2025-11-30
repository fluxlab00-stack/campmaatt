<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="CampMart - Your trusted campus marketplace for buying, selling, and connecting.">
    <title><?php echo $pageTitle ?? 'CampMart - Campus Marketplace'; ?></title>
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom Tailwind Config -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#198754', /* green */
                        secondary: '#FF7A00', /* orange */
                        accent: '#FF7A00',
                    }
                }
            }
        }
    </script>
    
    <!-- Custom Styles -->
    <style>
        .hero-gradient {
            background: linear-gradient(135deg, #198754 0%, #15683f 100%);
        }
        
        .glass-effect {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
        }
        
        .hover-scale {
            transition: transform 0.3s ease;
        }
        
        .hover-scale:hover {
            transform: scale(1.05);
        }
        
        .shadow-custom {
            box-shadow: 0 4px 6px -1px rgba(25, 135, 84, 0.08), 0 2px 4px -1px rgba(25, 135, 84, 0.06);
        }
        
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 10px;
        }
        
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        
        ::-webkit-scrollbar-thumb {
            background: #198754;
            border-radius: 5px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #15683f;
        }

        /* Animations for card feature modal */
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slideUp {
            from { 
                opacity: 0;
                transform: translateY(20px);
            }
            to { 
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fadeIn {
            animation: fadeIn 0.3s ease-in-out;
        }

        .animate-slideUp {
            animation: slideUp 0.3s ease-out;
        }
    </style>
</head>
<body class="bg-gray-50">
    <script>
        window.__currentUserId = <?php echo isLoggedIn() ? (int)getCurrentUserId() : 0; ?>;
    </script>
    
    <!-- Navigation Bar -->
    <nav class="bg-white shadow-lg sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                
                <!-- Logo -->
                <div class="flex-shrink-0">
                    <a href="<?php echo baseUrl('index.php'); ?>" class="flex items-center space-x-2">
                        <span class="text-2xl font-bold"><span class="text-primary">Camp</span><span class="text-secondary">Mart</span></span>
                    </a>
                </div>
                
                <!-- Desktop Navigation -->
                <div class="hidden md:flex items-center space-x-6">
                    <a href="<?php echo baseUrl('index.php'); ?>" class="text-gray-700 hover:text-primary transition">Home</a>
                    
                    <!-- Categories Dropdown -->
                    <div class="relative group">
                        <button class="text-gray-700 hover:text-primary transition flex items-center">
                            Categories <i class="fas fa-chevron-down ml-1 text-xs"></i>
                        </button>
                        <div class="absolute left-0 mt-2 w-56 bg-white rounded-lg shadow-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200">
                            <div class="py-2">
                                <a href="<?php echo baseUrl('pages/marketplace.php?category=Gadgets'); ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-primary hover:text-white transition"><i class="fas fa-mobile-alt mr-2"></i> Gadgets</a>
                                <a href="<?php echo baseUrl('pages/marketplace.php?category=Electronics'); ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-primary hover:text-white transition"><i class="fas fa-tv mr-2"></i> Electronics</a>
                                <a href="<?php echo baseUrl('pages/marketplace.php?category=Apartments'); ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-primary hover:text-white transition"><i class="fas fa-home mr-2"></i> Apartments</a>
                                <a href="<?php echo baseUrl('pages/marketplace.php?category=Food'); ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-primary hover:text-white transition"><i class="fas fa-utensils mr-2"></i> Food</a>
                                <a href="<?php echo baseUrl('pages/marketplace.php?category=Beauty & Cosmetics'); ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-primary hover:text-white transition"><i class="fas fa-spray-can mr-2"></i> Beauty & Cosmetics</a>
                                <a href="<?php echo baseUrl('pages/marketplace.php?category=Vehicles'); ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-primary hover:text-white transition"><i class="fas fa-car mr-2"></i> Vehicles</a>
                                <a href="<?php echo baseUrl('pages/free-corner.php'); ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-primary hover:text-white transition border-t"><i class="fas fa-gift mr-2"></i> Free Corner</a>
                                <!-- Hot Deals removed -->
                                <a href="<?php echo baseUrl('marketplace.php?available_today=1'); ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-primary hover:text-white transition"><i class="fas fa-clock mr-2"></i> Available Today</a>
                            </div>
                        </div>
                    </div>
                    
                          <a href="<?php echo baseUrl('pages/trending.php'); ?>" class="text-gray-700 hover:text-primary transition">Trending</a>
                          <a href="<?php echo baseUrl('pages/free-corner.php'); ?>" class="text-gray-700 hover:text-primary transition"><i class="fas fa-gift mr-1"></i>Free Corner</a>
                              <a href="<?php echo baseUrl('pages/lost-found.php'); ?>" class="text-gray-700 hover:text-primary transition">Found It</a>
                    
                    <!-- Help Center Dropdown -->
                    <div class="relative group">
                        <button class="text-gray-700 hover:text-primary transition flex items-center">
                            Help <i class="fas fa-chevron-down ml-1 text-xs"></i>
                        </button>
                        <div class="absolute left-0 mt-2 w-48 bg-white rounded-lg shadow-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200">
                            <div class="py-2">
                                <a href="<?php echo baseUrl('pages/how-it-works.php'); ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-primary hover:text-white transition">How It Works</a>
                                <a href="<?php echo baseUrl('pages/faq.php'); ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-primary hover:text-white transition">FAQ</a>
                                <a href="<?php echo baseUrl('pages/about.php'); ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-primary hover:text-white transition">About Us</a>
                                <a href="<?php echo baseUrl('pages/contact.php'); ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-primary hover:text-white transition">Contact Us</a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Right Side Actions -->
                <div class="flex items-center space-x-4">
                    <?php if (isLoggedIn()): 
                        $user = getCurrentUser();
                    ?>
                        <!-- Post Item Button -->
                        <button onclick="openPostModal()" class="hidden md:flex items-center px-4 py-2 bg-accent text-white rounded-lg hover:bg-orange-600 transition">
                            <i class="fas fa-plus mr-2"></i> Post Item
                        </button>
                        
                        <!-- User Profile Dropdown -->
                        <div class="relative group">
                            <button class="flex items-center space-x-2 focus:outline-none">
                                <img src="<?php echo baseUrl($user['profile_picture_url'] ?? 'assets/images/default-avatar.png'); ?>" alt="Profile" class="w-10 h-10 rounded-full object-cover border-2 border-primary">
                            </button>
                            <div class="absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200">
                                <div class="py-2">
                                    <div class="px-4 py-2 border-b">
                                        <p class="text-sm font-semibold text-gray-900"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></p>
                                        <p class="text-xs text-gray-500"><?php echo htmlspecialchars($user['email']); ?></p>
                                    </div>
                                    <a href="<?php echo baseUrl('pages/profile.php'); ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-primary hover:text-white transition"><i class="fas fa-user mr-2"></i> My Profile</a>
                                    <a href="<?php echo baseUrl('pages/my-listings.php'); ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-primary hover:text-white transition"><i class="fas fa-list mr-2"></i> My Listings</a>
                                    <a href="<?php echo baseUrl('pages/saved-items.php'); ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-primary hover:text-white transition"><i class="fas fa-bookmark mr-2"></i> Saved Items</a>
                                    <a href="<?php echo baseUrl('pages/messages.php'); ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-primary hover:text-white transition"><i class="fas fa-envelope mr-2"></i> Messages</a>
                                    <?php if (isAdmin()): ?>
                                        <a href="<?php echo baseUrl('admin/index.php'); ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-primary hover:text-white transition border-t"><i class="fas fa-cog mr-2"></i> Admin Panel</a>
                                    <?php endif; ?>
                                    <a href="<?php echo baseUrl('includes/logout.php'); ?>" class="block px-4 py-2 text-sm text-red-600 hover:bg-red-600 hover:text-white transition border-t"><i class="fas fa-sign-out-alt mr-2"></i> Logout</a>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- Login/Register Buttons -->
                        <button onclick="openLoginModal()" class="text-gray-700 hover:text-primary transition font-semibold">Login</button>
                        <button onclick="openRegisterModal()" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-green-600 transition">Sign Up</button>
                    <?php endif; ?>
                    
                    <!-- Mobile Menu Toggle -->
                    <button onclick="toggleMobileMenu()" class="md:hidden text-gray-700 hover:text-primary">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Mobile Menu -->
        <div id="mobileMenu" class="hidden md:hidden bg-white border-t">
            <div class="px-4 py-2 space-y-2">
                <a href="<?php echo baseUrl('index.php'); ?>" class="block py-2 text-gray-700 hover:text-primary transition">Home</a>
                <a href="<?php echo baseUrl('pages/marketplace.php'); ?>" class="block py-2 text-gray-700 hover:text-primary transition">Marketplace</a>
                <a href="<?php echo baseUrl('pages/trending.php'); ?>" class="block py-2 text-gray-700 hover:text-primary transition">Trending</a>
                <a href="<?php echo baseUrl('pages/free-corner.php'); ?>" class="block py-2 text-gray-700 hover:text-primary transition">Free Corner</a>
                    <a href="<?php echo baseUrl('pages/lost-found.php'); ?>" class="block py-2 text-gray-700 hover:text-primary transition">Found It</a>
                <a href="<?php echo baseUrl('pages/how-it-works.php'); ?>" class="block py-2 text-gray-700 hover:text-primary transition">How It Works</a>
                <a href="<?php echo baseUrl('pages/about.php'); ?>" class="block py-2 text-gray-700 hover:text-primary transition">About Us</a>
                <a href="<?php echo baseUrl('pages/contact.php'); ?>" class="block py-2 text-gray-700 hover:text-primary transition">Contact Us</a>
                <?php if (isLoggedIn()): ?>
                    <button onclick="openPostModal()" class="w-full py-2 bg-accent text-white rounded-lg hover:bg-orange-600 transition">
                        <i class="fas fa-plus mr-2"></i> Post Item
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </nav>
    
    <!-- Flash Messages -->
    <?php 
    $flash = getFlashMessage();
    if ($flash): 
        $bgColor = $flash['type'] === 'success' ? 'bg-green-500' : 
                   ($flash['type'] === 'error' ? 'bg-red-500' : 
                   ($flash['type'] === 'warning' ? 'bg-yellow-500' : 'bg-blue-500'));
    ?>
        <div id="flashMessage" class="<?php echo $bgColor; ?> text-white px-6 py-4 relative">
            <div class="max-w-7xl mx-auto flex justify-between items-center">
                <span><?php echo htmlspecialchars($flash['message']); ?></span>
                <button onclick="document.getElementById('flashMessage').remove()" class="text-white hover:text-gray-200">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    <?php endif; ?>

<?php if (isLoggedIn()): ?>
<script>
    // Attempt to obtain user geolocation on page load and every 30 minutes
    (function() {
        const LOCATION_ENDPOINT = '<?php echo baseUrl("includes/location/update-location.php"); ?>';

        function sendLocation(lat, lon) {
            fetch(LOCATION_ENDPOINT, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ lat: lat, lon: lon })
            }).then(r => r.json()).then(data => {
                if (data && data.success) {
                    console.log('Location updated', data);
                } else {
                    console.log('Location update failed', data);
                }
            }).catch(err => console.warn('Location send error', err));
        }

        function tryGeolocation() {
            if (!navigator.geolocation) return;

            navigator.geolocation.getCurrentPosition(function(pos) {
                sendLocation(pos.coords.latitude, pos.coords.longitude);
            }, function(err) {
                console.warn('Geolocation error', err);
            }, { enableHighAccuracy: false, maximumAge: 5 * 60 * 1000, timeout: 10000 });
        }

        // On load
        tryGeolocation();

        // Every 30 minutes
        setInterval(tryGeolocation, 30 * 60 * 1000);
    })();
</script>
<?php endif; ?>
