<?php
require_once __DIR__ . '/../core/Router.php';
require_once __DIR__ . '/../res/PasswordResource.php';
require_once __DIR__ . '/../res/UrlResource.php';

//test nomas BORRAR DESPUES
// require_once __DIR__ . '/../res/TestDB.php';
// $testDB = new TestDB();
// BORRAR

$router = new Router('', '/api'); // se puede pooiner el v1 pero por lo que pide el pdf no lo pongo
$passController = new PasswordResource();
$urlController = new UrlController();

// exa 1
$router->addRoute('GET', '/password', [$passController, 'generateSingle']);
$router->addRoute('POST', '/passwords', [$passController, 'generateMultiple']);
$router->addRoute('POST', '/password/validate', [$passController, 'validate']);

// exa 3


// exa 2, hasta abajo por la ultima ruta
$router->addRoute('POST', '/url/shorten', [$urlController, 'shorten']);
$router->addRoute('GET', '/url/stats/{code}', [$urlController, 'stats']);
$router->addRoute('GET', '/{code}', [$urlController, 'redirect']);

// Tests BORRARR
// $router->addRoute('GET', '/test-db', [$testDB, 'checkConnection']);
// $router->dispatch();
// BORRAR

$router->dispatch();

?>