<?php

namespace Application\Controller;

use Application\Model\Book\Book;

require_once('src/lib/DatabaseConnection.php');
require_once('src/Models/Book.php');

class BookController
{
  private $book;

  public function __construct()
  {
    $this->book = new Book();
  }

  public function getBooks(array $filters = [])
  {
    $books = $this->book->getBooks($filters);
    return $this->sendResponse(['error' => false, 'books' => $books, 'message' => 'ok']);
  }

  public function getBook(int $id)
  {
    $book = $this->book->getBook($id);
    return $this->sendResponse(['error' => false, 'book' => $book, 'message' => 'ok']);
  }

  public function addBook(int $editor_id, string $title, string $authors, string $isbn, ?string $cover, string $edited_at, ?string $plot, int $page_number)
  {
    $bookId = $this->book->addBook($editor_id, $title, $authors, $isbn, $cover, $edited_at, $plot, $page_number);
    if (!is_numeric($bookId) || $bookId <= 0) {
      throw new \Exception('Ajout du livre échoué');
    }
    return $this->sendResponse(['error' => false, 'message' => 'Livre ajouté avec succès.', 'book_id' => $bookId]);
  }

  public function updateBook(int $id, int $editor_id, string $title, string $authors, string $isbn, ?string $cover, string $edited_at, ?string $plot, int $page_number)
  {
    $affectedRows = $this->book->updateBook($id, $editor_id, $title, $authors, $isbn, $cover, $edited_at, $plot, $page_number);
    if ($affectedRows <= 0) {
      throw new \Exception('Mise à jour du livre échouée ou aucune modification effectuée');
    }
    return $this->sendResponse(['error' => false, 'message' => 'Livre mis à jour avec succès.']);
  }

  public function deleteBook(int $id)
  {
    $affectedRows = $this->book->deleteBook($id);
    if ($affectedRows <= 0) {
      throw new \Exception('Suppression du livre échouée ou livre introuvable');
    }
    return $this->sendResponse(['error' => false, 'message' => 'Livre supprimé avec succès.']);
  }

  private function sendResponse(array $data): void
  {
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
  }
}
