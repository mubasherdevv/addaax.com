<?php
/**
 * Product Data
 * 
 * This file contains the sample product data that would normally be stored in a database.
 * In a production environment, this should be replaced with actual database queries.
 */

// Sample products array
$products = [
    1 => [
        'name' => 'Premium Office Chair',
        'price' => 89.99,
        'bulk_price' => 79.99,
        'bulk_qty' => 10,
        'description' => 'High-quality ergonomic office chair with adjustable height, lumbar support, and premium cushioning for all-day comfort. Perfect for offices, home workspaces, and conference rooms.',
        'features' => [
            'Ergonomic design for proper posture',
            'Adjustable height and tilt mechanism',
            'Breathable mesh backrest',
            'Premium cushioned seat',
            'Durable steel frame with 5-year warranty',
            'Weight capacity: 300 lbs'
        ],
        'specifications' => [
            'Dimensions' => '26"W x 26"D x 38-42"H',
            'Weight' => '35 lbs',
            'Material' => 'Mesh, Fabric, Steel Frame',
            'Color Options' => 'Black, Gray, Blue',
            'Assembly' => 'Minimal assembly required',
        ],
        'images' => ['product1.jpg', 'product1-2.jpg', 'product1-3.jpg'],
        'category' => 'furniture',
        'badge' => 'Best Seller',
        'stock' => 45
    ],
    2 => [
        'name' => 'Wireless Earbuds',
        'price' => 49.99,
        'bulk_price' => 39.99,
        'bulk_qty' => 20,
        'description' => 'High-quality wireless earbuds featuring premium sound, active noise cancellation, and long battery life. Perfect for professionals and music enthusiasts alike.',
        'features' => [
            'True wireless design with Bluetooth 5.2',
            'Active noise cancellation',
            'Up to 8 hours of playback (24 hours with charging case)',
            'IPX7 water resistance',
            'Touch controls for playback and calls',
            'Built-in microphones for clear calls'
        ],
        'specifications' => [
            'Dimensions' => 'Earbuds: 0.8" x 0.7" x 0.9", Case: 2.5" x 1.5" x 1"',
            'Weight' => 'Earbuds: 5g each, Case: 35g',
            'Battery' => 'Earbuds: 60mAh each, Case: 500mAh',
            'Charging' => 'USB-C, Wireless Qi compatible',
            'Color Options' => 'Black, White, Blue'
        ],
        'images' => ['product2.jpg', 'product2-2.jpg', 'product2-3.jpg'],
        'category' => 'electronics',
        'badge' => '',
        'stock' => 120
    ],
    3 => [
        'name' => 'Stainless Steel Water Bottles',
        'price' => 14.99,
        'bulk_price' => 11.99,
        'bulk_qty' => 50,
        'description' => 'Premium stainless steel water bottles that keep beverages cold for 24 hours or hot for 12 hours. Durable, eco-friendly design perfect for offices, gyms, and outdoor activities.',
        'features' => [
            'Double-wall vacuum insulation',
            'Premium 18/8 stainless steel construction',
            'BPA-free and eco-friendly',
            'Leak-proof design',
            'Fits most cup holders',
            'Available in multiple sizes and colors'
        ],
        'specifications' => [
            'Capacity' => '20 oz (590ml)',
            'Dimensions' => '2.9" dia x 10.5"H',
            'Weight' => '12.8 oz (empty)',
            'Material' => '18/8 food-grade stainless steel',
            'Color Options' => 'Silver, Black, Blue, Red, Green'
        ],
        'images' => ['product3.jpg', 'product3-2.jpg', 'product3-3.jpg'],
        'category' => 'office',
        'badge' => '',
        'stock' => 200
    ],
    4 => [
        'name' => 'LED Desk Lamp',
        'price' => 29.99,
        'bulk_price' => 24.99,
        'bulk_qty' => 15,
        'description' => 'Modern LED desk lamp with adjustable brightness levels and color temperatures. Energy-efficient design with flexible arm for optimal lighting positioning.',
        'features' => [
            'Touch-sensitive controls',
            '5 brightness levels and 3 color temperatures',
            'Flexible adjustable arm',
            'USB charging port',
            'Auto-off timer function',
            'Energy-efficient LED technology'
        ],
        'specifications' => [
            'Dimensions' => '7" base x 18" max height',
            'Weight' => '2.2 lbs',
            'Power' => '9W LED, 50,000 hour lifespan',
            'Color Temperature' => '3000K-6000K',
            'Material' => 'Aluminum alloy and ABS plastic',
            'Color Options' => 'Black, White, Silver'
        ],
        'images' => ['product4.jpg', 'product4-2.jpg', 'product4-3.jpg'],
        'category' => 'office',
        'badge' => '',
        'stock' => 75
    ],
    5 => [
        'name' => 'Bluetooth Speaker',
        'price' => 59.99,
        'bulk_price' => 49.99,
        'bulk_qty' => 10,
        'description' => 'Powerful Bluetooth speaker with rich sound quality and deep bass. Perfect for meetings, presentations, or office events with its long battery life and versatile connectivity options.',
        'features' => [
            'Bluetooth 5.0 connectivity',
            '20W stereo sound with enhanced bass',
            'Up to 12 hours of playtime',
            'IPX5 water resistance',
            'Built-in microphone for calls',
            'AUX and microSD card inputs'
        ],
        'specifications' => [
            'Dimensions' => '7.3" x 2.9" x 3.1"',
            'Weight' => '1.6 lbs',
            'Battery' => '3600mAh rechargeable',
            'Charging Time' => '3-4 hours',
            'Wireless Range' => 'Up to 100 feet',
            'Audio' => '20W dual drivers with passive bass radiator'
        ],
        'images' => ['product5.jpg', 'product5-2.jpg', 'product5-3.jpg'],
        'category' => 'electronics',
        'badge' => 'New',
        'stock' => 60
    ],
    6 => [
        'name' => 'Eco-Friendly Notebooks',
        'price' => 8.99,
        'bulk_price' => 6.99,
        'bulk_qty' => 30,
        'description' => 'High-quality eco-friendly notebooks made from 100% recycled paper. Durable spiral binding and perforated pages make these perfect for meetings, note-taking, and planning.',
        'features' => [
            '100% recycled paper with 80 sheets (160 pages)',
            'Acid-free paper suitable for all pen types',
            'Durable spiral binding that lays flat',
            'Perforated pages for easy removal',
            'Interior pocket for loose papers',
            'Available in lined, grid, or dot pattern'
        ],
        'specifications' => [
            'Dimensions' => '8.5" x 11"',
            'Weight' => '0.75 lbs per notebook',
            'Paper' => '100% recycled, 80 sheets, 100 gsm',
            'Cover' => 'Kraft recycled cardboard',
            'Pattern Options' => 'Lined, Grid, Dot',
            'Color Options' => 'Natural, Blue, Green, Black'
        ],
        'images' => ['product6.jpg', 'product6-2.jpg', 'product6-3.jpg'],
        'category' => 'office',
        'badge' => 'New',
        'stock' => 150
    ],
    7 => [
        'name' => 'Wireless Charger',
        'price' => 24.99,
        'bulk_price' => 19.99,
        'bulk_qty' => 15,
        'description' => 'Fast wireless charging pad compatible with all Qi-enabled devices. Sleek, low-profile design perfect for desks and nightstands with overcharge protection and LED indicator.',
        'features' => [
            'Qi-certified with 10W fast charging',
            'Compatible with all Qi-enabled smartphones',
            'Slim, compact design',
            'Anti-slip surface and base',
            'Overcharge and temperature protection',
            'LED charging indicator'
        ],
        'specifications' => [
            'Dimensions' => '3.5" diameter x 0.4" height',
            'Weight' => '3.2 oz',
            'Input' => 'USB-C, 5V/2A, 9V/1.67A',
            'Output' => '5W, 7.5W, 10W (device dependent)',
            'Cable Length' => '3.3 ft / 1m',
            'Color Options' => 'Black, White, Blue'
        ],
        'images' => ['product7.jpg', 'product7-2.jpg', 'product7-3.jpg'],
        'category' => 'electronics',
        'badge' => 'New',
        'stock' => 85
    ],
    8 => [
        'name' => 'Reusable Shopping Bags',
        'price' => 12.99,
        'bulk_price' => 9.99,
        'bulk_qty' => 50,
        'description' => 'Heavy-duty reusable shopping bags made from recycled materials. Foldable design with reinforced handles can hold up to 40 pounds, perfect for corporate gifts and eco-friendly initiatives.',
        'features' => [
            'Made from recycled polyester fabric',
            'Folds into included pouch for easy storage',
            'Reinforced handles and stitching',
            'Holds up to 40 lbs / 18 kg',
            'Machine washable',
            'Available in multiple colors and designs'
        ],
        'specifications' => [
            'Dimensions' => '14" x 18" x 6" when open',
            'Folded Size' => '4" x 4" pouch',
            'Weight' => '2.4 oz per bag',
            'Material' => 'Recycled ripstop polyester',
            'Capacity' => '30 liters / 40 lbs',
            'Pack Sizes' => 'Sets of 5, 10, or 25 bags'
        ],
        'images' => ['product8.jpg', 'product8-2.jpg', 'product8-3.jpg'],
        'category' => 'office',
        'badge' => 'New',
        'stock' => 250
    ],
    9 => [
        'name' => 'Desktop Monitor Stand',
        'price' => 34.99,
        'bulk_price' => 29.99,
        'bulk_qty' => 10,
        'description' => 'Ergonomic monitor stand with adjustable height settings to improve posture and reduce neck strain. Includes storage drawer and cable management system for a clean, organized workspace.',
        'features' => [
            'Adjustable height settings (4" to 6")',
            'Built-in storage drawer',
            'Cable management system',
            'Non-slip feet and padding',
            'Supports monitors up to 33 lbs',
            'Sleek, modern design'
        ],
        'specifications' => [
            'Dimensions' => '16.7" W x 9.4" D x 4-6" H',
            'Weight' => '4.5 lbs',
            'Weight Capacity' => 'Up to 33 lbs',
            'Material' => 'Bamboo or metal options',
            'Drawer Dimensions' => '15.7" W x 8.3" D x 1" H',
            'Color Options' => 'Black, White, Bamboo, Silver'
        ],
        'images' => ['product9.jpg', 'product9-2.jpg', 'product9-3.jpg'],
        'category' => 'office',
        'badge' => '',
        'stock' => 60
    ],
    10 => [
        'name' => 'USB-C Hub',
        'price' => 39.99,
        'bulk_price' => 34.99,
        'bulk_qty' => 15,
        'description' => 'Multi-port USB-C hub adapter with HDMI, USB-A, SD card reader, and power delivery. Essential accessory for laptops with limited ports, perfect for presentations and remote work.',
        'features' => [
            '7-in-1 hub with multiple ports',
            '4K HDMI video output',
            '100W Power Delivery pass-through',
            'SD/microSD card readers',
            '3 USB-A 3.0 ports',
            'Compact, portable design'
        ],
        'specifications' => [
            'Dimensions' => '4.1" x 1.2" x 0.5"',
            'Weight' => '2.5 oz',
            'HDMI Output' => 'Up to 4K @ 60Hz',
            'USB Ports' => '3x USB-A 3.0 (5Gbps)',
            'Card Reader' => 'SD/microSD UHS-I',
            'Power Delivery' => 'USB-C PD up to 100W'
        ],
        'images' => ['product10.jpg', 'product10-2.jpg', 'product10-3.jpg'],
        'category' => 'electronics',
        'badge' => '',
        'stock' => 90
    ],
    11 => [
        'name' => 'Executive Pen Set',
        'price' => 19.99,
        'bulk_price' => 16.99,
        'bulk_qty' => 25,
        'description' => 'Elegant executive pen set featuring a ballpoint pen and roller ball pen in a premium gift box. Perfect for corporate gifting, with custom engraving available for bulk orders.',
        'features' => [
            'High-quality ballpoint and roller ball pens',
            'Medium point with smooth writing experience',
            'Ergonomic grip for comfort',
            'Twist mechanism for ballpoint',
            'Cap-off design for roller ball',
            'Premium gift box included'
        ],
        'specifications' => [
            'Pen Length' => '5.5 inches',
            'Weight' => '1.2 oz per pen',
            'Material' => 'Brass body with chrome accents',
            'Ink' => 'Black ballpoint, blue roller ball',
            'Refill Compatibility' => 'Standard refills',
            'Customization' => 'Laser engraving available for bulk orders'
        ],
        'images' => ['product11.jpg', 'product11-2.jpg', 'product11-3.jpg'],
        'category' => 'office',
        'badge' => '',
        'stock' => 100
    ],
    12 => [
        'name' => 'Wireless Keyboard and Mouse Combo',
        'price' => 49.99,
        'bulk_price' => 44.99,
        'bulk_qty' => 10,
        'description' => 'Ergonomic wireless keyboard and mouse combination with advanced features for improved productivity. Low-profile keys, customizable shortcuts, and long battery life make it perfect for any workspace.',
        'features' => [
            'Wireless 2.4GHz connection with single USB receiver',
            'Ergonomic design for reduced strain',
            'Quiet, low-profile keys',
            'Programmable shortcut keys',
            'Up to 12 months battery life',
            'Compatible with Windows and Mac'
        ],
        'specifications' => [
            'Keyboard Dimensions' => '17.7" x 5.2" x 0.9"',
            'Mouse Dimensions' => '4.1" x 2.8" x 1.5"',
            'Weight' => 'Keyboard: 1.2 lbs, Mouse: 3.4 oz',
            'Battery' => 'Keyboard: 2 AAA, Mouse: 1 AA (included)',
            'Wireless Range' => 'Up to 33 feet / 10 meters',
            'DPI Settings' => '800, 1200, 1600 DPI adjustable'
        ],
        'images' => ['product12.jpg', 'product12-2.jpg', 'product12-3.jpg'],
        'category' => 'electronics',
        'badge' => '',
        'stock' => 55
    ],
    // Add more products here as needed
];

/**
 * To add a new product, follow this template:
 * 
 * $products[PRODUCT_ID] = [
 *     'name' => 'Product Name',
 *     'price' => 0.00,
 *     'bulk_price' => 0.00,
 *     'bulk_qty' => 10,
 *     'description' => 'Product description text',
 *     'features' => [
 *         'Feature 1',
 *         'Feature 2',
 *         'Feature 3',
 *     ],
 *     'specifications' => [
 *         'Spec Name 1' => 'Spec Value 1',
 *         'Spec Name 2' => 'Spec Value 2',
 *         'Spec Name 3' => 'Spec Value 3',
 *     ],
 *     'images' => ['main-image.jpg', 'image2.jpg', 'image3.jpg'],
 *     'category' => 'category-name',
 *     'badge' => '', // Optional badge like 'New', 'Best Seller', etc.
 *     'stock' => 10, // Number of items in stock
 * ];
 */ 