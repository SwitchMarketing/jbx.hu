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

/** UNAS */
$routes->get('/unas', 'UnasTest::index');