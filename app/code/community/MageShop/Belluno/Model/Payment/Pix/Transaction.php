<?php

class MageShop_Belluno_Model_Payment_Pix_Transaction
{
  /**
   * Function transaction of method payment
   * @param Varien_Object $payment
   * @param $amount
   * @param $info
   */
  public function transactionPix(Varien_Object $payment, $amount, $info)
  {
    $createRequest = $this->getCreateRequest();
    $data = json_decode($payment->getAdditionalInformation("data"), true);
    $request = $createRequest->createRequest($data, $info);
    $connector = $this->getConnector();
    $response = $connector->doRequest($request, "POST", "/v2/transaction/pix");
    $response = json_decode($response, true);

    $error =  "Algo não ocorreu bem. Por favor verifique suas informações ou altere a forma de pagamento.";
    if(isset($response['message'])){
        Mage::throwException($error);
    }elseif(isset($response['errors'])){
        $resError = current($response['errors']);
        if(gettype($resError) == 'array'){
            $error = current($resError);
        }
        Mage::throwException($error);
    }
    
    $pix = [
      'id' => $response['transaction']['transaction_id'],
      'pix' => $response['transaction']['pix'],
      'url' => $response['transaction']['url'],
      'link' => $response['transaction']['link'],
      'link_code' => $response['transaction']['link_code'],
    ];
    $info->setAdditionalInformation("status", $response['transaction']['status']);
    $info->setAdditionalInformation("pix", json_encode($pix));
  }

  /**Function to return class connector for requests */
  public function getConnector()
  {
    return new MageShop_Belluno_Service_ApiBelluno();
  }

  /**Function to return class create request */
  public function getCreateRequest()
  {
    return new MageShop_Belluno_Model_Payment_Pix_CreateRequest();
  }
}
