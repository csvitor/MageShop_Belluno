<?php
class MageShop_Belluno_Model_Payment_Bankslip_Transaction
{
  /**
   * Function authorize of method payment
   * @param Varien_Object $payment
   * @param $amount
   * @param $info
   */
  public function transactionBankslip(Varien_Object $payment, $amount, $info)
  {
    $createRequest = $this->getCreateRequest();
    $data = json_decode($payment->getAdditionalInformation("data"), true);
    $request = $createRequest->createRequest($data, $info);

    $connector = $this->getConnector();
    
    $response = $connector->doRequest($request, "POST", "/bankslip");
    $response = json_decode($response, true);

    $error =  "Algo não ocorreu bem. Por favor verifique suas informações ou altere a forma de pagamento.";
    if(isset($response['message'])){
        Mage::throwException($error);
    }elseif(isset($response['errors'])){
        Mage::throwException($error);
    }
    $bankslip = [
      'id' => $response['bankslip']['id'],
      'due' => $response['bankslip']['due'],
      'quote_id' => $response['bankslip']['document_code'],
      'url' => $response['bankslip']['url'],
      'digitable_line' => $response['bankslip']['digitable_line']
    ];
    $info->setAdditionalInformation("status", $response['bankslip']['status']);
    $info->setAdditionalInformation("bankslip", json_encode($bankslip));
  }

  /**Function to return class connector for requests */
  public function getConnector()
  {
    return new MageShop_Belluno_Service_ApiBelluno();
  }

  /**Function to return class create request */
  public function getCreateRequest()
  {
    return new MageShop_Belluno_Model_Payment_Bankslip_CreateRequest();
  }
}
