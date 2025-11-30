<?php
/**
 * Privacy Policy Page
 */

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';

$pageTitle = "Privacy Policy - CampMart";

include __DIR__ . '/../includes/header.php';
?>

<!-- Hero Section -->
<section class="hero-gradient text-white py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h1 class="text-4xl md:text-5xl font-bold mb-4 drop-shadow-lg">
                Privacy Policy
            </h1>
            <p class="text-xl text-gray-100">
                Your privacy is important to us
            </p>
        </div>
    </div>
</section>

<!-- Privacy Content -->
<section class="py-16 bg-gray-50">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-lg shadow-md p-8">
            
            <div class="mb-8">
                <p class="text-gray-600 mb-4">
                    <strong>Last Updated:</strong> November 15, 2025
                </p>
                <p class="text-gray-600">
                    At CampMart, we are committed to protecting your privacy and personal information. This Privacy Policy explains how we collect, use, store, and protect your data when you use our platform.
                </p>
            </div>
            
            <!-- Section 1 -->
            <div class="mb-8">
                <h2 class="text-2xl font-bold text-primary mb-4">1. Information We Collect</h2>
                
                <h3 class="text-lg font-semibold text-gray-900 mb-3">Personal Information</h3>
                <p class="text-gray-600 mb-3">When you register on CampMart, we collect:</p>
                <ul class="list-disc pl-6 text-gray-600 space-y-2 mb-4">
                    <li>Name (first and last name)</li>
                    <li>Email address (campus email)</li>
                    <li>Phone number</li>
                    <li>Campus, department, and level</li>
                    <li>Profile picture (optional)</li>
                    <li>Password (encrypted and securely stored)</li>
                </ul>
                
                <h3 class="text-lg font-semibold text-gray-900 mb-3">Listing Information</h3>
                <p class="text-gray-600 mb-3">When you post items, we collect:</p>
                <ul class="list-disc pl-6 text-gray-600 space-y-2 mb-4">
                    <li>Item title, description, and price</li>
                    <li>Photos and images</li>
                    <li>Category and condition</li>
                    <li>Location information</li>
                </ul>
                
                <h3 class="text-lg font-semibold text-gray-900 mb-3">Automatically Collected Information</h3>
                <ul class="list-disc pl-6 text-gray-600 space-y-2">
                    <li>IP address and device information</li>
                    <li>Browser type and version</li>
                    <li>Pages visited and time spent on the platform</li>
                    <li>Search queries and filters used</li>
                    <li>Interaction with listings (views, bookmarks)</li>
                </ul>
            </div>
            
            <!-- Section 2 -->
            <div class="mb-8">
                <h2 class="text-2xl font-bold text-primary mb-4">2. How We Use Your Information</h2>
                <p class="text-gray-600 mb-3">We use your information to:</p>
                <ul class="list-disc pl-6 text-gray-600 space-y-2">
                    <li>Create and manage your account</li>
                    <li>Display your listings to other users</li>
                    <li>Facilitate communication between buyers and sellers</li>
                    <li>Send notifications about your account and listings</li>
                    <li>Improve our platform and user experience</li>
                    <li>Detect and prevent fraud and abuse</li>
                    <li>Comply with legal obligations</li>
                    <li>Analyze platform usage and trends</li>
                    <li>Provide customer support</li>
                </ul>
            </div>
            
            <!-- Section 3 -->
            <div class="mb-8">
                <h2 class="text-2xl font-bold text-primary mb-4">3. Information Sharing and Disclosure</h2>
                
                <h3 class="text-lg font-semibold text-gray-900 mb-3">Public Information</h3>
                <p class="text-gray-600 mb-4">
                    When you post a listing, certain information becomes publicly visible to other CampMart users, including your name, phone number (for contact purposes), and listing details.
                </p>
                
                <h3 class="text-lg font-semibold text-gray-900 mb-3">We Do NOT Share Your Information With:</h3>
                <ul class="list-disc pl-6 text-gray-600 space-y-2 mb-4">
                    <li>Third-party advertisers</li>
                    <li>Marketing companies</li>
                    <li>Data brokers</li>
                    <li>Any organization for commercial purposes</li>
                </ul>
                
                <h3 class="text-lg font-semibold text-gray-900 mb-3">We May Share Information:</h3>
                <ul class="list-disc pl-6 text-gray-600 space-y-2">
                    <li>When required by law or legal process</li>
                    <li>To protect the rights and safety of CampMart and its users</li>
                    <li>With service providers who help operate our platform (with strict confidentiality agreements)</li>
                    <li>In connection with a business transfer or acquisition (users will be notified)</li>
                </ul>
            </div>
            
            <!-- Section 4 -->
            <div class="mb-8">
                <h2 class="text-2xl font-bold text-primary mb-4">4. Data Security</h2>
                <p class="text-gray-600 mb-3">We implement robust security measures to protect your information:</p>
                <ul class="list-disc pl-6 text-gray-600 space-y-2">
                    <li>Passwords are encrypted using industry-standard hashing algorithms</li>
                    <li>Secure HTTPS connections for all communications</li>
                    <li>Regular security audits and updates</li>
                    <li>Access controls and authentication systems</li>
                    <li>Secure database storage with backup systems</li>
                </ul>
                <p class="text-gray-600 mt-3">
                    However, no system is 100% secure. We encourage you to use strong, unique passwords and never share your account credentials.
                </p>
            </div>
            
            <!-- Section 5 -->
            <div class="mb-8">
                <h2 class="text-2xl font-bold text-primary mb-4">5. Cookies and Tracking</h2>
                <p class="text-gray-600 mb-3">
                    CampMart uses cookies and similar technologies to:
                </p>
                <ul class="list-disc pl-6 text-gray-600 space-y-2">
                    <li>Keep you logged in to your account</li>
                    <li>Remember your preferences and settings</li>
                    <li>Analyze platform usage and performance</li>
                    <li>Improve user experience</li>
                </ul>
                <p class="text-gray-600 mt-3">
                    You can disable cookies in your browser settings, but this may affect platform functionality.
                </p>
            </div>
            
            <!-- Section 6 -->
            <div class="mb-8">
                <h2 class="text-2xl font-bold text-primary mb-4">6. Your Rights and Choices</h2>
                <p class="text-gray-600 mb-3">You have the right to:</p>
                <ul class="list-disc pl-6 text-gray-600 space-y-2">
                    <li><strong>Access:</strong> Request a copy of your personal data</li>
                    <li><strong>Correction:</strong> Update or correct your information</li>
                    <li><strong>Deletion:</strong> Request deletion of your account and data</li>
                    <li><strong>Portability:</strong> Export your data in a readable format</li>
                    <li><strong>Object:</strong> Object to certain uses of your data</li>
                    <li><strong>Opt-out:</strong> Unsubscribe from promotional communications</li>
                </ul>
                <p class="text-gray-600 mt-3">
                    To exercise these rights, contact us at <a href="mailto:privacy@campmart.ng" class="text-primary hover:underline">privacy@campmart.ng</a>
                </p>
            </div>
            
            <!-- Section 7 -->
            <div class="mb-8">
                <h2 class="text-2xl font-bold text-primary mb-4">7. Data Retention</h2>
                <p class="text-gray-600">
                    We retain your information for as long as your account is active or as needed to provide services. When you delete your account, we will delete or anonymize your personal information within 30 days, except where we are legally required to retain it longer.
                </p>
            </div>
            
            <!-- Section 8 -->
            <div class="mb-8">
                <h2 class="text-2xl font-bold text-primary mb-4">8. Children's Privacy</h2>
                <p class="text-gray-600">
                    CampMart is not intended for users under 18. If you are under 18, you must have parental consent to use our platform. We do not knowingly collect information from children under 13.
                </p>
            </div>
            
            <!-- Section 9 -->
            <div class="mb-8">
                <h2 class="text-2xl font-bold text-primary mb-4">9. Third-Party Links</h2>
                <p class="text-gray-600">
                    CampMart may contain links to external websites (e.g., WhatsApp for messaging). We are not responsible for the privacy practices of these third-party sites. Please review their privacy policies separately.
                </p>
            </div>
            
            <!-- Section 10 -->
            <div class="mb-8">
                <h2 class="text-2xl font-bold text-primary mb-4">10. Changes to This Policy</h2>
                <p class="text-gray-600">
                    We may update this Privacy Policy from time to time. We will notify users of significant changes via email or a prominent notice on the platform. Continued use after changes constitutes acceptance of the updated policy.
                </p>
            </div>
            
            <!-- Section 11 -->
            <div class="mb-8">
                <h2 class="text-2xl font-bold text-primary mb-4">11. Contact Us</h2>
                <p class="text-gray-600">
                    If you have questions, concerns, or requests regarding this Privacy Policy or your personal information, please contact us:
                </p>
                <p class="text-gray-600 mt-3">
                    Email: <a href="mailto:privacy@campmart.ng" class="text-primary hover:underline">privacy@campmart.ng</a><br>
                    Support: <a href="mailto:support@campmart.ng" class="text-primary hover:underline">support@campmart.ng</a><br>
                    Or visit our <a href="contact.php" class="text-primary hover:underline">Contact Page</a>
                </p>
            </div>
            
            <div class="border-t pt-6">
                <p class="text-gray-600 text-sm">
                    By using CampMart, you acknowledge that you have read and understood this Privacy Policy and agree to the collection and use of your information as described.
                </p>
            </div>
            
        </div>
    </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
<?php include __DIR__ . '/../includes/modals.php'; ?>
