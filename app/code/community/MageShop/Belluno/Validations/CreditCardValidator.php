<?php

class MageShop_Belluno_Validations_CreditCardValidator
{

  protected $cards = [
    'visa' => [
      'type' => 'visa',
      'pattern' => '/^4[0-9]{12}(?:[0-9]{3})?$/',
      'length' => array(13, 16),
      'cvcLength' => array(3),
      'luhn' => true
    ],
    'mastercard' => [
      'type' => 'mastercard',
      'pattern' => '/^(603136|603689|608619|606200|603326|605919|608783|607998|603690|604891|603600|603134|608718|603680|608710|604998)|(5[1-5][0-9]{14}|2221[0-9]{12}|222[2-9][0-9]{12}|22[3-9][0-9]{13}|2[3-6][0-9]{14}|27[01][0-9]{13}|2720[0-9]{12})$/',
      'length' => array(16),
      'cvcLength' => array(3),
      'luhn' => true
    ],
    'elo' => [
      'type' => 'elo',
      'pattern' => '/^(401178|401179|431274|438935|451416|457393|457631|457632|504175|627780|636297|636368|(506699|5067[0-6]\d|50677[0-8])|(50900\d|5090[1-9]\d|509[1-9]\d{2})|65003[1-3]|(65003[5-9]|65004\d|65005[0-1])|(65040[5-9]|6504[1-3]\d)|(65048[5-9]|65049\d|6505[0-2]\d|65053[0-8])|(65054[1-9]|6505[5-8]\d|65059[0-8])|(65070\d|65071[0-8])|65072[0-7]|(65090[1-9]|65091\d|650920)|(65165[2-9]|6516[6-7]\d)|(65500\d|65501\d)|(65502[1-9]|6550[3-4]\d|65505[0-8]))[0-9]{10,12}/',
      'length' => array(16),
      'cvcLength' => array(3),
      'luhn' => true
    ],
    'hipercard' => [
      'type' => 'hipercard',
      'pattern' => '/^(38[0-9]{17}|60[0-9]{14})$/',
      'length' => array(13, 16, 19),
      'cvcLength' => array(3),
      'luhn' => true
    ],
    'hiper' => [
      'type' => 'hiper',
      'pattern' => '/^(6370950000000005|637095|637609|637599|637612|637568|63737423|63743358)/',
      'length' => array(12, 13, 14, 15, 16, 17, 18, 19),
      'cvcLength' => array(3),
      'luhn' => true
    ],
    'cabal' => [
      'type' => 'cabal',
      'pattern' => '/^(60420[1-9]|6042[1-9][0-9]|6043[0-9]{2}|604400)/',
      'length' => array(16),
      'cvcLength' => array(3),
      'luhn' => true
    ],
    'amex' => [
          'type' => 'amex',
          'pattern' => '/(\d{1,4})(\d{1,6})?(\d{1,5})?/',
          'length' => array(15, 16),
          'cvcLength' => array(3),
          'luhn' => true
    ],
  ];

  /**
   * Function to validate credit card
   * @param string $number
   * @param string $type
   * @return array
   */
  public function validateCreditCard($number, $type = null)
  {
    $returnDefault = [
      'valid' => false,
      'number' => '',
      'type' => ''
    ];

    if (empty($type)) {
      $type = $this->creditCardType($number);
    }

    $this->validCard($number, $type);

    if (array_key_exists($type, $this->cards) && $this->validCard($number, $type)) {
      if ($this->luhnCheck($number)) {
        return [
          'valid' => true,
          'number' => $number,
          'type' => $type
        ];
      }
    }

    return $returnDefault;
  }

  /**
   * Function to get card type
   * @param string $number
   * @return string $type
   */
  public function getCardTypeBelluno($number)
  {
    $arrayTypes = [
      '1' => 'mastercard',
      '2' => 'visa',
      '3' => 'elo',
      '4' => 'hipercard',
      '5' => 'cabal',
      '6' => 'hiper',
      '7' => 'amex',
    ];

    $res = $this->validateCreditCard($number);
    $type = $res['type'];
    $resultTypeCard = '';

    foreach ($arrayTypes as $key => $value) {
      if ($type == $value) {
        $resultTypeCard = $key;
      }
    }

    return $resultTypeCard;
  }

  //Internal functions

  /**
   * Function to validate credit card (luhncheck)
   * @param $cardNumber
   * @return bool
   */
  protected function luhnCheck($cardNumber)
  {
    $cardNumber = preg_replace('/\D/', '', $cardNumber);
    $numberLenght = strlen($cardNumber);
    $parity = $numberLenght % 2;

    $total = 0;
    for ($i = 0; $i < $numberLenght; $i++) {
      $digit = $cardNumber[$i];
      if ($i % 2 == $parity) {
        $digit *= 2;

        if ($digit > 9) {
          $digit -= 9;
        }
      }
      $total += $digit;
    }

    return ($total % 10 == 0) ? true : false;
  }

  /**
   * Function to get credit card type
   * @param string $number
   * @return string
   */
  protected function creditCardType($number)
  {
    foreach ($this->cards as $type => $card) {
      if (preg_match($card['pattern'], $number)) {
        return $type;
      }
    }
    return '';
  }

  /**
   * Function to validate credit card
   * @param string $number
   * @param string $type
   * @return bool
   */
  protected function validCard($number, $type)
  {
    return ($this->validPattern($number, $type) && $this->validLength($number, $type));
  }

  /**
   * Function to validate brand pattern
   * @param string $number
   * @param string $type
   * @return bool
   */
  protected function validPattern($number, $type)
  {
    return preg_match($this->cards[$type]['pattern'], $number);
  }

  /**
   * Function to validate length number of card
   * @param string $number
   * @param string $type
   * @return bool
   */
  protected function validLength($number, $type)
  {
    foreach ($this->cards[$type]['length'] as $length) {
      if (strlen($number) == $length) {
        return true;
      }
    }
    return false;
  }
}
