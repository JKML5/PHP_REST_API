<?php

require_once 'src/Controllers/BookController.php';
require_once 'src/Controllers/EditorController.php';
require_once 'src/Lib/Router.php';

header('Content-Type: application/json; charset=UTF-8');

$router = new App\Lib\Router();
$router->registerController(App\Controller\BookController::class);
$router->registerController(App\Controller\EditorController::class);

$method = $_SERVER['REQUEST_METHOD'];
$request_uri = $router->getRequestPath();

try {
  $router->dispatch($method, $request_uri);
} catch (Exception $e) {
  echo json_encode(['error' => true, 'message' => $e->getMessage()]);
}
