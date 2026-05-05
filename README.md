# Wholesale E-Commerce Website

A modern, responsive e-commerce website designed for wholesale businesses, featuring multi-level category navigation, bulk pricing, comprehensive product filtering, and QR code-based inventory management.

## Features

### User Interface
- Modern, responsive design with animations and transitions
- Mobile-friendly interface with dedicated responsive styles
- Font Awesome icons integration
- Custom animations for enhanced user experience
- Dynamic product image handling with primary/secondary images
- Notification system for user feedback

### Home Page
- Hero section with search functionality
- Categories overview with visual navigation
- New arrivals section with dynamic product loading
- Featured products showcase
- Customer testimonials section
- Call-to-action sections

### Product Management
- Multi-level category navigation system
- Advanced product filtering:
  - Category-based filtering
  - Search functionality
  - Price range filtering
  - Availability status
  - Bulk discount filtering
- Multiple sorting options:
  - Newest first
  - Price (Low to High)
  - Price (High to Low)
  - Popularity
  - Discount amount
- Pagination system for product listings
- Product details page with:
  - Multiple product images
  - Detailed product information
  - Bulk pricing options
  - Stock availability
  - Product reviews and ratings
  - Related products

### Shopping Cart & Checkout
- Shopping cart functionality
- Cart item management (add/remove/update quantity)
- Checkout process
- Order confirmation system
- Bulk order handling
- Order tracking

### User Authentication
- User registration and login system
- Account management
- Secure session handling
- Password reset functionality
- User profile management

### Admin Features
- Product management
- Category management
- Order management
- User management
- Website settings configuration
- Featured content management
- Inventory management
- QR code generation and scanning
- Product import/export functionality
- Order export to PDF

### QR Code Inventory Management
- QR code generation for products
- QR code scanning for inventory updates
- Multiple scanning modes:
  - Single decrease mode
  - Bulk decrease mode
  - Multi-product scanning mode
- Real-time inventory updates
- Inventory history tracking
- User-friendly scanning interface
- Mobile-responsive scanner

### Wishlist System
- Add products to wishlist
- Remove products from wishlist
- View wishlist items
- Move items from wishlist to cart

### Review & Rating System
- Product reviews
- Star ratings
- Review management
- Review moderation

## Technology Stack

- PHP 7.4+
- MySQL Database
- HTML5
- CSS3 with responsive design
- Vanilla JavaScript
- Font Awesome icons
- Google Fonts (Inter)
- HTML5 QR Code Scanner

## Directory Structure

```
wholesale-ecommerce/
│
├── index.php             # Home page
├── products.php          # Products listing page
├── product_details.php   # Individual product page
├── cart.php             # Shopping cart
├── checkout.php         # Checkout process
├── order_confirmation.php # Order confirmation
├── bulk-orders.php      # Bulk order handling
├── about.php            # About page
├── contact.php          # Contact page
├── categories.php       # Category management
├── auth/                # Authentication and admin files
│   ├── login.php        # User login
│   ├── register.php     # User registration
│   ├── dashboard.php    # User dashboard
│   ├── admin_dashboard.php # Admin dashboard
│   ├── scan.php         # QR code scanner
│   ├── product_management.php # Product management
│   ├── category_management.php # Category management
│   ├── order_management.php # Order management
│   ├── user_management.php # User management
│   └── ...              # Other admin files
├── css/                 # Stylesheets
│   ├── styles.css       # Main stylesheet
│   ├── responsive.css   # Responsive styles
│   ├── product-styles.css # Product specific styles
│   ├── cart-styles.css  # Cart styles
│   ├── auth.css         # Authentication styles
│   └── ...              # Other style files
├── js/                  # JavaScript files
│   ├── main.js          # Main JavaScript
│   ├── cart.js          # Cart functionality
│   ├── responsive.js    # Responsive behavior
│   ├── notifications.js # Notification system
│   └── ...              # Other JS files
├── includes/            # PHP includes
│   ├── config.php       # Configuration
│   ├── functions.php    # Helper functions
│   ├── nav.php          # Navigation
│   ├── auth_check.php   # Authentication check
│   └── ...              # Other include files
├── uploads/             # Uploaded files
│   ├── products/        # Product images
│   ├── categories/      # Category images
│   ├── users/           # User profile images
│   └── qrcodes/         # Generated QR codes
├── images/              # Website images
├── config/              # Configuration files
├── sql/                 # Database related files
└── logs/                # Error logs
```

## Getting Started

1. Clone the repository
2. Set up a local web server (e.g., XAMPP)
3. Import the database schema from `sql/` directory
4. Configure database connection in `config.php`
5. Access the website through your local server

## QR Code Scanning Setup

1. **Database Setup**
   - Run the SQL script to add the QR code column to the products table:
     ```
     php auth/add_qr_code_column.php
     ```
   - Run the SQL script to create the inventory logs table:
     ```
     php auth/create_inventory_logs_table.php
     ```

2. **QR Code Generation**
   - QR codes are automatically generated when new products are added through the admin dashboard
   - Each product's QR code contains its unique product ID in JSON format

3. **Using the QR Code Scanner**
   - Log in as an admin user
   - Navigate to the QR Code Scanner page from the admin dashboard
   - Choose scanning mode (Single, Bulk, or Multi-Product)
   - Click "Start Scanner" to activate the camera
   - Point the camera at a product's QR code
   - Follow the on-screen instructions

## Browser Support

The website is compatible with:
- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)

## License

This project is available for personal and commercial use.

## Future Enhancements

Planned features for future releases:
- Advanced search functionality with filters
- Product comparison feature
- Advanced reporting and analytics
- Multi-language support
- Integration with popular payment gateways
- API for third-party integrations
- Mobile app for inventory management
- Barcode scanning support 