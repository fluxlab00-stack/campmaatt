    <!-- Footer -->
    <footer class="bg-gray-900 text-white mt-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                
                <!-- Column 1: About -->
                <div>
                    <h3 class="text-2xl font-bold mb-4"><span class="text-primary">Camp</span><span class="text-secondary">Mart</span></h3>
                    <p class="text-gray-400 text-sm mb-4">Your trusted campus marketplace for buying, selling, and connecting.</p>
                    <div class="flex space-x-4">
                        <a href="#" onclick="openDeepLink('fb','https://facebook.com/campmart_ng'); return false;" class="text-gray-400 hover:text-primary transition" aria-label="Facebook"><i class="fab fa-facebook text-xl"></i></a>
                        <a href="#" onclick="openDeepLink('instagram','https://instagram.com/campmart_ng'); return false;" class="text-gray-400 hover:text-primary transition" aria-label="Instagram"><i class="fab fa-instagram text-xl"></i></a>
                        <a href="#" onclick="openDeepLink('twitter','https://twitter.com/campmart_ng'); return false;" class="text-gray-400 hover:text-primary transition" aria-label="Twitter"><i class="fab fa-twitter text-xl"></i></a>
                        <a href="#" onclick="openDeepLink('linkedin','https://www.linkedin.com/company/campmart_ng'); return false;" class="text-gray-400 hover:text-primary transition" aria-label="LinkedIn"><i class="fab fa-linkedin text-xl"></i></a>
                    </div>
                </div>
                
                <!-- Column 2: Quick Links -->
                <div>
                    <h4 class="font-semibold text-lg mb-4">Quick Links</h4>
                    <ul class="space-y-2">
                        <li><a href="<?php echo baseUrl('index.php'); ?>" class="text-gray-400 hover:text-primary transition text-sm">Home</a></li>
                        <li><a href="<?php echo baseUrl('pages/marketplace.php'); ?>" class="text-gray-400 hover:text-primary transition text-sm">Marketplace</a></li>
                        <li><a href="<?php echo baseUrl('pages/trending.php'); ?>" class="text-gray-400 hover:text-primary transition text-sm">Trending</a></li>
                        <li><a href="<?php echo baseUrl('pages/free-corner.php'); ?>" class="text-gray-400 hover:text-primary transition text-sm">Free Corner</a></li>
                        <li><a href="<?php echo baseUrl('pages/lost-found.php'); ?>" class="text-gray-400 hover:text-primary transition text-sm">Found It</a></li>
                    </ul>
                </div>
                
                <!-- Column 3: About CampMart -->
                <div>
                    <h4 class="font-semibold text-lg mb-4">About CampMart</h4>
                    <ul class="space-y-2">
                        <li><a href="<?php echo baseUrl('pages/about.php'); ?>" class="text-gray-400 hover:text-primary transition text-sm">About Us</a></li>
                        <li><a href="<?php echo baseUrl('pages/how-it-works.php'); ?>" class="text-gray-400 hover:text-primary transition text-sm">How It Works</a></li>
                        <li><a href="<?php echo baseUrl('pages/faq.php'); ?>" class="text-gray-400 hover:text-primary transition text-sm">FAQ</a></li>
                        <li><a href="<?php echo baseUrl('pages/contact.php'); ?>" class="text-gray-400 hover:text-primary transition text-sm">Contact Us</a></li>
                    </ul>
                </div>
                
                <!-- Column 4: Newsletter -->
                <div>
                    <h4 class="font-semibold text-lg mb-4">Stay Connected</h4>
                    <p class="text-gray-400 text-sm mb-4">Get campus deals directly to your inbox!</p>
                    <form class="flex">
                        <input type="email" placeholder="Your email" class="flex-1 px-4 py-2 rounded-l-lg text-gray-900 focus:outline-none" required>
                        <button type="submit" class="bg-primary hover:bg-green-600 px-4 py-2 rounded-r-lg transition">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- Bottom Bar -->
            <div class="border-t border-gray-800 mt-8 pt-8 flex flex-col md:flex-row justify-between items-center">
                <div class="text-gray-400 text-sm mb-4 md:mb-0">
                    <a href="<?php echo baseUrl('pages/privacy.php'); ?>" class="hover:text-primary transition">Privacy Policy</a>
                    <span class="mx-2">|</span>
                    <a href="<?php echo baseUrl('pages/terms.php'); ?>" class="hover:text-primary transition">Terms of Service</a>
                </div>
                <div class="text-gray-400 text-sm">
                    © 2024 CampMart. All Rights Reserved. Powered by CampMart
                </div>
            </div>
        </div>
    </footer>
    
    <!-- Floating Action Button (Mobile) - only on homepage and marketplace -->
    <?php if (isLoggedIn()):
        $pageName = basename($_SERVER['PHP_SELF']);
        if (in_array($pageName, ['index.php', 'marketplace.php'])): ?>
            <button onclick="openListingForm()" class="md:hidden fixed bottom-6 right-6 w-14 h-14 bg-primary rounded-full shadow-lg flex items-center justify-center text-white hover:bg-green-600 transition z-40">
                <i class="fas fa-plus text-xl"></i>
            </button>
    <?php endif; endif; ?>
    
    <!-- Modals will be included here -->
    <?php include __DIR__ . '/modals.php'; ?>
    
    <!-- Scripts -->
    <script src="assets/js/main.js"></script>
    <script src="assets/js/campmart-security.js"></script>
    
    <script>
        // Toggle mobile menu
        function toggleMobileMenu() {
            const menu = document.getElementById('mobileMenu');
            menu.classList.toggle('hidden');
        }
        
        // Auto-hide flash messages after 5 seconds
        setTimeout(() => {
            const flashMessage = document.getElementById('flashMessage');
            if (flashMessage) {
                flashMessage.style.transition = 'opacity 0.5s';
                flashMessage.style.opacity = '0';
                setTimeout(() => flashMessage.remove(), 500);
            }
        }, 5000);
    </script>
    
    <script>
        // Deep-link helper: try to open native app on mobile, fallback to web URL.
        // Platforms supported: instagram, twitter, facebook, linkedin
        function isMobileDevice() {
            return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent || navigator.vendor || window.opera);
        }

        function openDeepLink(platform, webUrl) {
            // App URL schemes (best-effort). Replace username/page id where applicable.
            const username = 'campmart_ng';
            let appUrl = '';

            switch (platform) {
                case 'instagram':
                    // instagram app scheme — opens profile
                    appUrl = 'instagram://user?username=' + username;
                    break;
                case 'twitter':
                    appUrl = 'twitter://user?screen_name=' + username;
                    break;
                case 'facebook':
                    // use facewebmodal to attempt to open the page via the native Facebook app
                    appUrl = 'fb://facewebmodal/f?href=' + encodeURIComponent(webUrl);
                    break;
                case 'linkedin':
                    // LinkedIn app deep links are limited; attempt company page using the web URL fallback encoded
                    appUrl = 'linkedin://company/' + username;
                    break;
                default:
                    appUrl = webUrl;
            }

            // Desktop: always open web URL in a new tab
            if (!isMobileDevice()) {
                window.open(webUrl, '_blank', 'noopener');
                return;
            }

            // Mobile: try opening the app scheme then fallback to the web URL after short delay
            const now = Date.now();

            // Try to open via location change first (works in many browsers)
            try {
                window.location = appUrl;
            } catch (e) {
                // ignore
            }

            // As a secondary attempt (some browsers block direct navigation), use an iframe then remove it
            const iframe = document.createElement('iframe');
            iframe.style.display = 'none';
            iframe.src = appUrl;
            document.body.appendChild(iframe);
            setTimeout(function() {
                try { document.body.removeChild(iframe); } catch(e) {}
            }, 1000);

            // Fallback: if the app didn't open within 800ms, open the web URL in the same tab
            setTimeout(function() {
                // On some platforms we can't detect reliably; use the time heuristic
                if (Date.now() - now < 2000) {
                    // Open web URL in a new tab on mobile fallback
                    try { window.open(webUrl, '_blank', 'noopener'); } catch(e) { window.location = webUrl; }
                }
            }, 800);
        }
    </script>
    <script>
        // Ensure the floating + opens the listing form: prefer modal if available, otherwise redirect
        function openListingForm(){
            try{
                if (typeof openPostModal === 'function'){
                    openPostModal();
                    return;
                }
            }catch(e){/*ignore*/}
            // fallback: redirect to full listing form page
            window.location.href = '<?php echo baseUrl('pages/post-item.php'); ?>';
        }
    </script>
</body>
</html>
