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
 * Shop kategóriák
 * Dinamikus kategória útvonalak betöltése cache-ből
 */
$categoryRouteCache = WRITEPATH . 'cache/category_routes.php';
if (file_exists($categoryRouteCache)) {
    require $categoryRouteCache;
}

/**
 * Shop termékek
 */
$routes->get('/termekek', 'ShopProducts::index');
$routes->get('/termekek/(:segment)', 'ShopProducts::product/$1');
$routes->post('/termek', 'ShopProducts::productVariation');

/**
 * Shop kosár
 */
$routes->get('/kosar', 'ShopCart::index');
$routes->post('/kosar', 'ShopCart::add');
$routes->post('/kosar/torles', 'ShopCart::remove');

/**
 * Shop pénztár
 */
$routes->get('/penztar', 'ShopCheckout::index');

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
    $routes->resource('download', ['controller' =>'Admin\Download', 'only' => ['show'], 'filter' => 'loggedin']);
});

/** Karbantartás */
$routes->get('/karbantartas', 'Maintenance::index');