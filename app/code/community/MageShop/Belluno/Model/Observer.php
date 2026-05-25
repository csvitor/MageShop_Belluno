<?php

class MageShop_Belluno_Model_Observer
{
    /**
     * Function to cancel order
     */
    public function _cancelOrder(Varien_Event_Observer $observer)
    {
        if ($observer->getOrder()->isCanceled()) {
            Mage::log( "Cancelamento Belluno Payment" , Zend_Log::DEBUG , 'mageshop_belluno_cancel.log', true);
            $order = $observer->getOrder();
            $payment = $order->getPayment();
            $method = $payment->getMethod();

            if ($method == 'belluno_creditcard') {
                if ($payment->getAdditionalInformation('belluno_refunded')) {
                    return;
                }
                $connector = $this->getConnector();
                $value = $payment->getAdditionalInformation('value');
                $transactionId = $payment->getAdditionalInformation('transaction_id');

                $request = [
                    'amount' => $value,
                    'reason' => '2'
                ];
                $request = json_encode($request);
                $connector->doRequest($request, "POST", "/transaction/$transactionId/refund");
                // Order save is in progress (sales_order_save_before); payment is
                // saved in cascade, so do not call $payment->save() here.
                $payment->setAdditionalInformation('belluno_refunded', true);
            }
        }
    }

    /**
     * Function to handle order refund
     */
    public function handleOrderRefund(Varien_Event_Observer $observer)
    {
        try {
            $creditmemo = $observer->getCreditmemo();
            $order = $creditmemo->getOrder();
            $payment = $order->getPayment();
            $method = $payment->getMethod();
            if ($method == 'belluno_creditcard') {
                if ($payment->getAdditionalInformation('belluno_refunded')) {
                    return;
                }
                $connector = $this->getConnector();
                $value = $creditmemo->getGrandTotal();
                $transactionId = $payment->getAdditionalInformation('transaction_id');
                Mage::helper("belluno")->log("Reembolso Belluno Payment: ID Transanction #". $transactionId, 'mageshop_belluno_refund.log');
                $request = [
                    'amount' => $value,
                    'reason' => '3'
                ];
                $request = json_encode($request);
                $connector->doRequest($request, "POST", "/transaction/$transactionId/refund");
                $payment->setAdditionalInformation('belluno_refunded', true);
                $payment->save();
            }
        } catch (\Throwable $th) {
            Mage::helper("belluno")->log( "Reembolso Belluno Payment: RETURN". $th->getMessage() ,'mageshop_belluno_refund.log');
        }
    }

    /**Function to return class connector for requests */
    public function getConnector()
    {
        return new MageShop_Belluno_Service_ApiBelluno();
    }
}