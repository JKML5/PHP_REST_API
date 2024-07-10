<?php

namespace Application\Model\Book;

use \DatabaseConnection;

require_once('src/lib/DatabaseConnection.php');

class Book
{
  public int $id;
  public int $editor_id;
  public string $title;
  public string $authors;
  public string $isbn;
  public ?string $cover;
  public string $edited_at;
  public ?string $plot;
  public int $page_number;

  private DatabaseConnection $connection;

  public function __construct()
  {
    $this->connection = new DatabaseConnection();
  }

  /**
   * Récupère une liste de livres en fonction des filtres donnés.
   * @param array $filters Tableau associatif des filtres (ex: ['title' => 'Some Title', 'authors' => 'Some Author'])
   * @return array Tableau d'objets Book représentant les livres trouvés.
   */
  public function getBooks(array $filters = []): array
  {
    $connection = $this->connection->getConnection();
    $query = 'SELECT * FROM book';
    $conditions = [];
    $params = [];
    $paramTypes = '';

    // Filtre par titre
    if (isset($filters['title'])) {
      $conditions[] = 'title = ?';
      $params[] = $filters['title'];
      $paramTypes .= 's';
    }

    // Filtre par auteurs
    if (isset($filters['authors'])) {
      $conditions[] = 'authors = ?';
      $params[] = $filters['authors'];
      $paramTypes .= 's';
    }

    // Filtre par ISBN
    if (isset($filters['isbn'])) {
      $conditions[] = 'isbn = ?';
      $params[] = $filters['isbn'];
      $paramTypes .= 's';
    }

    if ($conditions) {
      $query .= ' WHERE ' . implode(' AND ', $conditions);
    }

    // Tri
    if (isset($filters['sort'])) {
      $order = isset($filters['order']) && in_array(strtolower($filters['order']), ['asc', 'desc']) ? $filters['order'] : 'asc';
      $query .= ' ORDER BY ' . $filters['sort'] . ' ' . $order;
    }

    $statement = $connection->prepare($query);

    if ($params) {
      $statement->bind_param($paramTypes, ...$params);
    }

    if (!$statement->execute()) {
      throw new \Exception('Impossible de récupérer la liste des livres: ' . $statement->error);
    }

    $books = [];
    $result = $statement->get_result();

    while ($row = $result->fetch_assoc()) {
      $book = new Book();
      $book->id = $row['id'];
      $book->editor_id = $row['editor_id'];
      $book->title = $row['title'];
      $book->authors = $row['authors'];
      $book->isbn = $row['isbn'];
      $book->cover = $row['cover'] ?? null;
      $book->edited_at = $row['edited_at'];
      $book->plot = $row['plot'] ?? null;
      $book->page_number = $row['page_number'];

      $books[] = $book;
    }

    $statement->close();

    return $books;
  }

  /**
   * Récupère un livre en fonction de son ID.
   * @param int $id L'identifiant du livre.
   * @return Book L'objet Book correspondant.
   */
  public function getBook(int $id): Book
  {
    $statement = $this->connection->getConnection()->prepare('SELECT * FROM book WHERE id = ?');
    $statement->bind_param('i', $id);

    if (!$statement->execute()) {
      throw new \Exception('Aucun livre trouvé avec l\'ID fourni: ' . $statement->error);
    }

    $result = $statement->get_result();
    $row = $result->fetch_assoc();

    if (empty($row)) {
      throw new \Exception('Aucun livre trouvé avec l\'ID fourni: ' . $statement->error);
    }

    $book = new Book();
    $book->id = $row['id'];
    $book->editor_id = $row['editor_id'];
    $book->title = $row['title'];
    $book->authors = $row['authors'];
    $book->isbn = $row['isbn'];
    $book->cover = $row['cover'] ?? null;
    $book->edited_at = $row['edited_at'];
    $book->plot = $row['plot'] ?? null;
    $book->page_number = $row['page_number'];

    return $book;
  }

  /**
   * Ajoute un nouveau livre.
   * @param int $editor_id ID de l'éditeur.
   * @param string $title Titre du livre.
   * @param string $authors Auteurs du livre.
   * @param string $isbn ISBN du livre.
   * @param string|null $cover URL de la couverture du livre.
   * @param string $edited_at Date d'édition du livre.
   * @param string|null $plot Résumé du livre.
   * @param int $page_number Nombre de pages du livre.
   * @return int ID du livre ajouté.
   */
  public function addBook(int $editor_id, string $title, string $authors, string $isbn, ?string $cover, string $edited_at, ?string $plot, int $page_number): int
  {
    $connection = $this->connection->getConnection();
    $statement = $connection->prepare(
      'INSERT INTO book (editor_id, title, authors, isbn, cover, edited_at, plot, page_number) VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $statement->bind_param('issssssi', $editor_id, $title, $authors, $isbn, $cover, $edited_at, $plot, $page_number);

    if (!$statement->execute()) {
      throw new \Exception('Impossible d\'ajouter le livre: ' . $statement->error);
    }

    $bookId = $connection->insert_id;

    $statement->close();
    $connection->close();

    return $bookId;
  }

  /**
   * Met à jour les informations d'un livre.
   * @param int $id ID du livre.
   * @param int $editor_id ID de l'éditeur.
   * @param string $title Nouveau titre du livre.
   * @param string $authors Nouveaux auteurs du livre.
   * @param string $isbn Nouveau ISBN du livre.
   * @param string|null $cover Nouvelle URL de la couverture du livre.
   * @param string $edited_at Nouvelle date d'édition du livre.
   * @param string|null $plot Nouveau résumé du livre.
   * @param int $page_number Nouveau nombre de pages du livre.
   * @return int Nombre de lignes affectées par la mise à jour.
   */
  public function updateBook(int $id, int $editor_id, string $title, string $authors, string $isbn, ?string $cover, string $edited_at, ?string $plot, int $page_number): int
  {
    $connection = $this->connection->getConnection();
    $statement = $connection->prepare(
      "UPDATE book SET editor_id = ?, title = ?, authors = ?, isbn = ?, cover = ?, edited_at = ?, plot = ?, page_number = ? WHERE id = ?"
    );
    $statement->bind_param('issssssii', $editor_id, $title, $authors, $isbn, $cover, $edited_at, $plot, $page_number, $id);

    if (!$statement->execute()) {
      throw new \Exception('Impossible de mettre à jour les informations sur le livre: ' . $statement->error);
    }

    $affected_rows = $connection->affected_rows;
    $connection->close();

    return $affected_rows;
  }

  /**
   * Supprime un livre.
   * @param int $id ID du livre.
   * @return int Nombre de lignes affectées par la suppression.
   */
  public function deleteBook(int $id): int
  {
    $connection = $this->connection->getConnection();
    $statement = $connection->prepare('DELETE FROM book WHERE id = ?');
    $statement->bind_param('i', $id);

    if (!$statement->execute()) {
      throw new \Exception('Impossible de supprimer le livre: ' . $statement->error);
    }

    $affected_rows = $statement->affected_rows;

    $statement->close();
    $connection->close();

    return $affected_rows;
  }
}
