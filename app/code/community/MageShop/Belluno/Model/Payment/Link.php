<?php
class MageShop_Belluno_Model_Payment_Link extends Mage_Payment_Model_Method_Abstract
{
    const PAY_CODE = 'belluno_link';
    
    protected $_code = self::PAY_CODE;
    // protected $_formBlockType = 'belluno/form_link';
    protected $_infoBlockType = 'belluno/info_link';
    protected $_canUseCheckout = false;
    protected $_canUseInternal = true;
    protected $_canOrder = true;
    protected $_isInitializeNeeded = true;
    protected $_isGateway = true;
    protected $_allowCurrencyCode = ["BRL"];


    public function getTitle()
    {
        $title = Mage::getStoreConfig('payment/belluno_link/title');
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
        $transaction = new MageShop_Belluno_Model_Payment_Link_Transaction();
        $transaction->transactionLink($payment, $amount, $info); // Processar a transação do PIX
        return $this;
      }
    }
}
