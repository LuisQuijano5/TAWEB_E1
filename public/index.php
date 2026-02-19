<?php
require_once __DIR__ . '/../core/Router.php';
require_once __DIR__ . '/../res/PasswordResource.php';

//test nomas BORRAR DESPUES
require_once __DIR__ . '/../res/TestDB.php';
$testDB = new TestDB();
// BORRAR

$router = new Router('', '/api'); // se puede pooiner el v1 pero por lo que pide el pdf no lo pongo
$controller = new PasswordResource();

// exa 1
$router->addRoute('GET', '/password', [$controller, 'generateSingle']);
$router->addRoute('POST', '/passwords', [$controller, 'generateMultiple']);
$router->addRoute('POST', '/password/validate', [$controller, 'validate']);

// exa 2

// exa 3

// Tests BORRARR
$router->addRoute('GET', '/test-db', [$testDB, 'checkConnection']);
$router->dispatch();
// BORRAR

$router->dispatch();

?>