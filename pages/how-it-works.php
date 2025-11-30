<?php
/**
 * How It Works Page
 * Guide to using CampMart
 */

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';

$pageTitle = "How It Works - CampMart";

include __DIR__ . '/../includes/header.php';
?>

<!-- Page Header -->
<section class="bg-primary text-white py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h1 class="text-5xl font-bold mb-4">How CampMart Works</h1>
        <p class="text-xl text-gray-100">Your Guide to Campus Commerce Made Easy</p>
    </div>
</section>

<!-- 3-Step Process -->
<section class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-3xl font-bold text-gray-900 mb-12 text-center">Getting Started is Simple</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-12">
            <!-- Step 1 -->
            <div class="text-center">
                <div class="bg-primary text-white rounded-full w-24 h-24 flex items-center justify-center mx-auto mb-6 text-4xl font-bold">
                    1
                </div>
                <div class="bg-gray-100 rounded-full w-20 h-20 flex items-center justify-center mx-auto mb-6">
                    <i class="fas fa-user-plus text-4xl text-primary"></i>
                </div>
                <h3 class="text-2xl font-bold text-gray-900 mb-4">Create an Account</h3>
                <p class="text-gray-700">
                    Sign up in minutes with your campus email. Verify your student status and create your profile. It's free and only takes a few moments.
                </p>
            </div>
            
            <!-- Step 2 -->
            <div class="text-center">
                <div class="bg-primary text-white rounded-full w-24 h-24 flex items-center justify-center mx-auto mb-6 text-4xl font-bold">
                    2
                </div>
                <div class="bg-gray-100 rounded-full w-20 h-20 flex items-center justify-center mx-auto mb-6">
                    <i class="fas fa-search text-4xl text-primary"></i>
                </div>
                <h3 class="text-2xl font-bold text-gray-900 mb-4">Upload or Browse Items</h3>
                <p class="text-gray-700">
                    Post your items with photos and descriptions, or search thousands of listings by category, price, and campus location.
                </p>
            </div>
            
            <!-- Step 3 -->
            <div class="text-center">
                <div class="bg-primary text-white rounded-full w-24 h-24 flex items-center justify-center mx-auto mb-6 text-4xl font-bold">
                    3
                </div>
                <div class="bg-gray-100 rounded-full w-20 h-20 flex items-center justify-center mx-auto mb-6">
                    <i class="fas fa-handshake text-4xl text-primary"></i>
                </div>
                <h3 class="text-2xl font-bold text-gray-900 mb-4">Chat, Meet, Exchange</h3>
                <p class="text-gray-700">
                    Connect securely via in-app chat, agree on a safe campus meet point, and complete your transaction. Simple and secure!
                </p>
            </div>
        </div>
    </div>
</section>

<!-- For Sellers -->
<section class="py-16 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-3xl font-bold text-gray-900 mb-12 text-center">For Sellers</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div class="bg-white rounded-lg shadow-lg p-8">
                <div class="flex items-center mb-4">
                    <div class="bg-primary text-white rounded-full w-12 h-12 flex items-center justify-center mr-4">
                        <i class="fas fa-camera text-xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900">1. Take Good Photos</h3>
                </div>
                <p class="text-gray-700">
                    Clear, well-lit photos from multiple angles help buyers understand exactly what they're getting. Show any defects honestly.
                </p>
            </div>
            
            <div class="bg-white rounded-lg shadow-lg p-8">
                <div class="flex items-center mb-4">
                    <div class="bg-primary text-white rounded-full w-12 h-12 flex items-center justify-center mr-4">
                        <i class="fas fa-file-alt text-xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900">2. Write Detailed Descriptions</h3>
                </div>
                <p class="text-gray-700">
                    Include brand, model, condition, and any relevant details. Be honest about flaws. The more information, the faster you'll sell.
                </p>
            </div>
            
            <div class="bg-white rounded-lg shadow-lg p-8">
                <div class="flex items-center mb-4">
                    <div class="bg-primary text-white rounded-full w-12 h-12 flex items-center justify-center mr-4">
                        <i class="fas fa-tag text-xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900">3. Price Competitively</h3>
                </div>
                <p class="text-gray-700">
                    Research similar items on CampMart to set a fair price. Consider the item's condition and market demand.
                </p>
            </div>
            
            <div class="bg-white rounded-lg shadow-lg p-8">
                <div class="flex items-center mb-4">
                    <div class="bg-primary text-white rounded-full w-12 h-12 flex items-center justify-center mr-4">
                        <i class="fas fa-comments text-xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900">4. Respond Quickly</h3>
                </div>
                <p class="text-gray-700">
                    Fast responses to inquiries lead to faster sales. Be professional, friendly, and available to answer questions.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- For Buyers -->
