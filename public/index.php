<?php
// public/index.php

require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Request.php';
require_once __DIR__ . '/../core/Response.php';
require_once __DIR__ . '/../core/Router.php';
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/Middleware.php';

require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../controllers/ProductController.php';
require_once __DIR__ . '/../controllers/CartController.php';
require_once __DIR__ . '/../controllers/OrderController.php';
require_once __DIR__ . '/../controllers/CouponController.php';
require_once __DIR__ . '/../controllers/EventController.php';

$router = new Router();
$request = new Request();

$router->add('GET', '/products', 'ProductController::getAll');
$router->add('POST', '/auth/signup', 'AuthController::signup');
$router->add('POST', '/auth/login', 'AuthController::login');

// Add more routes as needed...

$router->dispatch($request);
