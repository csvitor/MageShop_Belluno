<?php

class MageShop_Belluno_HashController extends MageShop_Belluno_Controller_AbstractController
{
  const URI = '/transaction/card_hash_key';
  protected $_helper;
  /**
   * Function to get html of terms and conditions
   */
  public function indexAction() {
    $this->_helper = Mage::Helper("belluno/data");
    $responseback = $this->doRequest();
    echo json_encode($responseback);
  }

  public function doRequest(){
    $environment = $this->_helper->getUrlEnvironment() . self::URI;
    $token = $this->_helper->getToken();
    
    $curl = curl_init();

    curl_setopt_array($curl, array(
      CURLOPT_URL => ($environment),
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 60,
      CURLOPT_FOLLOWLOCATION => TRUE,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_SSL_VERIFYHOST => false,
      CURLOPT_SSL_VERIFYPEER => false,
      CURLOPT_CUSTOMREQUEST => "GET",
      CURLOPT_HTTPHEADER => [
        "Content-Type:application/json",
        "Accept:application/json",
        "Authorization: Bearer $token",
      ],
    ));

    $response = curl_exec($curl); 

    curl_close($curl);

    return ($response);
  }
}