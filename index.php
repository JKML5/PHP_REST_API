<?php

require_once('src/Controllers/BookController.php');

if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
  parse_str(file_get_contents("php://input"), $_PUT);
}

use Application\Controller\BookController;

header('Content-Type: application/json; charset=UTF-8');

try {
  $controller = new BookController();
  $dbConfig = require_once 'config/config.php';

  // Récupération des données de l'URL
  $method = $_SERVER['REQUEST_METHOD'];
  $request_uri = $_SERVER['REQUEST_URI'];
  $base_path = $dbConfig['app']['base_path'];

  $request_uri = strtok($request_uri, '?');
  $request_uri = str_replace($base_path, '', $request_uri);
  $segments = explode('/', $request_uri);
  $segments = array_filter($segments);

  $model = array_shift($segments);
  $id = array_shift($segments);

  if ($model !== 'books') {
    throw new Exception('Ressource non trouvée.', 404);
  }

  switch ($method) {
    case 'GET':
      if (empty($id)) {
        $filters = $_GET;
        $controller->getBooks($filters);
      } else {
        if (!is_numeric($id)) {
          throw new Exception("Paramètre id manquant ou invalide.");
        }
        $controller->getBook((int) $id);
      }
      break;

    case 'POST':
      if (
        !isset($_POST['editor_id']) ||
        !isset($_POST['title']) ||
        !isset($_POST['authors']) ||
        !isset($_POST['isbn']) ||
        !isset($_POST['edited_at']) ||
        !isset($_POST['page_number'])
      ) {
        throw new Exception("Paramètre manquant ou invalide.");
      }

      $cover = !empty($_POST['cover']) ? $_POST['cover'] : null;
      $plot = !empty($_POST['plot']) ? $_POST['plot'] : null;

      $controller->addBook(
        (int)$_POST['editor_id'],
        $_POST['title'],
        $_POST['authors'],
        $_POST['isbn'],
        $cover,
        $_POST['edited_at'],
        $plot,
        (int)$_POST['page_number']
      );
      break;

    case 'PUT':
      if (empty($id)) {
        throw new Exception('Identifiant manquant.', 400);
      }

      parse_str(file_get_contents("php://input"), $_PUT);

      if (
        !isset($_PUT['editor_id']) ||
        !isset($_PUT['title']) ||
        !isset($_PUT['authors']) ||
        !isset($_PUT['isbn']) ||
        !isset($_PUT['edited_at']) ||
        !isset($_PUT['page_number'])
      ) {
        throw new Exception("Paramètre manquant ou invalide.");
      }

      $cover = !empty($_PUT['cover']) ? $_PUT['cover'] : null;
      $plot = !empty($_PUT['plot']) ? $_PUT['plot'] : null;

      $controller->updateBook(
        $id,
        (int)$_PUT['editor_id'],
        $_PUT['title'],
        $_PUT['authors'],
        $_PUT['isbn'],
        $cover,
        $_PUT['edited_at'],
        $plot,
        (int)$_PUT['page_number']
      );
      break;

    case 'DELETE':
      if (empty($id)) {
        throw new Exception('Identifiant manquant.', 400);
      }
      $controller->deleteBook((int) $id);
      break;

    default:
      throw new Exception("URL ou méthode incorrecte.");
      break;
  }
} catch (Exception $e) {
  echo (json_encode(['error' => true, 'message' => $e->getMessage()]));
}
