# CampMart - Campus E-commerce Platform

A production-ready e-commerce platform designed specifically for campus communities. Built with PHP, MySQLi, HTML, and Tailwind CSS.

## 🌟 Features

### Core Functionality
- **User Authentication**: Secure registration and login with session management
- **Marketplace**: Browse and search thousands of listings
- **Post Listings**: Easy-to-use modal-based listing creation
- **Categories**: Organized product categories with filtering
- **Free Corner**: Dedicated section for free items
- **Lost & Found**: Help reunite items with their owners
- **User Profiles**: Comprehensive user profiles with ratings
- **In-app Messaging**: Secure communication between buyers and sellers
- **Meet Point System**: Safe campus locations for transactions
- **Bookmarks**: Save items for later viewing
- **Trending**: Popular items and top vendors
- **Responsive Design**: Works seamlessly on all devices

### Security Features
- CSRF protection on all forms
- SQL injection prevention with prepared statements
- Password hashing with bcrypt
- XSS protection with input sanitization
- Secure session management
- File upload validation

## 🚀 Installation

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- mod_rewrite enabled (for clean URLs)

### Setup Instructions

1. **Clone the repository**
   ```bash
   git clone https://github.com/bravepetal/campmart.git
   cd campmart
   ```

2. **Configure the database**
   - Create a new MySQL database named `campmart`
   - Import the database schema:
     ```bash
     mysql -u root -p campmart < database/schema.sql
     ```

3. **Configure the application**
   - Open `config/config.php`
   - Update database credentials:
     ```php
     define('DB_HOST', 'localhost');
     define('DB_USER', 'your_username');
     define('DB_PASS', 'your_password');
     define('DB_NAME', 'campmart');
     ```
   - Update `SITE_URL` to match your domain

4. **Set up file permissions**
   ```bash
   chmod 755 assets/uploads/
   chmod 755 assets/uploads/listings/
   chmod 755 assets/uploads/profiles/
   chmod 755 assets/uploads/lost_found/
   ```

5. **Access the application**
   - Open your browser and navigate to your installation URL
   - Register a new account to get started

## 📁 Project Structure

```
campmart/
├── assets/
│   ├── css/           # Custom stylesheets
│   ├── js/            # JavaScript files
│   ├── images/        # Static images
│   └── uploads/       # User uploaded files
├── config/
│   └── config.php     # Application configuration
├── database/
│   └── schema.sql     # Database schema
├── includes/
│   ├── auth/          # Authentication handlers
│   ├── listing/       # Listing management
│   ├── db.php         # Database connection
│   ├── session.php    # Session management
│   ├── functions.php  # Utility functions
│   ├── header.php     # Global header
│   ├── footer.php     # Global footer
│   └── modals.php     # Modal components
├── pages/
│   ├── admin/         # Admin panel
│   ├── marketplace.php
│   ├── profile.php
│   └── ...            # Other pages
└── index.php          # Homepage
```

## 🎨 Brand Colors

- **Primary**: `#B8144A` (Deep berry/maroon-pink)
- **Secondary**: `#F5F5F5` (Light grey)
- **Accent**: `#FFA500` (Orange)

## 🔒 Security

This platform implements multiple security measures:
- All user inputs are sanitized and validated
- SQL queries use prepared statements
- CSRF tokens on all forms
- Password hashing using bcrypt
- Secure session configuration
- File upload validation and restrictions

## 📊 Database Schema

The platform uses 16 core tables:
1. `users` - User accounts
2. `campuses` - Campus information
3. `departments` - Academic departments
4. `levels` - Student levels
5. `categories` - Item categories
6. `listings` - Marketplace items
7. `listing_images` - Item images
8. `listing_tags` - Item tags
9. `saved_items` - Bookmarked items
10. `chats` - Chat conversations
11. `messages` - Chat messages
12. `meet_points` - User meet points
13. `meetpoint_suggestions` - Suggested meet points
14. `lost_found_items` - Lost and found items
15. `reports` - User reports
16. `seller_ratings` - Seller ratings

## 🛠️ Technology Stack

- **Backend**: PHP 7.4+ with MySQLi
- **Frontend**: HTML5, Tailwind CSS 3.x
- **Icons**: Font Awesome 6
- **Architecture**: MVC-inspired structure
- **Database**: MySQL 5.7+

## 👥 User Roles

### Regular Users
- Create and manage listings
- Browse and search marketplace
- Message other users
- Save items for later
- Rate sellers
- Report listings

### Administrators
- All user permissions
- Manage users (suspend/activate)
- Moderate listings
- Review reports
- Access analytics
- System configuration

## 🎯 Optimizations

The platform is optimized for 10,000+ Monthly Active Users:
- Indexed database queries
- Efficient pagination
- Image optimization
- Connection pooling with singleton pattern
- Session management
- Caching strategies

## 📱 Responsive Design

The platform is fully responsive and works on:
- Desktop (1920px+)
- Laptop (1024px - 1919px)
- Tablet (768px - 1023px)
- Mobile (320px - 767px)

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## 📄 License

This project is proprietary software. All rights reserved.

## 📞 Support

For support, email support@campmart.ng or visit our Help Center.

## 🔄 Version

Current Version: 1.0.0

## 👨‍💻 Development Status

✅ Phase 1: Database & Core Structure - Complete
✅ Phase 2: Authentication System - Complete
⏳ Phase 3: Core Pages & Navigation - In Progress
⏳ Phase 4: Listing Management - In Progress
⏳ Remaining Phases - Pending

---

Built with ❤️ for campus communities across Nigeria
