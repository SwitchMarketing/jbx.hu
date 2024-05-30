<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

/**
 * Kapcsolat
 */
$routes->post('/kapcsolat', 'Home::submit');
$routes->get('/sikeres-kapcsolatfelvetel', 'Home::success');

/**
 * Adatkezelés oldal
 */
$routes->get('/adatkezeles', 'PrivacyPolicy::index');

/**
 * Sütikezelés
 */
$routes->get('/sutikezeles', 'CookiePolicy::index');

/**
 * Impresszum oldal
 */
$routes->get('/impresszum', 'Impressum::index');

/**
 * Érintettségi tájékoztató oldal
 */
$routes->get('/erintettseg', 'LegalPages::exposure');

/**
 * emailek kiküldése  
 */
$routes->cli('/cron', 'Cron::index');

/** Karbantartás */
$routes->get('/karbantartas', 'Maintenance::index');


/** Admin */
$routes->group('admin', static function ($routes) {
    
    $routes->resource('sessiondata', ['controller' =>'Admin\SessionData', 'only' => ['index']]);
    $routes->resource('login', ['controller' =>'Admin\Login', 'only' => ['create']]);
    $routes->resource('logout', ['controller' =>'Admin\Logout', 'only' => ['index']]);
    $routes->resource('leads', ['controller' =>'Admin\Leads', 'only' => ['index', 'show'], 'filter' => 'loggedin']);
    $routes->resource('download', ['controller' =>'Admin\Download', 'only' => ['show'], 'filter' => 'loggedin']);
});

