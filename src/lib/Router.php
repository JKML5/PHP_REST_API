<?php

namespace App\Lib;

use ReflectionClass;
use ReflectionMethod;

/**
 * Gère le routage des requêtes via annotations.
 */
class Router
{
  private array $routes = [];

  /**
   * Enregistre un contrôleur et ses routes basées sur les annotations.
   *
   * @param string $controllerClass Le nom de la classe du contrôleur.
   */
  public function registerController(string $controllerClass): void
  {
    $controller = new $controllerClass();
    $reflection = new ReflectionClass($controller);

    foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
      $attributes = $method->getAttributes(Route::class);
      foreach ($attributes as $attribute) {
        $route = $attribute->newInstance();
        $this->addRoute($route->method, $route->path, [$controller, $method->getName()]);
      }
    }
  }

  /**
   * Ajoute une route au tableau des routes.
   *
   * @param string $method La méthode HTTP de la route (GET, POST, etc.).
   * @param string $path Le chemin de la route.
   * @param callable $handler Le gestionnaire de la route.
   */
  public function addRoute(string $method, string $path, callable $handler): void
  {
    $this->routes[$method][$path] = $handler;
  }

  /**
   * Dispatch la requête vers le gestionnaire approprié en fonction de la méthode HTTP et du chemin.
   *
   * @param string $method La méthode HTTP de la requête.
   * @param string $path Le chemin de la requête.
   */
  public function dispatch(string $method, string $path): void
  {
    if (isset($this->routes[$method][$path])) {
      call_user_func($this->routes[$method][$path], $_REQUEST);
    } else {
      header('Content-Type: application/json; charset=UTF-8');
      echo json_encode(['error' => true, 'message' => 'Ressource non trouvée.']);
    }
  }

  /**
   * Retourne et formate le chemin de la requête (ex: /books)
   *
   * @return string
   */
  public function getRequestPath(): string
  {
    $dbConfig = require_once 'config/config.php';

    $requestUri = strtok($_SERVER['REQUEST_URI'], '?');
    return str_replace($dbConfig['app']['base_path'], '', $requestUri);
  }
}
