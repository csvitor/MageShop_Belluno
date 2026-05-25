<?php 

class MageShop_Belluno_Controller_AbstractController extends Mage_Core_Controller_Front_Action{

    const BL_STATUS_PAID = "Paid";
    const BL_STATUS_EXPIRED = "Expired";
    const BL_STATUS_INACTIVATED = "Inactivated";
    const BL_STATUS_CANCELLED = "Cancelled";
    const BL_STATUS_REFUSED = "Refused";
    const BL_STATUS_BL_CL_BY_DEADLINE = "Closure by deadline";
    const BL_STATUS_BL_CL_BY_REQUEST= "Closure by request";
    const BL_STATUS_BL_CL_REQUEST = "Closure requested";
    const BL_STATUS_CC_ANALYSIS = 'Manual Analysis';
    const BL_STATUS_CC_CLIENT_ANALYSIS = 'Client Manual Analysis';
    const BL_STATUS_CC_EXPIRED_USER_ANALYSIS = 'Expired User Analysis';
    protected $comment = '';

    protected function setReason($comment)
    {
        $this->comment = $comment;
    }
    protected function getReason(){
        return $this->comment;
    }

    protected function _paid($order, $statusBelluno){
        $this->_release($order);
        // Check if the order can be invoiced
        if(!$order->canInvoice()) {
            return true;
        }
        // Atualize o campo setAdditionalInformation do pedido
        $payment = $order->getPayment();
        $additionalInformation = $payment->getAdditionalInformation();
        $additionalInformation['status'] = $statusBelluno;
        $payment->setAdditionalInformation($additionalInformation);
        $commentInvoice = 'Gateway Belluno Digital: ' . Mage::getModel('core/date')->gmtDate('Y-m-d H:i:s') . ' | '. $this->getReason();
        if(empty($this->getReason())){
            $this->setReason($commentInvoice);
        }
        
        // Create the invoice
        $invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice();
        $invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_OFFLINE);
        $invoice->register();
        $invoice->getOrder()->setIsInProcess(true);
        $transactionSave = Mage::getModel('core/resource_transaction')
            ->addObject($invoice)
            ->addObject($invoice->getOrder());
        $invoice->addComment( (string) $this->getReason() , true, true);
        $invoice->save();
        $transactionSave->save();
        // Enviar o email da fatura
        $invoice->sendEmail();
        $invoice->setEmailSent(true);
        $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true);
        $order->setStatus(Mage_Sales_Model_Order::STATE_PROCESSING, true);
        $order->addStatusHistoryComment( (string) $this->getReason() , false);
        $order->save();
    }

    protected function _holded($order, $statusBelluno)
    {
        if ($order->getStatus() == Mage_Sales_Model_Order::STATE_HOLDED) {
            return $this;
        }
        // Atualize o campo setAdditionalInformation do pedido
        $payment = $order->getPayment();
        $additionalInformation = $payment->getAdditionalInformation();
        $additionalInformation['status'] = $statusBelluno;
        $payment->setAdditionalInformation($additionalInformation);

        $order->setState(Mage_Sales_Model_Order::STATE_HOLDED, true);
        $order->setStatus(Mage_Sales_Model_Order::STATE_HOLDED);
        $order->addStatusHistoryComment($this->getReason(), false);
        $order->save();
    }

    protected function _review($order, $statusBelluno){
        if ($order->getState() == Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW) {
           return $this;
        }
        // Atualize o campo setAdditionalInformation do pedido
        $payment = $order->getPayment();
        $additionalInformation = $payment->getAdditionalInformation();
        $additionalInformation['status'] = $statusBelluno;
        $payment->setAdditionalInformation($additionalInformation);

        $order->setState(Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW, true);
        $order->setStatus(Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW);
        $order->addStatusHistoryComment($this->getReason(), false);
        $order->save();
    }

    protected function _cancelled($order, $statusBelluno){
        $this->_release($order);
        $comment = 'Gateway Belluno Digital: ' . Mage::getModel('core/date')->gmtDate('Y-m-d H:i:s') . ' | ' . $this->getReason();
        if (!$order->canCancel()) {
            return true;
        }
       // Atualize o campo setAdditionalInformation do pedido
        $payment = $order->getPayment();
        $additionalInformation = $payment->getAdditionalInformation();
        $additionalInformation['status'] = $statusBelluno;
        $payment->setAdditionalInformation($additionalInformation);
        $order->cancel();
        $order->setStatus(Mage_Sales_Model_Order::STATE_CANCELED, true);
        $history = $order->addStatusHistoryComment($comment, true);
        $history->setIsCustomerNotified(true); 
        $order->save();
    }

    protected function _refund($order, $statusBelluno){
        $this->_release($order);
        if (!$order->canCreditmemo()) {
            return true;
        }
        // Atualize o campo setAdditionalInformation do pedido
        $payment = $order->getPayment();
        $additionalInformation = $payment->getAdditionalInformation();
        $additionalInformation['status'] = $statusBelluno;
        $payment->setAdditionalInformation($additionalInformation);

        $service = Mage::getModel('sales/service_order', $order);
        $creditmemo = $service->prepareCreditmemo();
        $creditmemo->setRefundRequested(true);
        $creditmemo->setOfflineRequested(true);
        $creditmemo->register();
        $creditmemo->save();

        $order->addStatusHistoryComment($this->getReason(), true);
        $order->save();
    }

    protected function payments($postback)
    {
        if(isset($postback['transaction']) && count($postback['transaction']) > 0){
           $payments = $postback['transaction']['payments'];
           if(isset($payments[0]) && count($payments[0]) > 0){
                switch ($payments[0]['type']) {
                    case 'pix':
                        $this->transactionPix($payments[0]);
                    break;
                    case 'card':
                        $this->transactionCard($payments[0]);
                    break;
                }
           }
        }else if(isset($postback['bankslip']) && count($postback['bankslip']) > 0){
            if(isset($postback['bankslip']['payment']) && count($postback['bankslip']['payment']) > 0){
                $this->bankslip($postback['bankslip']['payment']);
            }
        }
    }

    private function transactionCard($payment)
    {
        $message = "Informamos que o pagamento foi concluído com sucesso. Segue abaixo os detalhes do pagamento:\n\n";
        $message .= "Cartão: " . $payment['card'] . "<br>";
        $message .= "Bandeira: " . $payment['brand'] . "<br>";
        $message .= "Número de parcelas: " . $payment['installments_number'] . "<br>";
        $message .= "Código de autorização: " . $payment['cod_aut'] . "<br>";
        $message .= "NSU do pagamento: " . $payment['nsu_payment'] . "<br>";
        $message .= "Data de pagamento: " . $payment['paid_at'] . "<br>";
        $this->setReason($message);
    }

    private function transactionPix($payment)
    {
        $message = "Informamos que o pagamento foi concluído com sucesso. Segue abaixo os detalhes do pagamento:<br>";
        $message .= "Tipo de pagamento: " . $payment['type'] . "<br>";
        $message .= "Código: " . $payment['pix_code'] . "<br>";
        $message .= "Valor pago: R$ " . number_format($payment['value'], 2, ',', '.') . "<br>";
        $message .= "Taxa: R$ " . number_format($payment['fee'], 2, ',', '.') . "<br>";
        $message .= "Valor líquido: R$ " . number_format($payment['net_value'], 2, ',', '.') . "<br>";
        $message .= "Beneficiário: " . $payment['payee']['name'] . " - " . $payment['payee']['document'] . "<br>";
        $message .= "Data de pagamento: " . date('d/m/Y \à\s H:i:s', strtotime($payment['paid_at'])) . "<br>";
        $this->setReason($message);
    }

    private function bankslip($payment)
    {
        $message = "Informamos que o pagamento foi concluído com sucesso. Segue abaixo os detalhes do pagamento:<br>";
        $message .= "Data do pagamento: " . date('d/m/Y', strtotime($payment['payment_date'])) . "<br>";
        $message .= "Data de transferência: " . date('d/m/Y', strtotime($payment['transfer_date'])) . "<br>";
        $message .= "Lote de transferência: " . $payment['transfer_batch'] . "<br>";
        $message .= "Valor do pagamento: R$ " . number_format($payment['value'], 2, ',', '.') . "\n";
        $message .= "Juros: R$ " . number_format($payment['interest'], 2, ',', '.') . "<br>";
        $message .= "Multa: R$ " . number_format($payment['fine'], 2, ',', '.') . "<br>";
        $message .= "Desconto: R$ " . number_format($payment['discount'], 2, ',', '.') . "<br>";
        $message .= "Valor pago: R$ " . number_format($payment['paid_value'], 2, ',', '.') . "<br>";
        $message .= "Taxa de boleto: R$ " . number_format($payment['bankslip_fee'], 2, ',', '.') . "<br>";
        $message .= "Valor líquido: R$ " . number_format($payment['net_value'], 2, ',', '.') . "<br>";
        $this->setReason($message);
    }

    public function _release($order)
    {
        if($this->_toHold($order)){
            if ($order->canUnhold()) {
                $order->unhold();
            }
        }
       
    }

    public function _toHold($order)
    {
        if ($order->getState() == Mage_Sales_Model_Order::STATE_HOLDED || $order->getState() == Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW){
            return true;
        }
        return false;
    }
}
