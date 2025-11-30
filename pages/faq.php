<?php
/**
 * FAQ Page
 * Frequently Asked Questions
 */

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';

$pageTitle = "FAQ - CampMart";

include __DIR__ . '/../includes/header.php';
?>

<!-- Hero Section -->
<section class="hero-gradient text-white py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h1 class="text-4xl md:text-5xl font-bold mb-4 drop-shadow-lg">
                <i class="fas fa-question-circle"></i> Frequently Asked Questions
            </h1>
            <p class="text-xl text-gray-100">
                Find answers to common questions about CampMart
            </p>
        </div>
    </div>
</section>

<!-- FAQ Content -->
<section class="py-16 bg-gray-50">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Getting Started -->
        <div class="mb-12">
            <h2 class="text-3xl font-bold text-primary mb-6">Getting Started</h2>
            
            <div class="space-y-4">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <button onclick="toggleFAQ(this)" class="w-full text-left flex justify-between items-center">
                        <h3 class="text-lg font-semibold text-gray-900">What is CampMart?</h3>
                        <i class="fas fa-chevron-down text-primary transition-transform"></i>
                    </button>
                    <div class="faq-content hidden mt-4 text-gray-600">
                        <p>CampMart is a trusted campus marketplace designed specifically for students. It allows you to buy, sell, and trade items within your campus community safely and conveniently.</p>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow-md p-6">
                    <button onclick="toggleFAQ(this)" class="w-full text-left flex justify-between items-center">
                        <h3 class="text-lg font-semibold text-gray-900">How do I create an account?</h3>
                        <i class="fas fa-chevron-down text-primary transition-transform"></i>
                    </button>
                    <div class="faq-content hidden mt-4 text-gray-600">
                        <p>Click on the "Sign Up" button in the navigation bar, fill in your details including your campus email, department, and level. Once registered, you can start buying and selling immediately!</p>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow-md p-6">
                    <button onclick="toggleFAQ(this)" class="w-full text-left flex justify-between items-center">
                        <h3 class="text-lg font-semibold text-gray-900">Is CampMart free to use?</h3>
                        <i class="fas fa-chevron-down text-primary transition-transform"></i>
                    </button>
                    <div class="faq-content hidden mt-4 text-gray-600">
                        <p>Yes! CampMart is completely free. There are no listing fees, no transaction fees, and no hidden charges. We believe in making campus trading accessible to everyone.</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Buying & Selling -->
        <div class="mb-12">
            <h2 class="text-3xl font-bold text-primary mb-6">Buying & Selling</h2>
            
            <div class="space-y-4">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <button onclick="toggleFAQ(this)" class="w-full text-left flex justify-between items-center">
                        <h3 class="text-lg font-semibold text-gray-900">How do I post an item for sale?</h3>
                        <i class="fas fa-chevron-down text-primary transition-transform"></i>
                    </button>
                    <div class="faq-content hidden mt-4 text-gray-600">
                        <p>After logging in, click the "Post Item" button, upload clear photos of your item, fill in the details (title, description, price, condition), and submit. Your listing will be live immediately!</p>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow-md p-6">
                    <button onclick="toggleFAQ(this)" class="w-full text-left flex justify-between items-center">
                        <h3 class="text-lg font-semibold text-gray-900">How do I contact a seller?</h3>
                        <i class="fas fa-chevron-down text-primary transition-transform"></i>
                    </button>
                    <div class="faq-content hidden mt-4 text-gray-600">
                        <p>On any listing page, you'll find a "Contact Seller" button that opens WhatsApp with the seller's number. You can then arrange payment and pickup directly with them.</p>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow-md p-6">
                    <button onclick="toggleFAQ(this)" class="w-full text-left flex justify-between items-center">
                        <h3 class="text-lg font-semibold text-gray-900">What payment methods are accepted?</h3>
                        <i class="fas fa-chevron-down text-primary transition-transform"></i>
                    </button>
                    <div class="faq-content hidden mt-4 text-gray-600">
                        <p>Payment is arranged directly between buyers and sellers. We recommend cash on delivery, bank transfers, or mobile money. Always meet in safe, public locations on campus.</p>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow-md p-6">
                    <button onclick="toggleFAQ(this)" class="w-full text-left flex justify-between items-center">
                        <h3 class="text-lg font-semibold text-gray-900">Can I edit or delete my listing?</h3>
                        <i class="fas fa-chevron-down text-primary transition-transform"></i>
                    </button>
                    <div class="faq-content hidden mt-4 text-gray-600">
                        <p>Yes! Go to "My Listings" from your profile menu. You can edit details, mark items as sold, or delete listings at any time.</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Safety & Trust -->
        <div class="mb-12">
            <h2 class="text-3xl font-bold text-primary mb-6">Safety & Trust</h2>
            
            <div class="space-y-4">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <button onclick="toggleFAQ(this)" class="w-full text-left flex justify-between items-center">
                        <h3 class="text-lg font-semibold text-gray-900">Is CampMart safe?</h3>
                        <i class="fas fa-chevron-down text-primary transition-transform"></i>
                    </button>
                    <div class="faq-content hidden mt-4 text-gray-600">
                        <p>CampMart is designed for campus communities, so all users are fellow students. We recommend meeting in public places on campus, inspecting items before paying, and trusting your instincts. Report any suspicious activity to our team.</p>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow-md p-6">
                    <button onclick="toggleFAQ(this)" class="w-full text-left flex justify-between items-center">
                        <h3 class="text-lg font-semibold text-gray-900">What if I receive a fake or damaged item?</h3>
                        <i class="fas fa-chevron-down text-primary transition-transform"></i>
                    </button>
                    <div class="faq-content hidden mt-4 text-gray-600">
                        <p>Always inspect items thoroughly before completing payment. If you encounter fraud, report the user immediately through the "Report" button on their listing. We take fraud seriously and will investigate all reports.</p>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow-md p-6">
                    <button onclick="toggleFAQ(this)" class="w-full text-left flex justify-between items-center">
                        <h3 class="text-lg font-semibold text-gray-900">How do I report a suspicious listing?</h3>
                        <i class="fas fa-chevron-down text-primary transition-transform"></i>
                    </button>
                    <div class="faq-content hidden mt-4 text-gray-600">
                        <p>On any listing page, click the "Report" button and select the reason for reporting. Our team will review the listing within 24 hours and take appropriate action.</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Features -->
        <div class="mb-12">
            <h2 class="text-3xl font-bold text-primary mb-6">Features</h2>
            
            <div class="space-y-4">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <button onclick="toggleFAQ(this)" class="w-full text-left flex justify-between items-center">
                        <h3 class="text-lg font-semibold text-gray-900">What is the Free Corner?</h3>
                        <i class="fas fa-chevron-down text-primary transition-transform"></i>
                    </button>
                    <div class="faq-content hidden mt-4 text-gray-600">
                        <p>The Free Corner is a special section where students can give away items they no longer need for free. It's perfect for textbooks, dorm items, or anything you want to share with the campus community.</p>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow-md p-6">
                    <button onclick="toggleFAQ(this)" class="w-full text-left flex justify-between items-center">
                        <h3 class="text-lg font-semibold text-gray-900">What is Lost & Found?</h3>
                        <i class="fas fa-chevron-down text-primary transition-transform"></i>
                    </button>
                    <div class="faq-content hidden mt-4 text-gray-600">
                        <p>Lost & Found helps students report and find lost items on campus. If you've lost something or found an item, post it in this section to help reunite items with their owners.</p>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow-md p-6">
                    <button onclick="toggleFAQ(this)" class="w-full text-left flex justify-between items-center">
                        <h3 class="text-lg font-semibold text-gray-900">How do Saved Items work?</h3>
                        <i class="fas fa-chevron-down text-primary transition-transform"></i>
                    </button>
                    <div class="faq-content hidden mt-4 text-gray-600">
                        <p>Click the bookmark icon on any listing to save it to your Saved Items. Access your saved items from your profile menu to keep track of items you're interested in.</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Still Have Questions? -->
        <div class="bg-primary text-white rounded-lg p-8 text-center">
            <h2 class="text-2xl font-bold mb-4">Still Have Questions?</h2>
            <p class="mb-6">Can't find the answer you're looking for? We're here to help!</p>
            <a href="contact.php" class="inline-block px-8 py-3 bg-white text-primary rounded-lg hover:bg-gray-100 transition font-semibold">
                Contact Support
            </a>
        </div>
        
    </div>
</section>

<script>
    function toggleFAQ(button) {
        const content = button.parentElement.querySelector('.faq-content');
        const icon = button.querySelector('i');
        
        content.classList.toggle('hidden');
        icon.classList.toggle('rotate-180');
    }
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
<?php include __DIR__ . '/../includes/modals.php'; ?>
