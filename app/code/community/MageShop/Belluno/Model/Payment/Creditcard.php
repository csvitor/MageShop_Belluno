<?php
class MageShop_Belluno_Model_Payment_Creditcard extends Mage_Payment_Model_Method_Abstract
{
    const PAY_CODE = 'belluno_creditcard';
    
    protected $_code = self::PAY_CODE;
    protected $_formBlockType = 'belluno/form_creditcard';
    protected $_infoBlockType = 'belluno/info_creditcard';
    protected $_canOrder = true;
    protected $_isInitializeNeeded = true;
    protected $_isGateway = true;
    protected $_allowCurrencyCode = ["BRL"];

    public function getTitle()
    {
        $title = Mage::getStoreConfig('payment/belluno_creditcard/title');
        if (!$title) {
            $title = $this->_getData('title');
        }
        return $title;
        // Retorna o título do método de pagamento
    }
    /**
     * Method that will be executed instead of magento's authorize default
     * workflow
     *
     * @param string $paymentAction
     * @param Varien_Object $stateObject
     *
     * @return Mage_Payment_Model_Method_Abstract
     */
    public function initialize($paymentAction, $stateObject)
    {
        $payment = $this->getInfoInstance();
        $this->authorize($payment, $payment->getOrder()->getBaseTotalDue());
    }
    /**
     * Processa o pedido
     *
     * @param Varien_Object $payment
     * @param float $amount
     * @return $this
     * @throws Mage_Core_Exception
     */
    public function authorize(Varien_Object $payment, $amount)
    {
      if ($this->canOrder()) {
        $info = $this->getInfoInstance();
        $transaction = new MageShop_Belluno_Model_Payment_Creditcard_Transaction();
        $transaction->transactionCreditCard($payment, $amount, $info); // Processar a transação do PIX
        return $this;
      }
    }
     /**
     *  Essa função é responsável por armazenar as informações do pagamento recebidas do formulário de checkout.
     *  Ela recebe um objeto Varien contendo os dados do formulário e os atribui à instância de informação do pagamento.
     *  Em seguida, realiza uma validação dos dados e armazena-os em "additional_information" no formato de array.
     *
     * @param mixed $data
     * @return self
     */
    public function assignData($data)
    {
        $info = $this->getInfoInstance();
        $info->setCheckNo($data->getCheckNo())->setCheckDate($data->getCheckDate());
        $dataAssign = $this->saveAssignData($data);
        $info->setAdditionalInformation("data", $dataAssign);
        return $this;
    }
    /**
     * Function to save assign data
     */
    public function saveAssignData($data)
    {
        $parts = explode('/', $data['expires_at']);
        $array = [
            'method' => $data['method'],
            'client_document' => $data['card_holder_document'],
            'visitor_id' => $data['visitor_id'],
            'card_holder_document' => $data['card_holder_document'],
            'card_holder_cellphone' => $data['card_holder_cellphone'],
            'card_holder_birth' => $data['card_holder_birth'],
            'card_number' => $data['card_number'],
            'name_on_card' => $data['name_on_card'],
            'card_month_exp' => $parts[0],
            'card_year_exp' => $parts[1],
            'cc_cvv' => $data['cc_cvv'],
            'card_installment' => $data['card_installment'],
            'card_hash' => $data['card_hash'],
            'card_flag' => $data['card_flag']
        ];
        return json_encode($array);
    }
}
