<?php

class MageShop_Belluno_Model_Payment_Creditcard_Refund
{
  /**
   * Function refund (credit memo online) of method payment
   * @param Varien_Object $payment
   */
  public function refund(Varien_Object $payment)
  {
    $method = $payment->getMethod();
    $transactionId = $payment->getAdditionalInformation('transaction_id');
    $value = $payment->getAdditionalInformation('value');

    if ($method == 'belluno_creditcard') {
      $request = [
        'amount' => $value,
        'reason' => '2'
      ];

      $request = json_encode($request);
      $connector = $this->getConnector();
      $connector->doRequest($request, "POST", "/transaction/$transactionId/refund");
    }
  }

  /**Function to return class connector for requests */
  public function getConnector()
  {
    return new MageShop_Belluno_Service_ApiBelluno();
  }
}