<section class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-3xl font-bold text-gray-900 mb-12 text-center">For Buyers</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div class="bg-gray-50 rounded-lg p-8 border-2 border-primary">
                <div class="flex items-center mb-4">
                    <div class="bg-primary text-white rounded-full w-12 h-12 flex items-center justify-center mr-4">
                        <i class="fas fa-search text-xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900">1. Search & Filter</h3>
                </div>
                <p class="text-gray-700">
                    Use our advanced search and filtering options to find exactly what you need. Filter by category, price range, condition, and more.
                </p>
            </div>
            
            <div class="bg-gray-50 rounded-lg p-8 border-2 border-primary">
                <div class="flex items-center mb-4">
                    <div class="bg-primary text-white rounded-full w-12 h-12 flex items-center justify-center mr-4">
                        <i class="fas fa-user-check text-xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900">2. Check Seller Profile</h3>
                </div>
                <p class="text-gray-700">
                    Review the seller's profile, ratings, and previous listings. Verified campus users give you added peace of mind.
                </p>
            </div>
            
            <div class="bg-gray-50 rounded-lg p-8 border-2 border-primary">
                <div class="flex items-center mb-4">
                    <div class="bg-primary text-white rounded-full w-12 h-12 flex items-center justify-center mr-4">
                        <i class="fas fa-comment-dots text-xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900">3. Ask Questions</h3>
                </div>
                <p class="text-gray-700">
                    Use in-app chat to ask about condition, negotiate price, or request additional photos. Communication is key!
                </p>
            </div>
            
            <div class="bg-gray-50 rounded-lg p-8 border-2 border-primary">
                <div class="flex items-center mb-4">
                    <div class="bg-primary text-white rounded-full w-12 h-12 flex items-center justify-center mr-4">
                        <i class="fas fa-map-marker-alt text-xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900">4. Meet Safely</h3>
                </div>
                <p class="text-gray-700">
                    Choose a public, well-lit campus location. Inspect the item thoroughly before payment. Trust your instincts.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Safety Tips -->
<section class="py-16 bg-red-50">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <div class="bg-red-500 text-white rounded-full w-20 h-20 flex items-center justify-center mx-auto mb-6">
                <i class="fas fa-shield-alt text-3xl"></i>
            </div>
            <h2 class="text-3xl font-bold text-gray-900 mb-4">Safety Tips for Buyers & Sellers</h2>
            <p class="text-gray-700">Your safety is our top priority. Follow these guidelines for secure transactions.</p>
        </div>
        
        <div class="space-y-4">
            <div class="bg-white rounded-lg p-6 shadow-md">
                <div class="flex items-start">
                    <i class="fas fa-check-circle text-green-500 text-2xl mr-4 mt-1"></i>
                    <div>
                        <h4 class="font-semibold text-gray-900 mb-2">Meet in Public Places</h4>
                        <p class="text-gray-700">Always meet in well-lit, public campus locations like the library entrance, cafeteria, or department offices.</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg p-6 shadow-md">
                <div class="flex items-start">
                    <i class="fas fa-check-circle text-green-500 text-2xl mr-4 mt-1"></i>
                    <div>
                        <h4 class="font-semibold text-gray-900 mb-2">Inspect Before Paying</h4>
                        <p class="text-gray-700">Always thoroughly inspect items before exchanging money. Test electronics, check for defects, and verify condition.</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg p-6 shadow-md">
                <div class="flex items-start">
                    <i class="fas fa-times-circle text-red-500 text-2xl mr-4 mt-1"></i>
                    <div>
                        <h4 class="font-semibold text-gray-900 mb-2">Never Share Bank Details</h4>
                        <p class="text-gray-700">Don't share your bank account information, PIN, or passwords. CampMart never asks for these details.</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg p-6 shadow-md">
                <div class="flex items-start">
                    <i class="fas fa-times-circle text-red-500 text-2xl mr-4 mt-1"></i>
                    <div>
                        <h4 class="font-semibold text-gray-900 mb-2">Don't Meet at Private Residences</h4>
                        <p class="text-gray-700">For your safety, avoid meeting at private rooms or residences, especially for first-time transactions.</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg p-6 shadow-md">
                <div class="flex items-start">
                    <i class="fas fa-check-circle text-green-500 text-2xl mr-4 mt-1"></i>
                    <div>
                        <h4 class="font-semibold text-gray-900 mb-2">Report Suspicious Activity</h4>
                        <p class="text-gray-700">If something seems off, report it immediately using our report feature. We take all reports seriously.</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg p-6 shadow-md">
                <div class="flex items-start">
                    <i class="fas fa-check-circle text-green-500 text-2xl mr-4 mt-1"></i>
                    <div>
                        <h4 class="font-semibold text-gray-900 mb-2">Trust Your Instincts</h4>
                        <p class="text-gray-700">If a deal seems too good to be true or something feels wrong, walk away. Your safety comes first.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<?php if (!isLoggedIn()): ?>
<section class="py-16 bg-primary text-white">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-3xl font-bold mb-6">Ready to Get Started?</h2>
        <p class="text-xl text-gray-100 mb-8">
            Join thousands of students already using CampMart for safe, convenient campus commerce.
        </p>
        <div class="flex justify-center gap-4">
            <button onclick="openRegisterModal()" class="px-8 py-3 bg-white text-primary rounded-lg hover:bg-gray-100 transition font-semibold text-lg">
                Create Account
            </button>
            <button onclick="openLoginModal()" class="px-8 py-3 border-2 border-white text-white rounded-lg hover:bg-white hover:text-primary transition font-semibold text-lg">
                Login
            </button>
        </div>
    </div>
</section>
<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>
