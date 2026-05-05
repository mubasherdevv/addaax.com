<?php
// Path Constants
define('ROOT_PATH', dirname(__DIR__));
define('UPLOADS_PATH', ROOT_PATH . '/uploads');
define('PRODUCTS_UPLOADS_PATH', UPLOADS_PATH . '/products');

// URL Constants (adjust as needed for your environment)
define('BASE_URL', 'http://localhost/main');
define('ADMIN_URL', BASE_URL . '/auth');

// Site Constants
define('SITE_NAME', 'Wholesale E-commerce');
define('SITE_EMAIL', 'admin@example.com');

// Other settings
define('DEFAULT_PAGINATION_LIMIT', 20);
?> 