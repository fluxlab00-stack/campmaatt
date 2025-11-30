<?php
/**
 * Terms & Policies Page
 */

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';

$pageTitle = "Terms & Policies - CampMart";

include __DIR__ . '/../includes/header.php';
?>

<!-- Hero Section -->
<section class="hero-gradient text-white py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h1 class="text-4xl md:text-5xl font-bold mb-4 drop-shadow-lg">
                Terms & Policies
            </h1>
            <p class="text-xl text-gray-100">
                Please read these terms carefully before using CampMart
            </p>
        </div>
    </div>
</section>

<!-- Terms Content -->
<section class="py-16 bg-gray-50">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-lg shadow-md p-8">
            
            <div class="mb-8">
                <p class="text-gray-600 mb-4">
                    <strong>Last Updated:</strong> November 15, 2025
                </p>
                <p class="text-gray-600">
                    Welcome to CampMart! By accessing or using our platform, you agree to be bound by these Terms and Policies. Please read them carefully.
                </p>
            </div>
            
            <!-- Section 1 -->
            <div class="mb-8">
                <h2 class="text-2xl font-bold text-primary mb-4">1. Acceptance of Terms</h2>
                <p class="text-gray-600 mb-3">
                    By creating an account and using CampMart, you accept and agree to be bound by these terms and conditions. If you do not agree to these terms, please do not use our platform.
                </p>
                <p class="text-gray-600">
                    We reserve the right to update these terms at any time. Continued use of CampMart after changes constitutes acceptance of the new terms.
                </p>
            </div>
            
            <!-- Section 2 -->
            <div class="mb-8">
                <h2 class="text-2xl font-bold text-primary mb-4">2. User Eligibility</h2>
                <ul class="list-disc pl-6 text-gray-600 space-y-2">
                    <li>You must be a registered student at a participating campus</li>
                    <li>You must be at least 18 years old or have parental consent</li>
                    <li>You must provide accurate and truthful information during registration</li>
                    <li>You are responsible for maintaining the confidentiality of your account</li>
                </ul>
            </div>
            
            <!-- Section 3 -->
            <div class="mb-8">
                <h2 class="text-2xl font-bold text-primary mb-4">3. Prohibited Activities</h2>
                <p class="text-gray-600 mb-3">Users are strictly prohibited from:</p>
                <ul class="list-disc pl-6 text-gray-600 space-y-2">
                    <li>Posting illegal, stolen, or counterfeit items</li>
                    <li>Listing weapons, drugs, or other prohibited items</li>
                    <li>Engaging in fraudulent activities or scams</li>
                    <li>Posting misleading or false information</li>
                    <li>Harassing, threatening, or bullying other users</li>
                    <li>Using the platform for commercial purposes without authorization</li>
                    <li>Creating multiple accounts to manipulate the platform</li>
                    <li>Attempting to breach security or access unauthorized areas</li>
                </ul>
            </div>
            
            <!-- Section 4 -->
            <div class="mb-8">
                <h2 class="text-2xl font-bold text-primary mb-4">4. Listing Guidelines</h2>
                <p class="text-gray-600 mb-3">When posting items for sale:</p>
                <ul class="list-disc pl-6 text-gray-600 space-y-2">
                    <li>Provide accurate descriptions and clear, honest photos</li>
                    <li>Set fair and reasonable prices</li>
                    <li>Only post items you legally own and have the right to sell</li>
                    <li>Update or remove listings promptly when items are sold</li>
                    <li>Respond to inquiries in a timely and professional manner</li>
                    <li>Honor commitments made to buyers</li>
                </ul>
            </div>
            
            <!-- Section 5 -->
            <div class="mb-8">
                <h2 class="text-2xl font-bold text-primary mb-4">5. Transactions and Payments</h2>
                <p class="text-gray-600 mb-3">
                    CampMart is a platform connecting buyers and sellers. We do not:
                </p>
                <ul class="list-disc pl-6 text-gray-600 space-y-2">
                    <li>Process payments or hold funds</li>
                    <li>Verify the authenticity of items</li>
                    <li>Guarantee the quality or condition of items</li>
                    <li>Mediate disputes between buyers and sellers</li>
                </ul>
                <p class="text-gray-600 mt-3">
                    All transactions are conducted directly between users at their own risk. We strongly recommend meeting in safe, public locations on campus and inspecting items before payment.
                </p>
            </div>
            
            <!-- Section 6 -->
            <div class="mb-8">
                <h2 class="text-2xl font-bold text-primary mb-4">6. User Conduct and Safety</h2>
                <p class="text-gray-600 mb-3">For your safety:</p>
                <ul class="list-disc pl-6 text-gray-600 space-y-2">
                    <li>Always meet in well-lit, public areas on campus</li>
                    <li>Bring a friend to transactions when possible</li>
                    <li>Verify the identity of the person you're meeting</li>
                    <li>Inspect items thoroughly before completing payment</li>
                    <li>Trust your instincts - if something feels wrong, walk away</li>
                    <li>Report suspicious activity immediately</li>
                </ul>
            </div>
            
            <!-- Section 7 -->
            <div class="mb-8">
                <h2 class="text-2xl font-bold text-primary mb-4">7. Intellectual Property</h2>
                <p class="text-gray-600 mb-3">
                    All content on CampMart, including the logo, design, and code, is owned by CampMart and protected by copyright laws. By posting content, you grant CampMart a non-exclusive license to display it on our platform.
                </p>
            </div>
            
            <!-- Section 8 -->
            <div class="mb-8">
                <h2 class="text-2xl font-bold text-primary mb-4">8. Privacy and Data</h2>
                <p class="text-gray-600 mb-3">
                    We collect and process personal information as described in our <a href="privacy.php" class="text-primary hover:underline">Privacy Policy</a>. By using CampMart, you consent to this collection and use.
                </p>
            </div>
            
            <!-- Section 9 -->
            <div class="mb-8">
                <h2 class="text-2xl font-bold text-primary mb-4">9. Account Suspension and Termination</h2>
                <p class="text-gray-600 mb-3">
                    We reserve the right to suspend or terminate accounts that violate these terms or engage in prohibited activities. This includes:
                </p>
                <ul class="list-disc pl-6 text-gray-600 space-y-2">
                    <li>Immediate suspension for serious violations (fraud, illegal items)</li>
                    <li>Warning and temporary suspension for minor violations</li>
                    <li>Permanent ban for repeated violations</li>
                </ul>
            </div>
            
            <!-- Section 10 -->
            <div class="mb-8">
                <h2 class="text-2xl font-bold text-primary mb-4">10. Limitation of Liability</h2>
                <p class="text-gray-600 mb-3">
                    CampMart is provided "as is" without warranties. We are not liable for:
                </p>
                <ul class="list-disc pl-6 text-gray-600 space-y-2">
                    <li>Losses arising from transactions between users</li>
                    <li>Fraudulent activities or scams</li>
                    <li>Damage, injury, or theft during meet-ups</li>
                    <li>Technical issues or platform downtime</li>
                    <li>User-generated content or listings</li>
                </ul>
                <p class="text-gray-600 mt-3">
                    Users engage in transactions at their own risk and responsibility.
                </p>
            </div>
            
            <!-- Section 11 -->
            <div class="mb-8">
                <h2 class="text-2xl font-bold text-primary mb-4">11. Dispute Resolution</h2>
                <p class="text-gray-600">
                    While we provide a platform for campus trading, disputes between users must be resolved directly. We encourage professional, respectful communication. If a resolution cannot be reached, users may seek mediation through campus authorities or legal channels.
                </p>
            </div>
            
            <!-- Section 12 -->
            <div class="mb-8">
                <h2 class="text-2xl font-bold text-primary mb-4">12. Contact Information</h2>
                <p class="text-gray-600">
                    If you have questions about these terms, please contact us at:
                </p>
                <p class="text-gray-600 mt-3">
                    Email: <a href="mailto:support@campmart.ng" class="text-primary hover:underline">support@campmart.ng</a><br>
                    Or visit our <a href="contact.php" class="text-primary hover:underline">Contact Page</a>
                </p>
            </div>
            
            <div class="border-t pt-6">
                <p class="text-gray-600 text-sm">
                    By using CampMart, you acknowledge that you have read, understood, and agree to be bound by these Terms and Policies.
                </p>
            </div>
            
        </div>
    </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
<?php include __DIR__ . '/../includes/modals.php'; ?>
