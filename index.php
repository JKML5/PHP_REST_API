<?php

require_once('src/Controllers/BookController.php');
require_once('src/Controllers/EditorController.php');

if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
  parse_str(file_get_contents("php://input"), $_PUT);
}

use Application\Controller\BookController;
use Application\Controller\EditorController;

header('Content-Type: application/json; charset=UTF-8');

try {
  $bookController = new BookController();
  $editorController = new EditorController();

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

  if ($model !== 'books' && $model !== 'editors') {
    throw new Exception('Ressource non trouvée.', 404);
  }

  switch ($model) {
    case 'books':
      switch ($method) {
        case 'GET':
          if (empty($id)) {
            $filters = $_GET;
            $bookController->getBooks($filters);
          } else {
            if (!is_numeric($id)) {
              throw new Exception("Paramètre id manquant ou invalide.");
            }
            $bookController->getBook((int) $id);
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

          $cover = $_POST['cover'] ?? null;
          $plot = $_POST['plot'] ?? null;

          $bookController->addBook(
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

          $cover = $_PUT['cover'] ?? null;
          $plot = $_PUT['plot'] ?? null;

          $bookController->updateBook(
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
          $bookController->deleteBook((int) $id);
          break;

        default:
          throw new Exception("URL ou méthode incorrecte.");
          break;
      }
      break;

    case 'editors':
      switch ($method) {
        case 'GET':
          if (empty($id)) {
            $editorController->getEditors();
          } else {
            if (!is_numeric($id)) {
              throw new Exception("Paramètre id manquant ou invalide.");
            }
            $editorController->getEditor((int)$id);
          }
          break;

        case 'POST':
          if (!isset($_POST['name'])) {
            throw new Exception("Paramètre manquant ou invalide.");
          }
          $editorController->addEditor($_POST['name']);
          break;

        case 'PUT':
          if (empty($id)) {
            throw new Exception('Identifiant manquant.', 400);
          }

          parse_str(file_get_contents("php://input"), $_PUT);

          if (!isset($_PUT['name'])) {
            throw new Exception("Paramètre manquant ou invalide.");
          }

          $editorController->updateEditor($id, $_PUT['name']);
          break;

        case 'DELETE':
          if (empty($id)) {
            throw new Exception('Identifiant manquant.', 400);
          }
          $editorController->deleteEditor((int)$id);
          break;

        default:
          throw new Exception("URL ou méthode incorrecte.");
          break;
      }
      break;

    default:
      throw new Exception('Ressource non trouvée.', 404);
      break;
  }
} catch (Exception $e) {
  echo (json_encode(['error' => true, 'message' => $e->getMessage()]));
}
