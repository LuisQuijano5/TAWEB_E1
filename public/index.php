<?php
require_once __DIR__ . '/../core/Router.php';
require_once __DIR__ . '/../res/PasswordResource.php';
require_once __DIR__ . '/../res/UrlResource.php';
require_once __DIR__ . '/../vendor/autoload.php';

//test
// require_once __DIR__ . '/../res/TestDB.php';
// $testDB = new TestDB();

$router = new Router('', '/api'); // se puede poner el v1 pero por lo que pide el pdf no lo pongo
$passRes = new PasswordResource();
$urlRes = new UrlResource();

// exa 1
$router->addRoute('GET', '/password', [$passRes, 'generateSingle']);
$router->addRoute('POST', '/passwords', [$passRes, 'generateMultiple']);
$router->addRoute('POST', '/password/validate', [$passRes, 'validate']);

// exa 3


// exa 2, hasta abajo por la ultima ruta
$router->addRoute('POST', '/url/shorten', [$urlRes, 'shorten']);
$router->addRoute('GET', '/url/stats/{code}', [$urlRes, 'stats']);
$router->addRoute('GET', '/{code}', [$urlRes, 'redirect']);

// Tests
// $router->addRoute('GET', '/test-db', [$testDB, 'checkConnection']);
// $router->dispatch();

$router->dispatch();

?>