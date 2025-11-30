<?php
/**
 * Contact Us Page
 * Get in touch with CampMart
 */

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';

$pageTitle = "Contact Us - CampMart";

include __DIR__ . '/../includes/header.php';
?>

<!-- Page Header -->
<section class="bg-primary text-white py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h1 class="text-5xl font-bold mb-4">Get In Touch</h1>
        <p class="text-xl text-gray-100">We're here to help and answer any questions you might have</p>
    </div>
</section>

<!-- Contact Section -->
<section class="py-16 bg-white">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
            
            <!-- Contact Form -->
            <div>
                <h2 class="text-2xl font-bold text-gray-900 mb-6">Send us a Message</h2>
                
                <form action="<?php echo baseUrl('includes/contact-process.php'); ?>" method="POST" class="space-y-6">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    
                    <div>
                        <label class="block text-gray-700 text-sm font-semibold mb-2">Your Name</label>
                        <input type="text" name="name" required 
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-primary transition">
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 text-sm font-semibold mb-2">Email Address</label>
                        <input type="email" name="email" required 
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-primary transition">
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 text-sm font-semibold mb-2">Subject</label>
                        <select name="subject" required 
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-primary transition">
                            <option value="">Select a subject</option>
                            <option value="General Inquiry">General Inquiry</option>
                            <option value="Technical Support">Technical Support</option>
                            <option value="Report Issue">Report Issue</option>
                            <option value="Account Help">Account Help</option>
                            <option value="Partnership">Partnership Opportunity</option>
                            <option value="Feedback">Feedback & Suggestions</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 text-sm font-semibold mb-2">Message</label>
                        <textarea name="message" rows="6" required 
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-primary transition"
                            placeholder="Tell us how we can help you..."></textarea>
                    </div>
                    
                    <button type="submit" 
                        class="w-full bg-primary text-white py-3 rounded-lg hover:bg-pink-700 transition font-semibold">
                        Send Message
                    </button>
                </form>
            </div>
            
            <!-- Contact Information -->
            <div>
                <h2 class="text-2xl font-bold text-gray-900 mb-6">Contact Information</h2>
                
                <div class="space-y-6 mb-8">
                    <div class="flex items-start">
                        <div class="bg-primary text-white rounded-full w-12 h-12 flex items-center justify-center flex-shrink-0 mr-4">
                            <i class="fas fa-envelope text-xl"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold text-lg text-gray-900 mb-1">Email</h3>
                            <a href="mailto:support@campmart.ng" class="text-primary hover:underline">
                                support@campmart.ng
                            </a>
                        </div>
                    </div>
                    
                    <div class="flex items-start">
                        <div class="bg-primary text-white rounded-full w-12 h-12 flex items-center justify-center flex-shrink-0 mr-4">
                            <i class="fab fa-whatsapp text-xl"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold text-lg text-gray-900 mb-1">WhatsApp</h3>
                            <a href="https://wa.me/2348012345678" target="_blank" class="text-primary hover:underline">
                                +234 801 234 5678
                            </a>
                            <p class="text-sm text-gray-600 mt-1">For quick support and inquiries</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start">
                        <div class="bg-primary text-white rounded-full w-12 h-12 flex items-center justify-center flex-shrink-0 mr-4">
                            <i class="fas fa-clock text-xl"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold text-lg text-gray-900 mb-1">Support Hours</h3>
                            <p class="text-gray-700">Monday - Friday: 8:00 AM - 8:00 PM</p>
                            <p class="text-gray-700">Saturday: 10:00 AM - 6:00 PM</p>
                            <p class="text-gray-700">Sunday: Closed</p>
                        </div>
                    </div>
                </div>
                
                <!-- Social Media -->
                <div class="border-t pt-8">
                    <h3 class="font-semibold text-lg text-gray-900 mb-4">Follow Us</h3>
                    <div class="flex space-x-4">
                        <a href="#" class="bg-gray-100 hover:bg-primary hover:text-white text-gray-700 rounded-full w-12 h-12 flex items-center justify-center transition">
                            <i class="fab fa-facebook text-xl"></i>
                        </a>
                        <a href="#" class="bg-gray-100 hover:bg-primary hover:text-white text-gray-700 rounded-full w-12 h-12 flex items-center justify-center transition">
                            <i class="fab fa-instagram text-xl"></i>
                        </a>
                        <a href="#" class="bg-gray-100 hover:bg-primary hover:text-white text-gray-700 rounded-full w-12 h-12 flex items-center justify-center transition">
                            <i class="fab fa-twitter text-xl"></i>
                        </a>
                        <a href="#" class="bg-gray-100 hover:bg-primary hover:text-white text-gray-700 rounded-full w-12 h-12 flex items-center justify-center transition">
                            <i class="fab fa-linkedin text-xl"></i>
                        </a>
                    </div>
                </div>
                
                <!-- FAQ Link -->
                <div class="mt-8 bg-gray-50 rounded-lg p-6">
                    <h3 class="font-semibold text-lg text-gray-900 mb-2">Looking for answers?</h3>
                    <p class="text-gray-600 mb-4">Check out our FAQ section for quick answers to common questions.</p>
                    <a href="faq.php" class="text-primary hover:underline font-semibold">
                        Visit FAQ <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
            </div>
            
        </div>
    </div>
</section>

<!-- Quick Links -->
<section class="py-16 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-2xl font-bold text-gray-900 mb-8 text-center">Need Help With Something Specific?</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <a href="how-it-works.php" class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition">
                <div class="bg-primary text-white rounded-full w-12 h-12 flex items-center justify-center mb-4">
                    <i class="fas fa-question-circle text-xl"></i>
                </div>
                <h3 class="font-semibold text-lg text-gray-900 mb-2">How It Works</h3>
                <p class="text-gray-600 text-sm">Learn how to use CampMart effectively</p>
            </a>
            
            <a href="about.php" class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition">
                <div class="bg-primary text-white rounded-full w-12 h-12 flex items-center justify-center mb-4">
                    <i class="fas fa-info-circle text-xl"></i>
                </div>
                <h3 class="font-semibold text-lg text-gray-900 mb-2">About Us</h3>
                <p class="text-gray-600 text-sm">Learn more about our mission and values</p>
            </a>
            
            <a href="terms.php" class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition">
                <div class="bg-primary text-white rounded-full w-12 h-12 flex items-center justify-center mb-4">
                    <i class="fas fa-file-alt text-xl"></i>
                </div>
                <h3 class="font-semibold text-lg text-gray-900 mb-2">Terms & Policies</h3>
                <p class="text-gray-600 text-sm">Read our terms of service and privacy policy</p>
            </a>
        </div>
    </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
