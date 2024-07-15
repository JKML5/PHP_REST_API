<?php

namespace App\Model\Editor;

use \DatabaseConnection;

require_once('src/lib/DatabaseConnection.php');

class Editor
{
  public int $id;
  public string $name;
  private DatabaseConnection $connection;

  public function __construct()
  {
    $this->connection = new DatabaseConnection();
  }

  /**
   * Récupère une liste d'éditeurs
   * @return array Tableau d'éditeurs
   */
  public function getEditors(): array
  {
    $connection = $this->connection->getConnection();
    $query = 'SELECT * FROM editor';
    $statement = $connection->prepare($query);

    if (!$statement->execute()) {
      throw new \Exception('Impossible de récupérer la liste des éditeurs: ' . $statement->error);
    }

    $editors = [];
    $result = $statement->get_result();

    while ($row = $result->fetch_assoc()) {
      $editor = new Editor();
      $editor->id = $row['id'];
      $editor->name = $row['name'];
      $editors[] = $editor;
    }

    $statement->close();

    return $editors;
  }

  /**
   * Récupère un éditeur en fonction de son ID.
   * @param int $id L'identifiant de l'éditeur
   * @return Editor L'éditeur correspondant.
   */
  public function getEditor(int $id): Editor
  {
    $statement = $this->connection->getConnection()->prepare('SELECT * FROM editor WHERE id = ?');
    $statement->bind_param('i', $id);

    if (!$statement->execute()) {
      throw new \Exception('Aucun éditeur trouvé avec l\'ID fourni: ' . $statement->error);
    }

    $result = $statement->get_result();
    $row = $result->fetch_assoc();

    if (empty($row)) {
      throw new \Exception('Aucun éditeur trouvé avec l\'ID fourni.');
    }

    $editor = new Editor();
    $editor->id = $row['id'];
    $editor->name = $row['name'];

    return $editor;
  }

  /**
   * Ajoute un nouvel éditeur.
   * @param string $name nom de l'éditeur
   * @return int ID de l'éditeur créé.
   */
  public function addEditor(string $name): int
  {
    $connection = $this->connection->getConnection();
    $statement = $connection->prepare('INSERT INTO editor (name) VALUES (?)');
    $statement->bind_param('s', $name);

    if (!$statement->execute()) {
      throw new \Exception('Impossible d\'ajouter l\'éditeur: ' . $statement->error);
    }

    $editorId = $connection->insert_id;

    $statement->close();
    $connection->close();

    return $editorId;
  }

  /**
   * Met à jour les informations d'un éditeur.
   * @param int $id ID de l'éditeur.
   * @param string $name Nouveau nom de l'éditeur.
   * @return int Nombre de lignes affectées par la mise à jour.
   */
  public function updateEditor(int $id, string $name): int
  {
    $connection = $this->connection->getConnection();
    $statement = $connection->prepare('UPDATE editor SET name = ? WHERE id = ?');
    $statement->bind_param('si', $name, $id);

    if (!$statement->execute()) {
      throw new \Exception('Impossible de mettre à jour l\'éditeur: ' . $statement->error);
    }

    $affected_rows = $connection->affected_rows;
    $connection->close();

    return $affected_rows;
  }

  /**
   * Supprime un éditeur.
   * @param int $id ID de l'éditeur.
   * @return int Nombre de lignes affectées par la suppression.
   */
  public function deleteEditor(int $id): int
  {
    $connection = $this->connection->getConnection();
    $statement = $connection->prepare('DELETE FROM editor WHERE id = ?');
    $statement->bind_param('i', $id);

    if (!$statement->execute()) {
      throw new \Exception('Impossible de supprimer l\'éditeur: ' . $statement->error);
    }

    $affected_rows = $statement->affected_rows;

    $statement->close();
    $connection->close();

    return $affected_rows;
  }
}
