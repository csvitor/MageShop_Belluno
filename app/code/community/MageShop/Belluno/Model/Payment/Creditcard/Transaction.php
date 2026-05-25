<?php

class MageShop_Belluno_Model_Payment_Creditcard_Transaction
{

    /**
     * Function authorize of method payment
     * @param Varien_Object $payment
     * @param $amount
     * @param $info
     */
    public function transactionCreditCard(Varien_Object $payment, $amount, $info)
    {
        $createRequest = $this->getCreateRequest();
        $data = json_decode($payment->getAdditionalInformation("data"), true);
        $request = $createRequest->createRequest($data, $info);

        $connector = $this->getConnector();

        $response = $connector->doRequest($request, "POST", "/transaction");
        $response = json_decode($response, true);
        $error =  "Algo não ocorreu bem. Por favor verifique suas informações.";
        if(isset($response['message'])){
            Mage::throwException($error);
        }elseif(isset($response['errors'])){
            $resError = current($response['errors']);
            if(gettype($resError) == 'array'){
                $error = current($resError);
            }
            Mage::throwException($error);
        }
        
        $info->setAdditionalInformation("transaction_id", $response['transaction']['transaction_id']);
        $info->setAdditionalInformation("value", $response['transaction']['value']);
        $info->setAdditionalInformation("status", $response['transaction']['status']);
        if(isset($response['transaction']['status_code'])){
            $info->setAdditionalInformation("status_code", $response['transaction']['status_code']);
        }
        if(isset($response['transaction']['link'])){
            $info->setAdditionalInformation("link", $response['transaction']['link']);
        }
        $info->setAdditionalInformation("resBelluno", $connector->getMessege());
    }

    /**Function to return class connector for requests */
    public function getConnector()
    {
        return new MageShop_Belluno_Service_ApiBelluno();
    }

    /**Function to return class create request */
    public function getCreateRequest()
    {
        return new MageShop_Belluno_Model_Payment_Creditcard_CreateRequest();
    }
}
