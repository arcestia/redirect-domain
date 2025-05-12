<?php
/**
 * Domain Randomizer
 * Created: 2024-12-17
 * Author: Laurensius Jeffrey
 * License: MIT
 */

use Slim\Factory\AppFactory;
use DI\Container;
use App\Middleware\JsonBodyParserMiddleware;

require __DIR__ . '/../vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Create Container
$container = new Container();

// Create App
AppFactory::setContainer($container);
$app = AppFactory::create();

// Add Middleware
$app->addBodyParsingMiddleware();
$app->addErrorMiddleware(true, true, true);

// Add routes
$routes = require __DIR__ . '/../src/routes.php';
$routes($app);

$app->run();
