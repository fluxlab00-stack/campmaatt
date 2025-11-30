        </main>
    </div>

    <script>
        // Mobile menu toggle
        const sidebar = document.getElementById('sidebar');
        const mobileMenuToggle = document.getElementById('mobile-menu-toggle');
        
        mobileMenuToggle?.addEventListener('click', () => {
            sidebar.classList.toggle('-translate-x-full');
        });

        // Close sidebar on mobile when clicking outside
        document.addEventListener('click', (e) => {
            if (window.innerWidth < 1024) {
                if (!sidebar.contains(e.target) && !mobileMenuToggle.contains(e.target)) {
                    sidebar.classList.add('-translate-x-full');
                }
            }
        });

        // Ensure sidebar is visible on desktop
        window.addEventListener('resize', () => {
            if (window.innerWidth >= 1024) {
                sidebar.classList.remove('-translate-x-full');
            } else {
                sidebar.classList.add('-translate-x-full');
            }
        });

        // Initialize sidebar state
        if (window.innerWidth < 1024) {
            sidebar.classList.add('-translate-x-full');
        }
    </script>
</body>
</html>
