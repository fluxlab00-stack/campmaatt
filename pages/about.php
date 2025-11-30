<?php
/**
 * About Us Page
 * Information about CampMart
 */

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';

$pageTitle = "About Us - CampMart";

include __DIR__ . '/../includes/header.php';
?>

<!-- Page Header -->
<section class="bg-primary text-white py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h1 class="text-5xl font-bold mb-4">About CampMart</h1>
        <p class="text-xl text-gray-100">Building a Campus Community Through Commerce</p>
    </div>
</section>

<!-- Our Story -->
<section class="py-16 bg-white">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-3xl font-bold text-gray-900 mb-6">Our Story</h2>
        <div class="prose prose-lg">
            <p class="text-gray-700 mb-4">
                CampMart was born from a simple observation: students across Nigerian campuses needed a better way to buy, sell, and exchange goods and services within their community. Traditional marketplaces were either too distant, too expensive, or simply didn't understand the unique needs of campus life.
            </p>
            <p class="text-gray-700 mb-4">
                We created CampMart to be more than just a marketplace—it's a platform that brings students together, fosters trust, and makes transactions simple, secure, and convenient. Whether you're looking for textbooks, electronics, accommodation, or any other campus necessity, CampMart connects you with verified members of your campus community.
            </p>
        </div>
    </div>
</section>

<!-- Vision & Mission -->
<section class="py-16 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
            <div class="bg-white rounded-lg shadow-lg p-8">
                <div class="bg-primary text-white rounded-full w-16 h-16 flex items-center justify-center mb-6">
                    <i class="fas fa-eye text-2xl"></i>
                </div>
                <h3 class="text-2xl font-bold text-gray-900 mb-4">Our Vision</h3>
                <p class="text-gray-700">
                    To become the leading campus marketplace across Nigeria and Africa, empowering students to trade safely, efficiently, and affordably within their communities.
                </p>
            </div>
            
            <div class="bg-white rounded-lg shadow-lg p-8">
                <div class="bg-primary text-white rounded-full w-16 h-16 flex items-center justify-center mb-6">
                    <i class="fas fa-bullseye text-2xl"></i>
                </div>
                <h3 class="text-2xl font-bold text-gray-900 mb-4">Our Mission</h3>
                <p class="text-gray-700">
                    To provide a secure, user-friendly platform that facilitates seamless transactions between students, reduces the cost of campus living, and builds a stronger sense of community.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Why CampMart Exists -->
<section class="py-16 bg-white">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-3xl font-bold text-gray-900 mb-6">Why CampMart Exists</h2>
        <div class="space-y-6">
            <div class="flex items-start">
                <div class="bg-primary text-white rounded-full w-12 h-12 flex items-center justify-center flex-shrink-0 mr-4">
                    <i class="fas fa-hand-holding-usd"></i>
                </div>
                <div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Affordable Campus Living</h3>
                    <p class="text-gray-700">We understand the financial challenges students face. CampMart helps you save money by finding affordable items within your campus community.</p>
                </div>
            </div>
            
            <div class="flex items-start">
                <div class="bg-primary text-white rounded-full w-12 h-12 flex items-center justify-center flex-shrink-0 mr-4">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Safe Transactions</h3>
                    <p class="text-gray-700">With verified campus users and designated safe meet points, we prioritize your safety in every transaction.</p>
                </div>
            </div>
            
            <div class="flex items-start">
                <div class="bg-primary text-white rounded-full w-12 h-12 flex items-center justify-center flex-shrink-0 mr-4">
                    <i class="fas fa-clock"></i>
                </div>
                <div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Convenience</h3>
                    <p class="text-gray-700">No need to travel far or deal with complex logistics. Find what you need right on your campus, available when you need it.</p>
                </div>
            </div>
            
            <div class="flex items-start">
                <div class="bg-primary text-white rounded-full w-12 h-12 flex items-center justify-center flex-shrink-0 mr-4">
                    <i class="fas fa-users"></i>
                </div>
                <div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Community Building</h3>
                    <p class="text-gray-700">Connect with fellow students, build trust, and create lasting relationships through shared experiences.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Campus-First Philosophy -->
<section class="py-16 bg-primary text-white">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-3xl font-bold mb-6">Campus-First Philosophy</h2>
        <p class="text-xl text-gray-100 mb-8">
            CampMart is designed specifically for the unique environment and challenges of Nigerian university life. We understand late-night study sessions, tight budgets, limited transportation, and the need for quick, reliable exchanges.
        </p>
        <p class="text-lg text-gray-100">
            Every feature we build is crafted with students in mind—from our Free Corner for sharing resources, to our Lost & Found system for reuniting items with their owners, to our secure meet point system for safe exchanges.
        </p>
    </div>
</section>

<!-- Our Values -->
<section class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-3xl font-bold text-gray-900 mb-12 text-center">Our Core Values</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
            <div class="text-center">
                <div class="bg-primary text-white rounded-full w-20 h-20 flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-handshake text-3xl"></i>
                </div>
                <h3 class="font-semibold text-xl mb-2">Community</h3>
                <p class="text-gray-600">Building connections and fostering relationships within campus communities</p>
            </div>
            
            <div class="text-center">
                <div class="bg-primary text-white rounded-full w-20 h-20 flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-heart text-3xl"></i>
                </div>
                <h3 class="font-semibold text-xl mb-2">Trust</h3>
                <p class="text-gray-600">Creating a safe, reliable platform where students can transact with confidence</p>
            </div>
            
            <div class="text-center">
                <div class="bg-primary text-white rounded-full w-20 h-20 flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-rocket text-3xl"></i>
                </div>
                <h3 class="font-semibold text-xl mb-2">Empowerment</h3>
                <p class="text-gray-600">Giving students the tools to improve their campus experience</p>
            </div>
            
            <div class="text-center">
                <div class="bg-primary text-white rounded-full w-20 h-20 flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-bolt text-3xl"></i>
                </div>
                <h3 class="font-semibold text-xl mb-2">Convenience</h3>
                <p class="text-gray-600">Making campus commerce simple, fast, and hassle-free</p>
            </div>
        </div>
    </div>
</section>

<!-- Join Us -->
<section class="py-16 bg-gray-50">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-3xl font-bold text-gray-900 mb-6">Join the CampMart Community</h2>
        <p class="text-xl text-gray-700 mb-8">
            Thousands of students are already using CampMart to buy, sell, and connect. Be part of the movement to make campus life easier and more affordable.
        </p>
        <?php if (!isLoggedIn()): ?>
            <div class="flex justify-center gap-4">
                <button onclick="openLoginModal()" class="px-8 py-3 bg-primary text-white rounded-lg hover:bg-pink-700 transition font-semibold text-lg">
                    Get Started
                </button>
                <a href="contact.php" class="px-8 py-3 border-2 border-primary text-primary rounded-lg hover:bg-primary hover:text-white transition font-semibold text-lg">
                    Contact Us
                </a>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
