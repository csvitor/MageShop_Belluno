<?php

class MageShop_Belluno_Validations_Encrypt {

  /**
   * Function to generate card hash
   * @param string $cardNumber
   * @param string $cardExpMonth
   * @param string $cardExpYear
   * @param string $cardCvv
   * @return string
   */
  public function encrypt($cardNumber, $cardExpMonth, $cardExpYear, $cardCvv) {
    (strlen($cardExpMonth) == 1) ? $cardExpMonth = "0$cardExpMonth" : $cardExpMonth = $cardExpMonth;
    $queryString = "card_number=$cardNumber&card_expiration_date=$cardExpMonth$cardExpYear&card_cvv=$cardCvv";
    
    $response = $this->getPublicKey();
    $publicKey = $response['rsa_public_key'];
    $id = $response['id'];
    
    $result = '';
    $rsa = new phpseclib\Crypt\RSA;
    $rsa->loadKey($publicKey);
    $rsa->setEncryptionMode(phpseclib\Crypt\RSA::ENCRYPTION_PKCS1);
    $result = $rsa->encrypt($queryString);
    $result = base64_encode($result);

    return $id . '_' . $result;
  }

  /**
   * Function to get public key
   * @return array
   */
  public function getPublicKey() {
    $connector = new MageShop_Belluno_Service_Connector();
    $response = $connector->doRequest("0", "GET", "/transaction/card_hash_key");
    $response = json_decode($response, true);
    return $response;
  }

}