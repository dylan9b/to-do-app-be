<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

$routes->get('priorities', 'PriorityController::index');
$routes->get('priorities/(:segment)', 'PriorityController::index/$1');