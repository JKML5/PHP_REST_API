<?php

namespace Application\Controller;

use Application\Model\Editor\Editor;

require_once('src/lib/DatabaseConnection.php');
require_once('src/Models/Editor.php');

class EditorController
{
  private $editor;

  public function __construct()
  {
    $this->editor = new Editor();
  }

  public function getEditors()
  {
    $editors = $this->editor->getEditors();
    return $this->sendResponse(['error' => false, 'editors' => $editors, 'message' => 'ok']);
  }

  public function getEditor(int $id)
  {
    $editor = $this->editor->getEditor($id);
    return $this->sendResponse(['error' => false, 'editor' => $editor, 'message' => 'ok']);
  }

  public function addEditor(string $name)
  {
    $editorId = $this->editor->addEditor($name);
    if (!is_numeric($editorId) || $editorId <= 0) {
      throw new \Exception('Ajout de l\'éditeur échoué');
    }
    return $this->sendResponse(['error' => false, 'message' => 'Éditeur ajouté avec succès.', 'editor_id' => $editorId]);
  }

  public function updateEditor(int $id, string $name)
  {
    $affectedRows = $this->editor->updateEditor($id, $name);
    if ($affectedRows <= 0) {
      throw new \Exception('Mise à jour de l\'éditeur échouée ou aucune modification effectuée');
    }
    return $this->sendResponse(['error' => false, 'message' => 'Éditeur mis à jour avec succès.']);
  }

  public function deleteEditor(int $id)
  {
    $affectedRows = $this->editor->deleteEditor($id);
    if ($affectedRows <= 0) {
      throw new \Exception('Suppression de l\'éditeur échouée ou éditeur introuvable');
    }
    return $this->sendResponse(['error' => false, 'message' => 'Éditeur supprimé avec succès.']);
  }

  private function sendResponse(array $data): void
  {
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
  }
}
