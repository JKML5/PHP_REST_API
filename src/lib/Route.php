<?php

namespace App\lib;

/**
 * Classe utilitaire pour définir les routes dans les annotations.
 */
#[\Attribute(\Attribute::TARGET_METHOD)]
class Route
{
  public function __construct(
    public string $path,
    public string $method = 'GET'
  ) {
  }
}
