<?php

class MageShop_Belluno_Model_Payment_Link_Transaction
{
  /**
   * Function transaction of method payment
   * @param Varien_Object $payment
   * @param $amount
   * @param $info
   */
  public function transactionLink(Varien_Object $payment, $amount, $info)
  {
    $createRequest = $this->getCreateRequest();
    $request = $createRequest->createRequest($payment);
    $connector = $this->getConnector();
    $response = $connector->doRequest($request, "POST", "/v2/transaction/create");
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
    
    $link = [
      'id' => $response['transaction']['transaction_id'],
      'code_valid_until' => $response['transaction']['code_valid_until'],
      'link' => $response['transaction']['link'],
      'link_code' => $response['transaction']['link_code'],
    ];
    $info->setAdditionalInformation("transaction_id", $response['transaction']['transaction_id']);
    $info->setAdditionalInformation("status", $response['transaction']['status']);
    $info->setAdditionalInformation("link_payment", json_encode($link));
  }

  /**Function to return class connector for requests */
  public function getConnector()
  {
    return new MageShop_Belluno_Service_ApiBelluno();
  }

  /**Function to return class create request */
  public function getCreateRequest()
  {
    return new MageShop_Belluno_Model_Payment_Link_CreateRequest();
  }
}
