<?php

namespace App\Controller;

use App\Model\Book\Book;
use App\lib\Route;

require_once('src/lib/DatabaseConnection.php');
require_once('src/Models/Book.php');
require_once('src/lib/Route.php');

class BookController
{
  private Book $book;

  public function __construct()
  {
    $this->book = new Book();
  }

  #[Route('/books', 'GET')]
  public function getBooks(array $filters = []): void
  {
    $books = $this->book->getBooks($filters);
    $this->sendResponse(['error' => false, 'books' => $books, 'message' => 'ok']);
  }

  #[Route('/books/{id}', 'GET')]
  public function getBook(int $id): void
  {
    $book = $this->book->getBook($id);
    $this->sendResponse(['error' => false, 'book' => $book, 'message' => 'ok']);
  }

  #[Route('/books', 'POST')]
  public function addBook(
    int $editor_id,
    string $title,
    string $authors,
    string $isbn,
    ?string $cover,
    string $edited_at,
    ?string $plot,
    int $page_number
  ): void {
    $bookId = $this->book->addBook($editor_id, $title, $authors, $isbn, $cover, $edited_at, $plot, $page_number);
    if (!is_numeric($bookId) || $bookId <= 0) {
      throw new \Exception('Ajout du livre échoué');
    }
    $this->sendResponse(['error' => false, 'message' => 'Livre ajouté avec succès.', 'book_id' => $bookId]);
  }

  #[Route('/books/{id}', 'PUT')]
  public function updateBook(
    int $id,
    int $editor_id,
    string $title,
    string $authors,
    string $isbn,
    ?string $cover,
    string $edited_at,
    ?string $plot,
    int $page_number
  ): void {
    $affectedRows = $this->book->updateBook($id, $editor_id, $title, $authors, $isbn, $cover, $edited_at, $plot, $page_number);
    if ($affectedRows <= 0) {
      throw new \Exception('Mise à jour du livre échouée ou aucune modification effectuée');
    }
    $this->sendResponse(['error' => false, 'message' => 'Livre mis à jour avec succès.']);
  }

  #[Route('/books/{id}', 'DELETE')]
  public function deleteBook(int $id): void
  {
    $affectedRows = $this->book->deleteBook($id);
    if ($affectedRows <= 0) {
      throw new \Exception('Suppression du livre échouée ou livre introuvable');
    }
    $this->sendResponse(['error' => false, 'message' => 'Livre supprimé avec succès.']);
  }

  private function sendResponse(array $data): void
  {
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
  }
}
