<?php

class MageShop_Belluno_Block_Form_Bankslip extends Mage_Payment_Block_Form {

  protected function _construct() {
    parent::_construct();
    $this->setTemplate('mageshop/belluno/form/bankslip.phtml');
  }

  public function getFieldCaptureTax() {
    $captureTax = Mage::getStoreConfig('payment/belluno_bankslip/capture_tax');
    if ($captureTax == true) {
      return true;
    } else {
      return false;
    }
  }
}
