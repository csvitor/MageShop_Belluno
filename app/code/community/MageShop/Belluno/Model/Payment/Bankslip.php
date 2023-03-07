<?php
class MageShop_Belluno_Model_Payment_Bankslip extends Mage_Payment_Model_Method_Abstract
{
    const PAY_CODE = 'belluno_bankslip';
    
    protected $_code = self::PAY_CODE;
    protected $_formBlockType = 'belluno/form_bankslip';
    protected $_infoBlockType = 'belluno/info_bankslip';

    protected $_canOrder = true;
    protected $_isInitializeNeeded = true;
    protected $_isGateway = true;
    protected $_allowCurrencyCode = ["BRL"];

    /**
     * Ob title module
     *
     * @return void
     */
    public function getTitle()
    {
        $title = Mage::getStoreConfig('payment/belluno_bankslip/title');
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
        $transaction = new MageShop_Belluno_Model_Payment_Bankslip_Transaction();
        $transaction->transactionBankslip($payment, $amount, $info); // Processar a transação do PIX
        return $this;
      }
    }
     /**
     *  Essa função é responsável por armazenar as informações do pagamento recebidas do formulário de checkout.
     *  Ela recebe um objeto Varien contendo os dados do formulário e os atribui à instância de informação do pagamento.
     *  Em seguida, realiza uma validação dos dados e armazena-os em "additional_information" no formato de array.
     *
     * @param Varien_Object $data
     * @return void
     */
    public function assignData(Varien_Object $data)
    {
        if (!($data instanceof Varien_Object)) {
            $data = new Varien_Object($data);
        }
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
        $array = [
            'method' => $data['method'],
            'client_document' => $data['client_document']
        ];
        return json_encode($array);
    }
   
}
