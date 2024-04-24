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
 * emailek kiküldése  
 */
$routes->cli('/cron', 'Cron::index');

/** Karbantartás */
$routes->get('/karbantartas', 'Maintenance::index');