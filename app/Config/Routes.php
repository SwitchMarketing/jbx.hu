<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

/** 
 * Termékek
 */
$routes->get('/gepbiztonsagi-kerites', 'Products::xguard');
$routes->get('/kabeltalca-megoldasok', 'Products::xtray');
$routes->get('/utkozesvedelem', 'Products::xprotect');
$routes->get('/raktarbiztonsagi-megoldasok', 'Products::xstore');
$routes->get('/ingatlan-megoldasok', 'Products::property');

/** 
 * Bemutatkozó
 */
$routes->get('/bemutatkozo', 'About::index');

/** 
 * Minőségbiztosítás
 */
$routes->get('/minosegbiztositasi-nyilatkozat', 'QualityPolicy::index');

/**
 * Kapcsolat
 */
$routes->get('/kapcsolat', 'Contact::index');
$routes->post('/kapcsolat', 'Home::submit');
$routes->get('/sikeres-kapcsolatfelvetel', 'Home::success');

/**
 * Adatkezelés oldal
 */
$routes->get('/adatkezeles', 'LegalPages::privacy');

/**
 * Általános szerződési feltételek
 */
$routes->get('/altalanos-szerzodesi-feltetelek', 'LegalPages::terms');

/**
 * Sütikezelés
 */
$routes->get('/sutikezeles', 'LegalPages::cookies');

/**
 * Impresszum oldal
 */
$routes->get('/impresszum', 'LegalPages::impressum');

/**
 * Érintettségi tájékoztató oldal
 */
$routes->get('/erintettseg', 'LegalPages::exposure');

/**
 * Shop belépés / regisztráció
 */
$routes->get('/belepes', 'ShopLoginRegister::index');

/**
 * Teszt funkciók (csak fejlesztéshez)
 */
$routes->get('/teszt-kosar', 'Home::testCart');
$routes->get('/debug-kategoriak', 'Home::debugCategories');

/**
 * Shop kategóriák listaoldal
 */
$routes->get('/termekek/kategoriak', 'ShopCategories::index');
$routes->get('/kategoriak', static function() {
    return redirect()->to(base_url('termekek/kategoriak'), 301);
});

/**
 * Shop kategóriák
 * Dinamikus kategória útvonalak betöltése cache-ből
 */
$categoryRouteCache = WRITEPATH . 'cache/category_routes.php';
if (!file_exists($categoryRouteCache)) {
    \App\Helpers\CategoryRouteCache::generate();
}
if (file_exists($categoryRouteCache)) {
    require $categoryRouteCache;
}

/**
 * Shop termékek
 */
$routes->get('/termekek', 'ShopProducts::index');
$routes->get('/termekek/(:segment)/(:segment)', 'ShopProducts::product/$1/$2');
$routes->post('/termek', 'ShopProducts::productVariation');
$routes->get('/google-merchant-feed.xml', 'GoogleMerchantFeed::index');

/**
 * Shop kosár
 */
$routes->get('/kosar', 'ShopCart::index');
$routes->post('/kosar', 'ShopCart::add');
$routes->post('/kosar/torles', 'ShopCart::remove');
$routes->post('/kosar/mennyiseg', 'ShopCart::updateQty');

/**
 * Shop pénztár
 */
$routes->get('/megrendeles', 'ShopCheckout::index');
$routes->get('/penztar', static function() {
    return redirect()->to(base_url('megrendeles'), 301);
});
$routes->post('/megrendeles', 'ShopCheckout::submit');
$routes->get('/sikeres-megrendeles', 'ShopCheckout::success');


/** Blog */
$routes->get('/blog', 'Blog::index');

/**
 * Blog aloldalak
 * Dinamikus blog aloldalak betöltése cache-ből
 */
$blogRouteCache = WRITEPATH . 'cache/blog_routes.php';
if (file_exists($blogRouteCache)) {
    require $blogRouteCache;
}

/**
 * emailek kiküldése  
 */
$routes->cli('/cron', 'Cron::index');


/** UNAS */
$routes->get('/unas', 'UnasTest::index');

/** Admin */
$routes->group('admin', static function ($routes) {    
    $routes->resource('sessiondata', ['controller' =>'Admin\SessionData', 'only' => ['index']]);
    $routes->resource('login', ['controller' =>'Admin\Login', 'only' => ['create']]);
    $routes->resource('logout', ['controller' =>'Admin\Logout', 'only' => ['index']]);
    $routes->resource('leads', ['controller' =>'Admin\Leads', 'only' => ['index', 'show'], 'filter' => 'loggedin']);
    $routes->resource('orders', ['controller' =>'Admin\Orders', 'only' => ['index', 'show'], 'filter' => 'loggedin']);
    $routes->resource('products', ['controller' =>'Admin\Products', 'filter' => 'loggedin']);
    $routes->post('products/bulk_move_category', 'Admin\Products::bulkMoveCategory', ['filter' => 'loggedin']);
    $routes->resource('productvariants', ['controller' =>'Admin\ProductVariants', 'only' => ['create', 'update', 'delete'], 'filter' => 'loggedin']);
    $routes->post('products/save_default_attributes/(:num)', 'Admin\Products::saveDefaultAttributes/$1', ['filter' => 'loggedin']);
    $routes->post('productvariants/reorder', 'Admin\ProductVariants::reorder', ['filter' => 'loggedin']);
    $routes->post('productvariants/save_attributes/(:num)', 'Admin\ProductVariants::saveAttributes/$1', ['filter' => 'loggedin']);
    $routes->post('productvariants/parse_names/(:num)', 'Admin\ProductVariants::parseNames/$1', ['filter' => 'loggedin']);
    $routes->resource('attributes', ['controller' =>'Admin\Attributes', 'only' => ['index', 'create', 'update', 'delete'], 'filter' => 'loggedin']);
    $routes->resource('categories', ['controller' =>'Admin\Categories', 'filter' => 'loggedin']);
    $routes->post('categories/upload_image/(:num)', 'Admin\Categories::uploadImage/$1', ['filter' => 'loggedin']);
    $routes->resource('images', ['controller' => 'Admin\Images', 'filter' => 'loggedin']);
    $routes->post('images/upload', 'Admin\Images::upload', ['filter' => 'loggedin']);
    $routes->post('images/reorder', 'Admin\Images::reorder', ['filter' => 'loggedin']);
    $routes->resource('settings', ['controller' => 'Admin\Settings', 'filter' => 'loggedin']);
    $routes->resource('download', ['controller' =>'Admin\Download', 'only' => ['show'], 'filter' => 'loggedin']);
});

/** Karbantartás */
$routes->get('/karbantartas', 'Maintenance::index');