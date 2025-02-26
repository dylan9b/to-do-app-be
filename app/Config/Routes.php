<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->group('api', ['filter' => 'options'], function($routes) {
  $routes->post('loginGoogle', 'AuthController::loginGoogle');
  $routes->post('login', 'AuthController::login');
  $routes->post('register', 'AuthController::register');
  
  $routes->get('priorities', 'PriorityController::index');
  $routes->post('priorities/create', 'PriorityController::create');
  
  $routes->get('roles', 'RoleController::index');
  $routes->get('users', 'UserController::index');
  
  $routes->post('todos/get', 'TodoController::get');
  $routes->put('todos/update', 'TodoController::update');
  $routes->post('todos/create', 'TodoController::create');
  $routes->delete('todos/delete', 'TodoController::delete');
});
