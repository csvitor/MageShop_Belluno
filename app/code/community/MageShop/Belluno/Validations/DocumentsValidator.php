<?php

class MageShop_Belluno_Validations_DocumentsValidator {

  /**
   * Function to validate document
   * @param string $document
   * @return bool
   */
  public function validateDocument($document) {
    $document = preg_replace('/[^0-9]/is', '', $document);
    if (strlen($document) == 11) {
      return $this->validateDocumentCpf($document);
    } else if (strlen($document) == 14) {
      return $this->validateDocumentCnpj($document);
    } else {
      return false;
    }
  }

  /**
   * Function to validate document cpf
   * @param string $document
   * @return bool
   */
  protected function validateDocumentCpf($document) {
    if (preg_match('/(\d)\1{10}/', $document)) {
      return false;
    }
    for ($t = 9; $t < 11; $t++) {
      for ($d = 0, $c = 0; $c < $t; $c++) {
        $d += $document[$c] * (($t + 1) - $c);
      }
      $d = ((10 * $d) % 11) % 10;
      if ($document[$c] != $d) {
        return false;
      }
    }
    return true;
  }

  /**
   * Function to validate document cnpj
   * @param string $document
   * @return bool
   */   
  protected function validateDocumentCnpj($document) {
    if (preg_match('/(\d)\1{13}/', $document)) {
      return false;
    }

    for ($i = 0, $j = 5, $soma = 0; $i < 12; $i++) {
      $soma += $document[$i] * $j;
      $j = ($j == 2) ? 9 : $j - 1;
    }

    $resto = $soma % 11;

    if ($document[12] != ($resto < 2 ? 0 : 11 - $resto))
      return false;

    for ($i = 0, $j = 6, $soma = 0; $i < 13; $i++) {
      $soma += $document[$i] * $j;
      $j = ($j == 2) ? 9 : $j - 1;
    }

    $resto = $soma % 11;

    return $document[13] == ($resto < 2 ? 0 : 11 - $resto);
  }

}