<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/','AuthController::login');
$routes->post('auth/autenticar','AuthController::autenticar');
$routes->get('auth/logout','AuthController::logout');

$routes->get('dashboard', 'DashboardController::index');
$routes->get('personal', 'PersonalController::index');